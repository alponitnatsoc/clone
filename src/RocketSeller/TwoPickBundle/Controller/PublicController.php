<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PublicController extends Controller
{
	public function homeAction() {
        $user=$this->getUser();
        if (empty($user)) {
            return $this->render('RocketSellerTwoPickBundle:Public:home.html.twig');
        } else {
            return $this->redirectToRoute('show_dashboard');
        }
        
    }

    public function beneficiosAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:beneficios.html.twig', array(
            "breadcrumbs" => array("Inicio" => "/", "Beneficios" => "")
        ));   
    }

    public function calculadoraAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:calculadora.html.twig');   
    }

    public function preciosAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:precios.html.twig', array(
            "breadcrumbs" => array("Inicio" => "/", "Precios" => "")
        ));   
    }

    public function nosotrosAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:nosotros.html.twig', array(
            "breadcrumbs" => array("Inicio" => "/", "Nosotros" => "")
        ));   
    }

    public function ayudaAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:ayuda.html.twig');   
    }

    public function blogAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:blog.html.twig');   
    }

    public function contactenosAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:contactenos.html.twig', array(
            "breadcrumbs" => array("Inicio" => "/", "Contáctenos" => "")
        ));   
    }
}