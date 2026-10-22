<?php

declare(strict_types=1);

/*
 * This file is part of the yceruto/bundle-flex package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$header = <<<'EOF'
    This file is part of the yceruto/bundle-flex package.

    (c) Yonel Ceruto <yonelceruto@gmail.com>

    For the full copyright and license information, please view the LICENSE
    file that was distributed with this source code.
    EOF;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@PHP82Migration' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'declare_strict_types' => true,
        'header_comment' => ['header' => $header, 'location' => 'after_declare_strict'],
    ])
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in([__DIR__.'/src', __DIR__.'/tests'])
            ->exclude('Fixtures')
            ->append([__FILE__]),
    )
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
;
