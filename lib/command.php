<?php

namespace rexstan;

use rex_console_command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function getcwd;

class rexstan_command extends rex_console_command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('rexstan:analyze')
            ->setDescription('Run static code analysis')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to analyze')
            ->addOption('level', 'l', InputOption::VALUE_REQUIRED, 'Rule level')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $path = null;
        $level = 0;
        if ($input->hasArgument('path')) {
            $analyzePath = getcwd() .'/../'. $input->getArgument('path');
            $path = realpath($analyzePath);
        }

        if ($input->hasOption('level')) {
            $level = $input->getOption('level');
            if ($level !== 'max') {
                $level = (int) $level;
            }
        }

        $result = RexStan::runFromCli($path, $level, $errorOutput, $exitCode);

        if ($exitCode === 0) {
            if ($result !== '') {
                $io->write($result);
            }
        } else {
            if ($errorOutput !== '') {
                $io->error($errorOutput);
            }
        }

        return $exitCode;
    }
}
