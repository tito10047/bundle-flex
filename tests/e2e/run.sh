#!/usr/bin/env bash
#
# End-to-end test of the plugin: generates a bundle with "composer create-project"
# from a bundle-skeleton checkout and this working copy of the plugin, checks it,
# optionally reinstalls its dependencies for a given Symfony version, and runs its
# own Composer scripts (tests and quality checks).
#
# Usage: tests/e2e/run.sh <target-dir>
#
# The answers of the assistant are taken from the BUNDLE_FLEX_* environment
# variables (see CONTRIBUTING.md). The run is configured with:
#
#   E2E_SKELETON_DIR         bundle-skeleton checkout; when empty, E2E_SKELETON_REPOSITORY
#                            is cloned at E2E_SKELETON_REF
#   E2E_SKELETON_REPOSITORY  default: https://github.com/tito10047/bundle-skeleton.git
#   E2E_SKELETON_REF         default: feat/symfony-8
#   E2E_PLUGIN_VERSION       version given to this working copy, it must satisfy the
#                            skeleton's "yceruto/bundle-flex" constraint (default: 1.0.0)
#   E2E_SYMFONY              Symfony versions to install, e.g. "7.4.*" (SYMFONY_REQUIRE with
#                            Symfony Flex, as in the generated CI); default: the latest ones
#   E2E_DEPENDENCIES         "highest" (default) or "lowest" (composer update --prefer-lowest)
#   E2E_ALLOW_INSECURE       "1" allows the versions affected by security advisories when installing the
#                            dependencies (Composer's audit.block-insecure), e.g. for Symfony 8.0, which is
#                            no longer maintained; like the Symfony 8.0 job of the generated CI
#   E2E_SCRIPTS              Composer scripts of the bundle to run, in order (default: "qa", which runs
#                            the tests first); a missing script fails the run
#
# The plugin is copied without vendor/ (tracked and untracked files, .gitignore applies);
# the skeleton is exported with "git archive", so its export-ignore rules apply as for
# the dist archive. Neither composer.json is modified: the plugin repository is declared
# in the global configuration of a temporary COMPOSER_HOME, so nothing test-specific can
# leak into the generated composer.json.

set -euo pipefail

