<?php

use Symfony\Component\Console\Input\InputInterface;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo RexStan::runFromCli();

        return 0;
    }
}
