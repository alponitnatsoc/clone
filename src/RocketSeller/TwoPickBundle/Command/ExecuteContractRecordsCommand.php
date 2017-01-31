<?php

namespace RocketSeller\TwoPickBundle\Command;

use RocketSeller\TwoPickBundle\Controller\ContractRestSecuredController;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteContractRecordsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('symplifica:execute:contractRecord')
            ->setDescription('check all the contractRecord to execute them when the date changes need to be aplied match the actual date')
            ->setHelp('the contractRecords are checked every day');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Cron Task executeContractRecords ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
        $this->output = $output;

        $controller = new ContractRestSecuredController();
        $controller->setContainer($this->getContainer());
        $response = $controller->putExecuteAllPendingContractRecordsAction();

        if ($response->getStatusCode() != 200) {
            $output->writeln('<error>Error calling service</error>');
        }else{
            if(key_exists("contractRecords",$response->getData())){
                $contractResponse = $response->getData()["contractRecords"];
            }
            foreach ($contractResponse as $key => $result) {
                if(key_exists("executed",$result))
                    $done = $result["executed"];
                if(key_exists("error",$result))
                    $error = $result["error"];
                if($done){
                    $output->writeln("El Contract Record con id ".$key." se ejecuto correctamente");
                }else{
                    $output->writeln("<error>[ERROR]-- El Contract Record con id ".$key." no se pudo ejecutar[ERROR]</error>");
                }
            }
        }
    }
}
