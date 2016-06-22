<?php

namespace RocketSeller\TwoPickBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;

class PayrollCloseCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
                ->setName('symplifica:payroll:close')
                ->setDescription('Cerrar nominas dia 25')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Cerrar nominas dia 25</comment>');

        $this->output = $output;

        $ch = curl_init("http://127.0.0.1/api/public/v1/secured/auto/liquidate/payroll");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

        $response = curl_exec($ch);
        if (!$response) {
            $output->writeln('Fallo llamando servicio');
        } else {
            //$response = json_decode($response);
            //dump($response);
            $output->writeln("Respuesta: " . $response);
        }
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
        $returnCode = $command->execute($input, $this->output);

        return $returnCode != 0;
    }

}