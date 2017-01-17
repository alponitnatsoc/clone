<?php

namespace RocketSeller\TwoPickBundle\Command;

use RocketSeller\TwoPickBundle\Controller\PayrollRestController;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class RunCesantiasProcessCommand extends ContainerAwareCommand
{
	
	private $output;
	
	protected function configure()
	{
		$this
			->setName('symplifica:run:cesantias:process')
			->setDescription('Runs cesantias process for all users')
			->setHelp('Runs cesantias process for all users')
		;
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<comment>Running Cron Task RunCesantiasProcessCommand ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
		$this->output = $output;
		
		$em = $this->getContainer()->get('doctrine')->getManager();
		$eHERepo = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee");
		$eHEs = $eHERepo->findAll();
		/** @var EmployerHasEmployee $eHE */
		foreach ($eHEs as $eHE) {
			if($eHE->getState() >= 4 && $eHE->getActiveContract()->getState() == 1) {
				$request = new Request();
				$request->request->add(array(
					"cod_process" => '7',
					"employee_id" => $eHE->getIdEmployerHasEmployee(),
					"execution_type" => 'P'
				));
				$request->setMethod("POST");
				
				$controller = new PayrollRestController();
				$controller->setContainer($this->getContainer());
				$response = $controller->postProcessExecutionAction($request);
				
				if ($response->getStatusCode() != 200) {
					$output->writeln($eHE->getIdEmployerHasEmployee() . ': <error>Error</error> status' . $response->getStatusCode() );
				} else {
					$output->writeln($eHE->getIdEmployerHasEmployee() . ': <comment>Success</comment> ');
				}
			}
		}
		
		$output->writeln('<comment>Done RunCesantiasProcessCommand!</comment>');
	}
	
}
