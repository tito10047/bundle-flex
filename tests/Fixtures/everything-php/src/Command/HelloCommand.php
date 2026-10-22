<?php

declare(strict_types=1);

namespace Acme\EverythingPhpBundle\Command;

use Acme\EverythingPhpBundle\Exception\InvalidArgumentException;
use Acme\EverythingPhpBundle\GreeterInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Greets someone from the command line, e.g. "php bin/console acme-everything-php:hello Jane".
 *
 * Registered as the "acme_everything_php.command.hello" service. The command name is also
 * declared on its "console.command" tag, so the application loads the command lazily.
 */
#[AsCommand(
    name: 'acme-everything-php:hello',
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
        private GreeterInterface $greeter,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'The name of the person to greet')]
        string $name,
        #[Option(description: 'Greet in uppercase', shortcut: 'y')]
        bool $yell = false,
    ): int {
        try {
            $greeting = $this->greeter->greet($name);
        } catch (InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return Command::INVALID;
        }

        $io->success($yell ? mb_strtoupper($greeting) : $greeting);

        return Command::SUCCESS;
    }
}
