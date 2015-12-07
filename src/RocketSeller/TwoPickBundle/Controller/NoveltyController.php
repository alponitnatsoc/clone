<?php
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NoveltyController extends Controller
{

    public function addNoveltyAction($idPayroll)
    {
        return $this->render('RocketSellerTwoPickBundle:General:products.html.twig');
    }
}
