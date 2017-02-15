<?php

namespace RocketSeller\TwoPickBundle\Command;

use DateTime;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Date;

class PayrollCloseCommand extends ContainerAwareCommand
{
	
	private $output;
	
	protected function configure()
	{
		$this
		  ->setName('symplifica:payroll:close')
		  ->setDescription('Cerrar nominas dia 25')
		  ->setHelp('Cerrar nominas dia 25');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<comment>Running Cron Task PayrollClose ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
		$this->output = $output;
		
		/** @var DateTime $today */
		$today = new DateTime();
		$day = $today->format('d');
		$month = $today->format('m');
		$year = $today->format('Y');
		
		if($day == 16) {
			$day = 13;
		}
		if($day == 1) {
			$day = 26;
			$oneMonthLess = $today->modify("-1 month");
			$month = $oneMonthLess->format('m');
			$year = $oneMonthLess->format('Y');
		}
		
		if($day != 13 || $day != 26) {
			$output->writeln('<comment>Done today is not the day!</comment>');
			return;
		}
		
		$host = "127.0.0.1";
		$port = "8000";
		if($this->getContainer()->getParameter('ambiente') == "produccion") {
			$port = "80";
		}
		/** @var User $backUser */
		$backUser = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")
		    ->findOneBy(array('email_cannonical' => 'backofficesymplifica@gmail.com'));
		$parameters = array(
		  "token" => $backUser->getSalt(),
		  "month" => "01",
		  "year" => date("Y"),
		  "day" => "26",
		  "period" => 4
		);
		
		$paramsJson = json_encode($parameters);
		$chAutoLiquidate = curl_init("//$host:$port/api/public/v1/auto/liquidate/payroll");
		
		curl_setopt($chAutoLiquidate, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chAutoLiquidate, CURLOPT_HTTPHEADER, array('Content-type: application/json','Content-Length: ' . strlen($paramsJson)));
		curl_setopt($chAutoLiquidate, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($chAutoLiquidate, CURLOPT_POSTFIELDS, $paramsJson);
		
		
		do {
			$response = curl_exec($chAutoLiquidate);
			var_dump($response);
			if ($response == null || curl_getinfo($chAutoLiquidate, CURLINFO_HTTP_CODE != 200)) {
				$output->writeln('Fallo llamando auto liquidate');
				$cont = 0;
			} else {
				$output->writeln("Respuesta auto liquidate todbien: ");
				
//				$output->writeln(json_decode($response, true));
				$jsonR = json_decode($response, true);
				$cont = $jsonR['cont'];
				$output->writeln('cont: ' . $cont);
			}
			
		} while($cont != 0);
		
		$parameters2 = array(
		  "period" => 4,
		  "month" => "01",
		  "year" => date("Y")
		);

		$paramsJson = json_encode($parameters2);

		$chFixPodPila = curl_init("//$host:$port/api/public/v1/fix/p/o/d/pila");

		curl_setopt($chFixPodPila, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chFixPodPila, CURLOPT_HTTPHEADER, array('Content-type: application/json','Content-Length: ' . strlen($paramsJson)));
		curl_setopt($chFixPodPila, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($chFixPodPila, CURLOPT_POSTFIELDS, $paramsJson);

		$response = curl_exec($chFixPodPila);
		if (!$response || curl_getinfo($chAutoLiquidate, CURLINFO_HTTP_CODE != 200)) {
			$output->writeln('Fallo llamando fix pod pila');
			$output->writeln($response);
		} else {
			$output->writeln("Respuesta fix pod pila: " . PHP_EOL . $response);
		}

		$chSendPlanilla = curl_init("//$host:$port/api/public/v1/send/planilla/file/to/enlace/operativo/back");

		curl_setopt($chSendPlanilla, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chSendPlanilla, CURLOPT_HTTPHEADER, array('Content-type: application/json','Content-Length: ' . strlen($paramsJson)));
		curl_setopt($chSendPlanilla, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($chSendPlanilla, CURLOPT_POSTFIELDS, $paramsJson);
		
		do {
			$response = curl_exec($chSendPlanilla);
			if (!$response || curl_getinfo($chAutoLiquidate, CURLINFO_HTTP_CODE != 200)) {
				$output->writeln('Fallo llamando send planilla to enlace operativo');
				$output->writeln($response);
				$cont = 0;
			} else {
				$output->writeln("Respuesta send planilla to enlace operativo:");
				$output->writeln($response);
				$jsonR = json_decode($response, true);
				$cont = $jsonR['conta'];
				$output->writeln('cont: ' . $cont);
	//			$output->writeln("Respuesta send planilla to enlace operativo: " . $response);
			}
		} while($cont != 0);

		$output->writeln('<comment>Done!</comment>');
	}
}