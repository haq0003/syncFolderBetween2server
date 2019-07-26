<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Logger\ConsoleLogger;

class DppM01tom02Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dpp:m01tom02')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description');

        // php bin/console dpp:m01tom02
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->myOutput('--begin--');

        $process = new Process(' ps aux | grep "rsync" | grep -v "grep" ');
        $process->run();
        $checkIfrunning = $process->getOutput();

        if($checkIfrunning){
            $this->myOutput("STOP RSYNC IS ALREADY RUNNING !");
            return;
        }

        $servers = [];
        $servers[] = [ 
            'source' =>[
                'host' => 'XXXXXXXXXX',
                'alias'=> 'XXXXXXXXXX',
                'path' => 'XXXXXXXXXX',
                'port' => 'XXXXXXXXXX',
                'user' => 'XXXXXXXXXX'
            ],
            'destination' =>[
                'path' => 'XXXXXXXXXX',
                'user' => 'XXXXXXXXXX',
                'group'=> 'XXXXXXXXXX'
            ]
        ];

        foreach ($servers as $server){
            $optionSsh = " --super -og --chown={$server['destination']['user']}:{$server['destination']['group']} ";
            $optionSsh .= " -e \"ssh -p {$server['source']['port']}\" --stats --delete-before --bwlimit=500  ";

            $command =  "ionice -c 3 nice -n 19 /usr/bin/rsync -avzKh $optionSsh ";
            $command .= "{$server['source']['user']}@{$server['source']['host']}:{$server['source']['path']}   ";
            $command .= "  {$server['destination']['path']} ";
            $this->myOutput($command);
            $this->myExec($command);
        }

        $this->myOutput('--end--');
    }

    public function running($type, $buffer)
    {
        $buffer = trim($buffer);
        if (Process::ERR === $type) {
            $this->myOutput("<error>{$buffer}</error>");
        } else {
            $this->myOutput($buffer);
        }
    }

    public function myExec($command): array
    {
        $process = new Process($command);
        $process->setTimeout(86400);
        $process->run(array($this, 'running'));
        return array_filter(explode("\n", $process->getOutput()), 'strlen');
    }

    public function myOutput($buffer)
    {
        $this->getContainer()->get('monolog.logger.m01tom02')->info($buffer);
        $this->output->writeln($buffer);
    }

}







