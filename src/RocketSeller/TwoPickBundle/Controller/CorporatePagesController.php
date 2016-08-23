<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CorporatePagesController extends Controller
{
    public function nosotrosAction()
    {
        return $this->render('RocketSellerTwoPickBundle:CorporatePages:nosotros.html.twig', array(
                // ...
            ));    }

    public function terminosCondicionesAction()
    {
        return $this->render('RocketSellerTwoPickBundle:CorporatePages:terminos-condiciones.html.twig', array(
                // ...
            ));    }

    public function politicaPrivacidadAction()
    {
        return $this->render('RocketSellerTwoPickBundle:CorporatePages:politicas-de-privacidad.html.twig', array(
                // ...
            ));    }

}
