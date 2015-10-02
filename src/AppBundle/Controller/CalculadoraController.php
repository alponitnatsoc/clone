<?php
namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CalculadoraController extends Controller{
 	private $lastValue;
 	public function __construct(){
 		$lastValue="NaN";
 	}
 	private function add($a, $b){
	 	$lastValue= (is_numeric($a)&&is_numeric($b)) ? $a+$b: "Non numeric Values"; 
	 	return $lastValue;

	}
 	/**
     * @Route("/calc/{number1}/{number2}", name="calculator")
     */
 	public function operarAction($number1,$number2){
 		return $this->render('calc.html.twig',array('operationResponse' => CalculadoraController::add($number1,$number2) ));
 	}
 	


 } 
  ?>