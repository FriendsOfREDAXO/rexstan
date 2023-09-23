<?php

namespace rexstan;

use rex_console_command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

            if ($path === false) {
                throw new \Exception('Invalid path: '. $input->getArgument('path'));
            }
        }

        if ($input->hasOption('level')) {
            $level = $input->getOption('level');
            if (!preg_match('/^[0-9]$/', $level)) {
                throw new \Exception('Invalid level: '. $level);
            }
        }

        $result = RexStan::runFromCli($exitCode, $path, $level, $errorOutput);

        if ($result !== '') {
            $io->write($result);
        }

        // pass PHPStan exit code 1:1
        return $exitCode;
    }
}
