<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class LegalAssistanceController extends Controller
{
	public function indexAction(){
		return $this->render('RocketSellerTwoPickBundle:legalAssistance:index.html.twig');
	}
	public function startPayment(){
		return $this->render('RocketSellerTwoPickBundle:legalAssistance:startPayment.html.twig');
	}
}
