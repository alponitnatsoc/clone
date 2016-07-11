<?php

namespace RocketSeller\TwoPickBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\HttpFoundation\Request;

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
        /** @var Request $request */
        $request = new Request();
        $request->setMethod("PUT");
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollMethodRest:putAutoLiquidatePayroll',array('request'=>$request), array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            $output->writeln('Fallo llamando servicio');
        } else {
            //$response = json_decode($response);
            //dump($response);
            $output->writeln("Respuesta: " . $insertionAnswer->getContent());
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
