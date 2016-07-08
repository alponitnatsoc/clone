<?php


namespace RocketSeller\TwoPickBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;

class ReminderPayCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
            ->setName('symplifica:reminder')
            ->setDescription('Runs Reminder task if needed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Recordatorio Fecha Corte Novedades</comment>');

        $this->output = $output;

        $ch = curl_init("http://127.0.0.1/api/public/v1/secured/reminders");

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
    
    
    
}