<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PagesController extends Controller
{
    public function indexAction()
    {
        return $this->render('RocketSellerTwoPickBundle:Default:index.html.twig');
    }
}
?>