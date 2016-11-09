<?php

namespace RocketSeller\TwoPickBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;

class PendingDocumentsReminderCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
                ->setName('symplifica:pending:documents:reminder')
                ->setDescription('Sends push notification reminding of pending documents')
                ->setHelp('The push notifications are sent 1, 3 and 7 days after users have completed step 3
                            but have at least one document pending.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Cron Task PendingDocumentsReminder ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
        $this->output = $output;

        $cronService = $this->getContainer()->get('app.symplifica_chrons');
        $response = $cronService->putPendingDocumentsReminderAction();
        if ($response->getStatusCode() != 200) {
            $output->writeln('<error>Error calling service</error>');
        } else {
            foreach ($response->getData() as $userResponse) {
                $output->write("<info>Sent to userId: " .$userResponse['userId'] . "</info>");
                $output->writeln(" -- status: " .$userResponse['result']['status']);
                if(isset($userResponse['result']['resultAndroid'])) {
                    if(isset($userResponse['result']['resultAndroid']['success']))
                        $output->write("----Android success:" . $userResponse['result']['resultAndroid']['success']);
                    if(isset($userResponse['result']['resultAndroid']['failure']))
                        $output->writeln(" -- failure:" . $userResponse['result']['resultAndroid']['failure']);
                    // $output->writeln("----multicast_id = " . $userResponse['result']['resultAndroid']['multicast_id']);
                    // $output->writeln("----success = " . $userResponse['result']['resultAndroid']['success']);
                    // $output->writeln("----failure = " . $userResponse['result']['resultAndroid']['failure']);
                    // $output->writeln("----canonical_ids = " . $userResponse['result']['resultAndroid']['canonical_ids']);
                    // $output->writeln("----results = ");
                    // foreach ($userResponse['result']['resultAndroid']['results'] as $AndroidResutls) {
                    //     if(isset($AndroidResutls['error']))
                    //         $output->writeln("------error: " . $AndroidResutls['error']);
                    //     if(isset($AndroidResutls['message_id']))
                    //         $output->writeln("------message_id: " . $AndroidResutls['message_id']);
                    // }
                }
                if(isset($userResponse['result']['resultIos'])) {
                    // $output->writeln("----Ios----");
                    if(isset($userResponse['result']['resultIos']['success']))
                        $output->write("----Ios     success:" . $userResponse['result']['resultIos']['success']);
                    if(isset($userResponse['result']['resultIos']['failure']))
                        $output->writeln(" -- failure:" . $userResponse['result']['resultIos']['failure']);
                    // $output->writeln("----multicast_id = " . $userResponse['result']['resultIos']['multicast_id']);
                    // $output->writeln("----success = " . $userResponse['result']['resultIos']['success']);
                    // $output->writeln("----failure = " . $userResponse['result']['resultIos']['failure']);
                    // $output->writeln("----canonical_ids = " . $userResponse['result']['resultIos']['canonical_ids']);
                    // $output->writeln("----results = ");
                    // foreach ($userResponse['result']['resultIos']['results'] as $AndroidResutls) {
                    //     if(isset($AndroidResutls['error']))
                    //         $output->writeln("------error: " . $AndroidResutls['error']);
                    //     if(isset($AndroidResutls['message_id']))
                    //         $output->writeln("------message_id: " . $AndroidResutls['message_id']);
                    // }
                }
            }


        }
        $output->writeln('<comment>Done PendingDocumentsReminder!</comment>');
    }

}
