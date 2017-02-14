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
        $port = "8000";
        if($this->getContainer()->getParameter('ambiente') == "produccion") {
            $port = "80";
        }
        $ch = curl_init("127.0.0.1:$port/api/public/v1/execute/all/pending/contract/records");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $response = curl_exec($ch);
        if (curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) {
            $output->writeln('<error>Error calling service</error>');
        }else{
            $response = json_decode($response,true);
            if(key_exists("contractRecords",$response)){
                $contractResponse = $response["contractRecords"];
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
