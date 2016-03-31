<?php

namespace RocketSeller\TwoPickBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;

class CronTasksRunCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
                ->setName('crontasks:run')
                ->setDescription('Runs Cron Tasks if needed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Cron Tasks...</comment>');

        $this->output = $output;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $crontasks = $em->getRepository('RocketSellerTwoPickBundle:CronTask')->findAll();

        foreach ($crontasks as $crontask) {
            // Get the last run time of this task, and calculate when it should run next
            $lastrun = $crontask->getLastRun() ? $crontask->getLastRun()->format('U') : 0;
            $nextrun = $lastrun + $crontask->getInterval();

            // We must run this task if:
            // * time() is larger or equal to $nextrun
            $run = (time() >= $nextrun);

            if ($run) {
                $output->writeln(sprintf('Running Cron Task <info>%s</info>', $crontask));

                try {
                    $commands = $crontask->getCommands();

                    if (!is_array($commands)) {
                        $commands2[] = $commands;
                    } else {
                        $commands2 = $commands;
                    }

                    foreach ($commands2 as $command) {
                        $output->writeln(sprintf('Executing command <comment>%s</comment>...', $command));
                        // Run the command
                        $result = $this->runCommand($command);

                        // Set $lastrun for this crontask
                        $crontask->setLastRun(new \DateTime());
                    }

                    $output->writeln('<info>SUCCESS</info>');
                } catch (\Exception $e) {
                    $output->writeln(sprintf('<error>ERROR=> %s</error>', $e->getMessage()));
                }

                // Persist crontask
                $em->persist($crontask);
            } else {
                $output->writeln(sprintf('Skipping Cron Task <info>%s</info>', $crontask));
            }
        }

        // Flush database changes
        $em->flush();

        $output->writeln('<comment>Done!</comment>');
    }

    private function runCommand($string)
    {
        // Split namespace and arguments
        $namespace = split(' ', $string)[0];

        // Set input
        $command = $this->getApplication()->find($namespace);
        $input = new StringInput($string);

        // Send all output to the console
        $returnCode = $command->run($input, $this->output);
        return $returnCode != 0;
    }

}
