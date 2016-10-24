<?php

namespace RocketSeller\TwoPickBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use DateTime;

class SocialSecurityAffiliationInProgressCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
                ->setName('symplifica:social:security:affiliation:inprogress')
                ->setDescription('Sends push notification saying that the affiliation is in progress')
                ->setHelp('1 business day affter all documents have been uploaded')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Cron Task SocialSecurityAffiliationInProgress ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
        $this->output = $output;

        $cronService = $this->getContainer()->get('app.symplifica_chrons');

        $response = $cronService->putSocialSecurityAffiliationInProgressAction();
        $this->printResponse($response, $output);

        $output->writeln('<comment>Done SocialSecurityAffiliationInProgress!</comment>');
    }

    private function printResponse($response, OutputInterface $output) {
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
                }
                if(isset($userResponse['result']['resultIos'])) {
                    if(isset($userResponse['result']['resultIos']['success']))
                        $output->write("----Ios     success:" . $userResponse['result']['resultIos']['success']);
                    if(isset($userResponse['result']['resultIos']['failure']))
                        $output->writeln(" -- failure:" . $userResponse['result']['resultIos']['failure']);
                }
            }
        }
    }
}
