<?php

namespace RocketSeller\TwoPickBundle\Command;

use RocketSeller\TwoPickBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class disableEndContractNotificationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('symplifica:disable:endContracts:notifications')
            ->setDescription('check all the notifications with docType CTC and disables it if deadline has been reached and document has never been downloaded')
            ->setHelp('the notification must be activate and have a deadline to work');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Cron Task disableEndContractNotifications ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
        $this->output = $output;
        $cronService = $this->getContainer()->get('app.symplifica_chrons');
        $response = $cronService->putDisableEndContractNotificationsAction();
        if ($response->getStatusCode() != 200) {
            $output->writeln('<error>Error calling service</error>');
        }else{
            /** @var Notification $notification */
            foreach ($response->getData()['notifications'] as $notification){
                $output->writeln("La notificación con id ".$notification->getId()." fue inhabilitada");
            }
        }
    }
}
