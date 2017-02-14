<?php

namespace RocketSeller\TwoPickBundle\Command;

use RocketSeller\TwoPickBundle\Controller\ContractRestSecuredController;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteEntityRecordsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('symplifica:execute:entityRecord')
            ->setDescription('check all the entityRecords to execute them when the date changes need to be applied matching the actual date')
            ->setHelp('the entityRecords are checked every day');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Cron Task executeEntityRecords ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
        $this->output = $output;

        $controller = new ContractRestSecuredController();
        $controller->setContainer($this->getContainer());
        $response = $controller->putExecuteAllPendingEntityRecordsAction();

        if ($response->getStatusCode() != 200) {
            $output->writeln('<error>Error calling service</error>');
        }else{
            if(key_exists("entityRecords",$response->getData())){
                $entityResponse = $response->getData()["entityRecords"];
            }
            foreach ($entityResponse as $key => $result) {
                if(key_exists("executed",$result))
                    $done = $result["executed"];
                if(key_exists("error",$result))
                    $error = $result["error"];
                if($done){
                    $output->writeln("El Entity Record con id ".$key." se ejecuto correctamente");
                }else{
                    $output->writeln("<error>[ERROR]-- El Entity Record con id ".$key." no se pudo ejecutar[ERROR]</error>");
                }
            }
        }
    }
}
