<?php


namespace RocketSeller\TwoPickBundle\Command;


use RocketSeller\TwoPickBundle\Controller\ChronServerRestController;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\HttpFoundation\Request;

class AutoChargeMembershipCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
            ->setName('symplifica:autoCharge')
            ->setDescription('Runs Reminder task if needed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Cobrar Automáticamente la membresía</comment>');
        /** @var ChronServerRestController $crons */
        $crons = $this->getContainer()->get('app.symplifica_chrons');

        $response = $crons->putAutoChargeMembershipAction();
        if ($response->getStatusCode() != 200) {
            $output->writeln('Fallo llamando servicio');
        } else {
            $output->writeln("Respuesta:\n" . implode("\n", $response->getData()['response']));
        }
        $output->writeln('<comment>Done!</comment>');
	    
    }
    
    
    
}