<?php

namespace RocketSeller\TwoPickBundle\Command;

use RocketSeller\TwoPickBundle\Entity\Contract;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ContractRenewCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('symplifica:renew:contracts')
            ->setDescription('check all the contracts to create notifications for auto renew contracts when end_date its equal to date + 38 days')
            ->setHelp('the contracts are checked every day');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Cron Task autoRenewContract ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
        $this->output = $output;
        $cronService = $this->getContainer()->get('app.symplifica_chrons');
        $response = $cronService->putAutoRenewalContractNotificationAction();
        if ($response->getStatusCode() != 200) {
            $output->writeln('<error>Error calling service</error>');
        }else{
            /** @var Contract $contract */
            foreach ($response->getData()['contracts'] as $contract){
                $output->writeln("El contrato con id ".$contract->getIdContract()." esta por terminar");
            }
        }
    }
}
