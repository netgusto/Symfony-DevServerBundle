<?php

namespace Netgusto\DevServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;

class DevServerCommand extends ContainerAwareCommand {
    
    protected function configure() {
        $this
            ->setName('ng:server')
            ->setDescription('Development server with subdomain support, and automatic assets compilation.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $server = new Process("php app/console server:run 0.0.0.0:8000");
        $server->setTimeout(null);
        $processes[] = $server;
        
        $watch = new Process("php app/console assetic:dump --watch");
        $watch->setTimeout(null);
        $processes[] = $watch;

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
                    // All processes are timed out after 2 seconds and restarted afterwards
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