<?php
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Person;
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
}
