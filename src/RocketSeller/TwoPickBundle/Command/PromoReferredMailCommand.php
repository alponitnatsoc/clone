<?php

namespace RocketSeller\TwoPickBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use DateTime;

class PromoReferredMailCommand extends ContainerAwareCommand
{
	
	private $output;
	
	protected function configure()
	{
		$this
			->setName('symplifica:promo:referred:mail')
			->setDescription('Sends mail of promo referidos')
			->setHelp('The mail is sent 5 and 15 days after step 3 completed')
		;
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<comment>Running Cron Task PromoReferredMailCommand ' . date("Y/m/d h:i") . ' time_zone: ' . date_default_timezone_get() . '</comment>');
		$this->output = $output;
		
		$cronService = $this->getContainer()->get('app.symplifica_chrons');
		
		$response = $cronService->putPromoReferredMailAction();
		$this->printResponse($response, $output);
		$output->writeln('<comment>Done PromoReferredMailCommand!</comment>');
	}
	
	private function printResponse($response, OutputInterface $output) {
		if ($response->getStatusCode() != 200) {
			$output->writeln('<error>Error calling service</error>');
		} else {
			foreach ($response->getData() as $userResponse) {
				$output->write("Email Sent to userId " . $userResponse['userId'] . " : ");
				$output->writeln($userResponse['resultMail'] ? 'true' : 'false');
			}
		}
	}
}
