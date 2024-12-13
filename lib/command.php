<?php

namespace rexstan;

use Exception;
use rex_console_command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->addArgument('path', InputArgument::OPTIONAL, 'File or directoy path to analyze')
            ->addOption('level', 'l', InputOption::VALUE_REQUIRED, 'Rule level (0-9)')
            ->addOption('generate-baseline', null, InputOption::VALUE_NONE, 'Generate PHPStan baseline')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Enable PHPStan debug')
            ->addOption('xdebug', null, InputOption::VALUE_NONE, 'Enable PHPStan Xdebug')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $stdErr = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $path = null;
        if ($input->getArgument('path') !== null) {
            $analyzePath = getcwd() .'/../'. $input->getArgument('path');
            $path = realpath($analyzePath);

            if ($path === false) {
                throw new Exception('Invalid path: '. $input->getArgument('path'));
            }
        }

        $arguments = '';
        if ($input->getOption('level') !== null) {
            $level = $input->getOption('level');
            if (!preg_match('/^1?[0-9]$/', $level)) {
                throw new Exception('Invalid level: '. $level);
            }
            $arguments .= ' --level='.$level;
        }

        if ($input->getOption('generate-baseline') !== false) {
            $arguments .= ' --generate-baseline';
        }
        if ($input->getOption('debug') !== false) {
            $arguments .= ' --debug';
        }
        if ($input->getOption('xdebug') !== false) {
            $arguments .= ' --xdebug';
        }

        if ($output->isDebug()) {
            $arguments .= ' -vvv';
        } elseif ($output->isVeryVerbose()) {
            $arguments .= ' -vv';
        } elseif ($output->isVerbose()) {
            $arguments .= ' -v';
        }

        $result = RexStan::runFromCli($exitCode, $path, $arguments, $errorOutput);

        if ($result !== '') {
            $io->write($result);
        }

        if ($errorOutput !== '') {
            $stdErr->write($errorOutput);
        }

        // pass PHPStan exit code 1:1
        return $exitCode;
    }
}
