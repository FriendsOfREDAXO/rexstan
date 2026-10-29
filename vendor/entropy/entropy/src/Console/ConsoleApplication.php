<?php

declare(strict_types=1);

namespace Entropy\Console;

use Entropy\Attribute\RelatedTest;
use Entropy\Console\Contract\CommandInterface;
use Entropy\Console\Enum\ExitCode;
use Entropy\Console\Exception\ConsoleInputMappingException;
use Entropy\Console\Input\InputParser;
use Entropy\Console\Mapper\CLIRequestMapper;
use Entropy\Console\Output\CommandHelpFactory;
use Entropy\Console\Output\HelpPrinter;
use Entropy\Console\Output\OutputPrinter;
use Entropy\Tests\Console\ConsoleApplication\ConsoleApplicationTest;
use Throwable;

#[RelatedTest(ConsoleApplicationTest::class)]
final class ConsoleApplication
{
    private HelpPrinter $helpPrinter;

    private OutputPrinter $outputPrinter;

    private CommandHelpFactory $commandHelpFactory;

    private InputParser $inputParser;

    private CommandRegistry $commandRegistry;

    private CLIRequestMapper $cliRequestMapper;

    public function __construct(
        HelpPrinter $helpPrinter,
        OutputPrinter $outputPrinter,
        CommandHelpFactory $commandHelpFactory,
        InputParser $inputParser,
        CommandRegistry $commandRegistry,
        CLIRequestMapper $cliRequestMapper
    ) {
        $this->helpPrinter = $helpPrinter;
        $this->outputPrinter = $outputPrinter;
        $this->commandHelpFactory = $commandHelpFactory;
        $this->inputParser = $inputParser;
        $this->commandRegistry = $commandRegistry;
        $this->cliRequestMapper = $cliRequestMapper;
    }

    /**
     * @param mixed[] $argv
     * @return ExitCode::*
     */
    public function run(array $argv): int
    {
        $cliRequest = $this->inputParser->parse($argv);

        $commandName = $cliRequest->getCommandName();

        // no command name given - fall back to the default command, or show help
        if ($commandName === null) {
            $defaultCommand = $this->commandRegistry->getDefault();
            $wantsHelp = array_intersect(['h', 'help'], array_keys($cliRequest->getOptions())) !== [];

            if (! $defaultCommand instanceof CommandInterface || $wantsHelp) {
                $this->helpPrinter->print();
                return ExitCode::SUCCESS;
            }

            $commandName = $defaultCommand->getName();
        }

        if (! $this->commandRegistry->has($commandName)) {
            $defaultCommand = $this->commandRegistry->getDefault();

            // with a default command, an unknown leading token is its first argument (e.g. "ecs src")
            if (! $defaultCommand instanceof CommandInterface) {
                fwrite(STDERR, sprintf("Unknown command: %s\n\n", $commandName));

                $this->helpPrinter->print();

                return ExitCode::INVALID_COMMAND;
            }

            $cliRequest = $cliRequest->withCommandNameAndPrependedArgument($defaultCommand->getName(), $commandName);
            $commandName = $defaultCommand->getName();
        }

        try {
            $command = $this->commandRegistry->get($commandName);

            if ($cliRequest->isCommandHelp()) {
                // build command help here :)
                $commandHelp = $this->commandHelpFactory->build($command);
                $this->outputPrinter->writeln($commandHelp);

                return ExitCode::SUCCESS;
            }

            $runArguments = $this->cliRequestMapper->resolveArguments($command, $cliRequest);

            return $command->run(...$runArguments);

        } catch (ConsoleInputMappingException $consoleInputMappingException) {
            // user input error - show a clean message without a stack trace
            $this->outputPrinter->redBackground('Run failed: ' . $consoleInputMappingException->getMessage());

            return ExitCode::ERROR;
        } catch (Throwable $throwable) {
            $this->outputPrinter->redBackground('Run failed: ' . $throwable->getMessage());

            $this->outputPrinter->newline();
            $this->outputPrinter->writeln($throwable->getTraceAsString());

            return ExitCode::ERROR;
        }
    }
}
