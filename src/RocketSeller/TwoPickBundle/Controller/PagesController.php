<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PagesController extends Controller
{
    public function indexAction()
    {
    	if ($this->getUser()) {
    		$response = $this->forward('RocketSellerTwoPickBundle:DashBoard:showDashBoard');        		       	
    	}else{
    		return $this->render('RocketSellerTwoPickBundle:General:landing_new.html.twig');    		
    	} 
    	return $response;    	
    }
}