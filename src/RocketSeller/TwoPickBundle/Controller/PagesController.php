<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class PagesController extends Controller
{
    public function indexAction()
    {
		/** @var User $user */
		$user=$this->getUser();
    	if ($user) {
    		return $this->redirectToRoute('show_dashboard');
    	}else{
    		return $this->render('RocketSellerTwoPickBundle:General:landing_new.html.twig');    		
    	} 
    	return $response;    	
    }
}