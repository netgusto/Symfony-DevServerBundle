<?php

namespace Netgusto\DevServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\ProcessUtils;
use Symfony\Component\Process\Process;

class DevServerCommand extends ContainerAwareCommand {
    
    protected function configure() {
        $this
            ->setName('server:dev')
            ->setDescription('Development server with subdomain support, and automatic assets compilation.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $container = $this->getContainer();

        $serverconfig = $container->getParameter('netgusto_dev_server.config');
        foreach($serverconfig['tasks'] as $task) {

            $command = $task['command'];

            if(!is_null($task['path'])) {
                $command = 'cd ' . ProcessUtils::escapeArgument($task['path']) . ';' . $command;
            }

            $process = new Process($command);
            $process->setTimeout(null);

            $processes[] = $process;
        }

        $sleep = 0.25;
        $output->writeln('<info>Server and assets watch started.</info>');

        do {
            $count = count($processes);
            for($i = 0; $i < $count; $i++) {
                
                if (!$processes[$i]->isStarted()) {
                    $processes[$i]->start();
                    continue;
                }

                try {
                    $processes[$i]->checkTimeout();
                } catch (\Exception $e) {
                    // Don't stop main thread execution
                }

                if (!$processes[$i]->isRunning()) {
                    $processes[$i]->restart();
                    $output->writeln('<info>Restart</info>');
                }

                $output->write($processes[$i]->getIncrementalOutput());

                gc_collect_cycles();
            }

            usleep($sleep * 1000000);
        } while ($count > 0);
    }
}