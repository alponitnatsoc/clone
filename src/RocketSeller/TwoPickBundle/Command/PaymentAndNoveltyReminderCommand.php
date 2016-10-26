<?php

namespace RocketSeller\TwoPickBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use DateTime;

class PaymentAndNoveltyReminderCommand extends ContainerAwareCommand
{

    private $output;

    protected function configure()
    {
        $this
                ->setName('symplifica:payment:novelty:reminder')
                ->setDescription('Sends push notification and mail reminding payments due day')
                ->setHelp('The push notifications are sent
                             //TODO REVISAR FECHAS!!
                            -Report Novleties reminder: 6 days business days before 15.
                            -Last day of pay reminder: 4 days business days before 15.
                            -Report Novleties reminder: 6 days business days before 25.
                            -Last day of pay reminder: 4 days business days before 25.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Running Cron Task PaymentAndNovletyReminder ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
        $this->output = $output;

        $cronService = $this->getContainer()->get('app.symplifica_chrons');

        $now = new DateTime('now');
        $currMonth = $now->format('m');
        $currYear = $now->format('Y');
        $currDate = $now->format('d');

        $begin = new DateTime( '2010-05-01' );
        $end = new DateTime( '2016-10-15' );
        $utils = $this->getContainer()->get('app.symplifica_utils');

        $dateString = $currYear . '-' . $currMonth . '-12';
        $sixbusinessDaysBefore15 = $utils->getWorkableDaysToDateAction($dateString, -5);
        if($sixbusinessDaysBefore15 == $now->format("Y-m-d")) {
            $response = $cronService->putPaymentReminderAction("Hola, es tiempo de reportar novedades y pagar",
                                                    "¡Hola! Es momento de reportar novedades y pagar la quincena de tu empleado. Da clic para entrar",
                                                    2);
           $output->writeln("5 días hábiles antes del 12");
           $this->printResponse($response, $output);
        }

        $dateString = $currYear . '-' . $currMonth . '-12';
        // $fourbusinessDaysBefore15 = $utils->getWorkableDaysToDateAction($dateString, 0);
        if($dateString == $now->format("Y-m-d")) {
            $response = $cronService->putPaymentReminderAction("¡Último día! Realiza el pago a tu empleado",
                                                    "¡Importante! Hoy es el último día para realizar el pago a tu empleado y reportar las novedades de este período. Entra ya haciendo clic",
                                                    2);
            $output->writeln("recordatorio de pago dia 12");
            $this->printResponse($response, $output);
        }

        $dateString = $currYear . '-' . $currMonth . '-25';
        $sixbusinessDaysBefore25 = $utils->getWorkableDaysToDateAction($dateString, -5);
        if($sixbusinessDaysBefore25 == $now->format("Y-m-d")) {
            $response = $cronService->putPaymentReminderAction("Hola, es tiempo de reportar novedades y pagar",
                                                    "¡Hola! Es momento de reportar novedades de este periodo y realizar los pagos de seguridad social y sueldo. Da clic para entrar",
                                                    2);
            $output->writeln("5 días hábiles antes del 25");
            $this->printResponse($response, $output);
        }

        $dateString = $currYear . '-' . $currMonth . '-26';
        // $fourbusinessDaysBefore25 = $utils->getWorkableDaysToDateAction($dateString, -4);
        if($dateString == $now->format("Y-m-d")) {
            $response = $cronService->putPaymentReminderAction("¡Último día! Realiza el pago a tu empleado",
                                                    "¡Importante! Hoy es el último día para realizar el pago a tu empleado y reportar las novedades de este período. Entra ya haciendo clic",
                                                    4);
            $output->writeln("recordatorio de pago dia 25");
            $this->printResponse($response, $output);
        }

        $output->writeln('<comment>Done PaymentAndNovletyReminder!</comment>');
    }

    private function printResponse($response, OutputInterface $output) {
        if ($response->getStatusCode() != 200) {
            $output->writeln('<error>Error calling service</error>');
        } else {
            foreach ($response->getData() as $userResponse) {
                $output->write("<info>Sent to userId: " .$userResponse['userId'] . "</info>");

                $output->write(" -- status push: " .$userResponse['resultPush']['status']);
                $emailSent = $userResponse['resultMail'] ? 'true' : 'false';
                $output->writeln("  email sent: $emailSent");
                if(isset($userResponse['resultPush']['resultAndroid'])) {
                    if(isset($userResponse['resultPush']['resultAndroid']['success']))
                        $output->write("----Android success:" . $userResponse['resultPush']['resultAndroid']['success']);
                    if(isset($userResponse['resultPush']['resultAndroid']['failure']))
                        $output->writeln(" -- failure:" . $userResponse['resultPush']['resultAndroid']['failure']);
                }
                if(isset($userResponse['resultPush']['resultIos'])) {
                    if(isset($userResponse['resultPush']['resultIos']['success']))
                        $output->write("----Ios     success:" . $userResponse['resultPush']['resultIos']['success']);
                    if(isset($userResponse['resultPush']['resultIos']['failure']))
                        $output->writeln(" -- failure:" . $userResponse['resultPush']['resultIos']['failure']);
                }
            }
        }
    }
}
