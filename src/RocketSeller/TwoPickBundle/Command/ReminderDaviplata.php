<?php


namespace RocketSeller\TwoPickBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\HttpFoundation\Request;

class ReminderDaviplataCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
            ->setName('symplifica:daviReminder')
            ->setDescription('Runs Reminder Daviplata task if needed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Recordatorio Crear Daviplata</comment>');
        /** @var Request $request */
        $request = new Request();
        $request->setMethod("POST");
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:ReminderRest:postReminderDaviplata',array('request'=>$request), array('_format' => 'json'));
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