if [[ $# -ne 1 ]]; then
    echo "Usage: $0 <target-dir>" >&2
    exit 64
fi

plugin_dir=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
target_dir=$(realpath -m "$1")
skeleton_dir=${E2E_SKELETON_DIR:-}
skeleton_repository=${E2E_SKELETON_REPOSITORY:-https://github.com/tito10047/bundle-skeleton.git}
skeleton_ref=${E2E_SKELETON_REF:-feat/symfony-8}
plugin_version=${E2E_PLUGIN_VERSION:-1.0.0}
symfony=${E2E_SYMFONY:-}
dependencies=${E2E_DEPENDENCIES:-highest}
allow_insecure=${E2E_ALLOW_INSECURE:-0}
read -r -a scripts <<< "${E2E_SCRIPTS-qa}"

fail() {
    if [[ -n ${GITHUB_ACTIONS:-} ]]; then echo "::error::e2e: $*"; else echo "e2e: error: $*" >&2; fi
    exit 1
}

group() {
    if [[ -n ${GITHUB_ACTIONS:-} ]]; then echo "::group::$*"; else echo "==> $*"; fi
}

endgroup() {
    if [[ -n ${GITHUB_ACTIONS:-} ]]; then echo "::endgroup::"; fi
}

[[ $dependencies == highest || $dependencies == lowest ]] || fail "E2E_DEPENDENCIES must be \"highest\" or \"lowest\", got \"$dependencies\""
[[ $allow_insecure == 0 || $allow_insecure == 1 ]] || fail "E2E_ALLOW_INSECURE must be \"0\" or \"1\", got \"$allow_insecure\""
[[ -e $target_dir ]] && fail "$target_dir already exists"

work_dir=$(mktemp -d)
trap 'rm -rf "$work_dir"' EXIT

# A temporary Composer home: the cache and the credentials of the real one are kept.
COMPOSER_CACHE_DIR=${COMPOSER_CACHE_DIR:-$(composer config --global cache-dir)}
real_home=$(composer config --global home)
export COMPOSER_CACHE_DIR
export COMPOSER_HOME=$work_dir/composer-home
mkdir -p "$COMPOSER_HOME"
if [[ -f $real_home/auth.json ]]; then cp "$real_home/auth.json" "$COMPOSER_HOME/"; fi

group "Copy the plugin"
mkdir "$work_dir/bundle-flex"
(
    cd "$plugin_dir"
    git ls-files -z --cached --others --exclude-standard | while IFS= read -r -d '' file; do
        # tracked files deleted from the working copy are listed too
        if [[ -e $file ]]; then printf '%s\0' "$file"; fi
    done | tar --null --files-from=- -cf - | tar -x -C "$work_dir/bundle-flex"
)
endgroup

group "Export the skeleton"
if [[ -z $skeleton_dir ]]; then
    skeleton_dir=$work_dir/skeleton-checkout
    git clone --quiet --depth 1 --branch "$skeleton_ref" "$skeleton_repository" "$skeleton_dir"
fi
[[ -f $skeleton_dir/composer.json ]] || fail "$skeleton_dir is not a bundle-skeleton checkout"
mkdir "$work_dir/skeleton"
ref=$(git -C "$skeleton_dir" stash create)
git -C "$skeleton_dir" archive "${ref:-HEAD}" | tar -x -C "$work_dir/skeleton"
ls -A "$work_dir/skeleton"
endgroup

repository() {
    printf '{"type": "path", "url": "%s", "options": {"symlink": false, "versions": {"%s": "%s"}}}' "$1" "$2" "$3"
}

composer config --global repositories.bundle-flex "$(repository "$work_dir/bundle-flex" yceruto/bundle-flex "$plugin_version")"

group "composer create-project"
composer create-project yceruto/bundle-skeleton "$target_dir" --no-interaction --no-progress \
    --repository="$(repository "$work_dir/skeleton" yceruto/bundle-skeleton 1.0.0)"
endgroup

cd "$target_dir"

group "Check the generated bundle"
# shellcheck disable=SC2016 # PHP code, not shell expansions
php -r '
    $json = json_decode(file_get_contents("composer.json"), true, flags: JSON_THROW_ON_ERROR);
    $errors = [];
    foreach (["repositories", "minimum-stability", "prefer-stable"] as $key) {
        if (array_key_exists($key, $json)) {
            $errors[] = "the skeleton leaked \"$key\"";
        }
    }
    if (str_contains(file_get_contents("composer.json"), "yceruto/bundle-flex")) {
        $errors[] = "yceruto/bundle-flex is still referenced";
    }
    if ("symfony-bundle" !== ($json["type"] ?? null)) {
        $errors[] = "the type is not \"symfony-bundle\"";
    }
    $name = getenv("BUNDLE_FLEX_NAME");
    if (false !== $name && $name !== ($json["name"] ?? null)) {
        $errors[] = "the name is not \"$name\"";
    }
    foreach ($errors as $error) {
        fwrite(STDERR, "e2e: error: composer.json: $error\n");
    }
    exit([] === $errors ? 0 : 1);
'
[[ -d vendor/yceruto/bundle-flex ]] && fail "the plugin is still installed"

composer validate --strict
composer dump-autoload --optimize --strict-psr
find . -path ./vendor -prune -o -name '*.php' -type f -print0 | xargs -0 -r -n1 php -l > /dev/null
endgroup

if [[ -n $symfony || $dependencies == lowest || $allow_insecure == 1 ]]; then
    group "Install the ${dependencies} dependencies${symfony:+ with Symfony $symfony}"
    # exactly as the generated CI workflow, see https://symfony.com/doc/current/bundles/best_practices.html
    composer global config --no-plugins allow-plugins.symfony/flex true
    composer global require --no-progress --no-scripts --no-plugins symfony/flex
    if [[ $allow_insecure == 1 ]]; then
        # in the temporary COMPOSER_HOME, so for this run only
        composer config --global audit.block-insecure false
    fi
    options=(--prefer-dist --no-progress --no-interaction)
    if [[ $dependencies == lowest ]]; then options+=(--prefer-lowest --prefer-stable); fi
    if [[ -n $symfony ]]; then export SYMFONY_REQUIRE=$symfony; fi
    composer update "${options[@]}"
    endgroup
fi

version=$(php -r 'require "vendor/autoload.php"; echo Symfony\Component\HttpKernel\Kernel::VERSION;')
echo "Symfony $version, PHP $(php -r 'echo PHP_VERSION;'), $dependencies dependencies"
# shellcheck disable=SC2053 # glob matching on purpose: "7.4.*" matches "7.4.3"
if [[ -n $symfony && $version != $symfony ]]; then
    fail "Symfony $symfony was expected, $version is installed"
fi

defined_scripts=$(php -r 'echo implode("\n", array_keys(json_decode(file_get_contents("composer.json"), true)["scripts"] ?? []));')
for script in "${scripts[@]}"; do
    grep -qxF "$script" <<< "$defined_scripts" || fail "the generated bundle has no \"$script\" script"

    group "composer $script"
    composer "$script"
    endgroup
done
