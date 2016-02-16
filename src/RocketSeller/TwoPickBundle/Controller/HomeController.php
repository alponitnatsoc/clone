<?php
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\HelpCategory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{

    public function productsAction()
    {
        return $this->render('RocketSellerTwoPickBundle:General:products.html.twig');
    }
    public function landingAction()
    {
        return $this->render('RocketSellerTwoPickBundle:General:landing.html.twig');
    }
    public function newLandingAction()
    {
        return $this->render('RocketSellerTwoPickBundle:General:landing_new.html.twig');
    }
    public function helpAction()
    {
		$helpCategories = $this->getDoctrine()
		->getRepository('RocketSellerTwoPickBundle:HelpCategory')
		->findAll();
    	return $this->render('RocketSellerTwoPickBundle:General:help.html.twig',array('helpCategories'=>$helpCategories));
    }
    public function helpDetailAction($id)
    {
		$helpCategory = $this->getDoctrine()
		->getRepository('RocketSellerTwoPickBundle:HelpCategory')
		->find($id);
		return $this->render('RocketSellerTwoPickBundle:General:helpDetail.html.twig',array('helpCategory'=>$helpCategory));
    }
    public function registerExpressInfoAction()
    {
        return $this->render('RocketSellerTwoPickBundle:General:infoExpressRegister.html.twig');
    }

    public function homeAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:home.html.twig');   
    }

}
