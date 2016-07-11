<?php


namespace RocketSeller\TwoPickBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;

class LastReminderPayCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
            ->setName('symplifica:lastReminder')
            ->setDescription('Runs Reminder task if needed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Recordatorio Última Fecha Corte Novedades</comment>');
        
        $ip = trim(shell_exec ("wget http://ipinfo.io/ip -qO -").'', " \n.\t.");
        
        if($ip=='52.91.121.67'){
            $service = "symplifica_dev:elmismo@52.91.121.67/api/public/v1/secured/lasts/reminders";
        }else{
            $service = "https://52.73.123.224/api/public/v1/secured/lasts/reminders";
        }
        $this->output = $output;
        $ch = curl_init($service);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

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