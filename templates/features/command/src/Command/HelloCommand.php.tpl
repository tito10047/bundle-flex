<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Feature\CommandFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$commandName = CommandFeature::commandName($naming);
$imports = $helper->imports([
    $naming->namespace.'\Exception\InvalidArgumentException',
    GreeterContributor::interfaceName($naming),
    'Symfony\Component\Console\Attribute\Argument',
    'Symfony\Component\Console\Attribute\AsCommand',
    'Symfony\Component\Console\Attribute\Option',
    'Symfony\Component\Console\Command\Command',
    'Symfony\Component\Console\Style\SymfonyStyle',
], $naming->namespace.'\Command');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->namespace ?>\Command;

<?= $imports->render() ?>

/**
 * Greets someone from the command line, e.g. "php bin/console <?= $commandName ?> Jane".
 *
 * Registered as the "<?= CommandFeature::serviceId($naming) ?>" service. The command name is also
 * declared on its "console.command" tag, so the application loads the command lazily.
 */
#[<?= $imports->name('Symfony\Component\Console\Attribute\AsCommand') ?>(
    name: <?= $helper->php($commandName) ?>,
    description: 'Greets someone',
    help: <<<'HELP'
        The <info>%command.name%</info> command greets the given person:

          <info>php %command.full_name% Jane</info>

        Add the <comment>--yell</comment> option to greet them in uppercase.
        HELP,
)]
final readonly class HelloCommand
{
    public function __construct(
        private <?= $imports->name(GreeterContributor::interfaceName($naming)) ?> $greeter,
    ) {
    }

    public function __invoke(
        <?= $imports->name('Symfony\Component\Console\Style\SymfonyStyle') ?> $io,
        #[<?= $imports->name('Symfony\Component\Console\Attribute\Argument') ?>(description: 'The name of the person to greet')]
        string $name,
        #[<?= $imports->name('Symfony\Component\Console\Attribute\Option') ?>(description: 'Greet in uppercase', shortcut: 'y')]
        bool $yell = false,
    ): int {
        try {
            $greeting = $this->greeter->greet($name);
        } catch (<?= $imports->name($naming->namespace.'\Exception\InvalidArgumentException') ?> $exception) {
            $io->error($exception->getMessage());

            return <?= $imports->name('Symfony\Component\Console\Command\Command') ?>::INVALID;
        }

        $io->success($yell ? mb_strtoupper($greeting) : $greeting);

        return <?= $imports->name('Symfony\Component\Console\Command\Command') ?>::SUCCESS;
    }
}
