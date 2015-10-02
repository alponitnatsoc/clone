<?php
/**
 * @author Gabriel Montero <gabriel.montero@symplifica.com>
 * @link notyet
 */
namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class RegistroController extends Controller{
 	
 	/**
 	 * @param does not recive anything
     *
     * @throws notyet
     *
     * @return \Response with each form element that will be in the register of the user
     *
     * @Route("/register/user", name="userRegistration")
     */
 	public function registerUserAction(){
 		return $this->render('registroUsuario.html.twig', array ( 'userForm' => array( 
 			array ('name' => 'nombre', 'type' => 'text', 'place' => 'Nombre'),
 			array ('name' => 'Cedula', 'type' => 'number', 'place' => 'Cedula'),
 			array ('name' => 'apellido', 'type' => 'text', 'place' => 'Apellido')))
 		);
 	}
 	


 } 
  ?>