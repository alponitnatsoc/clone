<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PagesController extends Controller
{
    public function indexAction()
    {
		/** @var User $user */
		$user=$this->getUser();
    	if ($user) {
    		$response = $user->getStatus()>=2 ? $this->forward('RocketSellerTwoPickBundle:DashBoardEmployer:showDashBoard') :$this->forward('RocketSellerTwoPickBundle:DashBoard:showDashBoard');
    	}else{
    		return $this->render('RocketSellerTwoPickBundle:General:landing_new.html.twig');    		
    	} 
    	return $response;    	
    }
}