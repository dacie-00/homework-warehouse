<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once "vendor/autoload.php";

$application = new Application();

$start = new class extends Command {
    protected static $defaultName = "start";

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        return Command::SUCCESS;
    }
};

$application->add($start);
$application->setDefaultCommand("start");
$application->run();