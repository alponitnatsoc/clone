<?php

namespace RocketSeller\TwoPickBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use DateTime;

class UploadContractReminderCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
                ->setName('symplifica:upload:contract:reminder')
                ->setDescription('Sends push notification reminding to upload contract')
                ->setHelp('The push notifications are sent
                            - 1, 7, 15, 30 after employee is register to slq.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Cron Task UploadContractReminder ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
        $this->output = $output;

        $cronService = $this->getContainer()->get('app.symplifica_chrons');

        $response = $cronService->putUploadContractReminderAction();
        $this->printResponse($response, $output);
        $output->writeln('<comment>Done UploadContractReminder!</comment>');
    }

    private function printResponse($response, OutputInterface $output) {
        if ($response->getStatusCode() != 200) {
            $output->writeln('<error>Error calling service</error>');
        } else {
            foreach ($response->getData() as $userResponse) {
                $output->write("<info>Sent to userId: " .$userResponse['userId'] . "</info>");

                $output->writeln(" -- status push: " .$userResponse['resultPush']['status']);
                if(isset($userResponse['resultPush']['resultAndroid'])) {
                    if(isset($userResponse['resultPush']['resultAndroid']['success']))
                        $output->write("----Android success:" . $userResponse['resultPush']['resultAndroid']['success']);
                    if(isset($userResponse['resultPush']['resultAndroid']['failure']))
                        $output->writeln(" -- failure:" . $userResponse['resultPush']['resultAndroid']['failure']);
                }
                if(isset($userResponse['resultPush']['resultIos'])) {
                    if(isset($userResponse['resultPush']['resultIos']['success']))
                        $output->write("----Ios     success:" . $userResponse['resultPush']['resultIos']['success']);
                    if(isset($userResponse['resultPush']['resultIos']['failure']))
                        $output->writeln(" -- failure:" . $userResponse['resultPush']['resultIos']['failure']);
                }
            }
        }
    }
}
