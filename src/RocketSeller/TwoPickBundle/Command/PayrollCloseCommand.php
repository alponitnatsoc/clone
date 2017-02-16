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
		
		if($day != 13 && $day != 26) {
			$output->writeln('<comment>Done today is not the day!</comment>');
			return;
		}
		
		if($day == 13){
			$period = 2;
		}
		if($day == 26) {
			$period = 4;
		}
		
		$fullHost = "127.0.0.1:8000";
		if($this->getContainer()->getParameter('ambiente') == "produccion") {
			$fullHost = "https://symplifica.com";
		}
		/** @var User $backUser */
		$backUser = $this->getContainer()->get('doctrine')->getRepository("RocketSellerTwoPickBundle:User")
		    ->findOneBy(array('emailCanonical' => 'backofficesymplifica@gmail.com'));
		$parameters = array(
		  "token" => $backUser->getSalt(),
		  "month" => $month,
		  "year" => $year,
		  "day" => $day,
		  "period" => $period
		);
		
		$paramsJson = json_encode($parameters);
		$chAutoLiquidate = curl_init("$fullHost/api/public/v1/auto/liquidate/payroll");
		
		curl_setopt($chAutoLiquidate, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chAutoLiquidate, CURLOPT_HTTPHEADER, array('Content-type: application/json','Content-Length: ' . strlen($paramsJson)));
		curl_setopt($chAutoLiquidate, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($chAutoLiquidate, CURLOPT_POSTFIELDS, $paramsJson);
		
		
		do {
			$response = curl_exec($chAutoLiquidate);
			if ($response == null || curl_getinfo($chAutoLiquidate, CURLINFO_HTTP_CODE) != 200) {
				$output->writeln('Fallo llamando auto liquidate status: ' . curl_getinfo($chAutoLiquidate, CURLINFO_HTTP_CODE));
				$output->writeln($response);
				$cont = 0;
			} else {
				$output->writeln("Respuesta auto liquidate: ");
				$jsonR = json_decode($response, true);
				
				$cont = $jsonR['cont'];
				$output->writeln($response);
				$output->writeln('cont: ' . $cont);
			}
			
		} while($cont != 0);
		
		$parameters2 = array(
		  "period" => $period,
		  "month" => $month,
		  "year" => $year
		);

		$paramsJson = json_encode($parameters2);

		$chFixPodPila = curl_init("$fullHost/api/public/v1/fix/p/o/d/pila");

		curl_setopt($chFixPodPila, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chFixPodPila, CURLOPT_HTTPHEADER, array('Content-type: application/json','Content-Length: ' . strlen($paramsJson)));
		curl_setopt($chFixPodPila, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($chFixPodPila, CURLOPT_POSTFIELDS, $paramsJson);

		$response = curl_exec($chFixPodPila);
		if (curl_getinfo($chAutoLiquidate, CURLINFO_HTTP_CODE) != 200) {
			$output->writeln('Fallo llamando fix pod pila status: ' . curl_getinfo($chAutoLiquidate, CURLINFO_HTTP_CODE));
			$output->writeln($response);
		} else {
			$output->writeln("Respuesta fix pod pila: ");
			$output->writeln($response);
		}

		$chSendPlanilla = curl_init("$fullHost/api/public/v1/send/planilla/file/to/enlace/operativo/back");

		curl_setopt($chSendPlanilla, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chSendPlanilla, CURLOPT_HTTPHEADER, array('Content-type: application/json','Content-Length: ' . strlen($paramsJson)));
		curl_setopt($chSendPlanilla, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($chSendPlanilla, CURLOPT_POSTFIELDS, $paramsJson);
		
		do {
			$response = curl_exec($chSendPlanilla);
			if (!$response || curl_getinfo($chAutoLiquidate, CURLINFO_HTTP_CODE) != 200) {
				$output->writeln('Fallo llamando send planilla to enlace operativo status: ' . curl_getinfo($chAutoLiquidate, CURLINFO_HTTP_CODE));
				$output->writeln($response);
				$cont = 0;
			} else {
				$output->writeln("Respuesta send planilla to enlace operativo:");
				$jsonR = json_decode($response, true);
				$cont = $jsonR['conta'];
				$output->writeln('cont: ' . $cont);
			}
		} while($cont != 0);

		$output->writeln('<comment>Done!</comment>');
	}
}