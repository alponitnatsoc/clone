<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
class DashBoardController extends Controller
{	
	/**
    * Maneja el registro de una nueva persona con los datos básicos, 
    * TODO agregar todos los campos de los wireframes
    * @param el Request que manjea el form que se imprime
    * @return La vista de el formulario de la nueva persona
	**/
    public function showDashBoardAction(Request $request)
    {
        //¿Cómo vamos a hacer para saber en que parte del form está el usuario?
        //para el render se envía un array steps en el cuals e le puede agregar el estado el usuario
        $user=$this->getUser();
        $stateRegister=0;
        $stateEmployees=0;
        $employer=$user->getPersonPerson()->getEmployer();
        if ($user->getPersonPerson()->getEmployer()==null) {
            $stateRegister+=10;
            
        }else{
            $stateRegister+=100;
            $employees=$employer->getEmployerHasEmployees();
            $numEmployees=count($employees);
            $minUnit=100/($numEmployees*2);
            if ($numEmployees>0) {
                foreach ($employees as $key => $value) {
                    if (count($value->getContracts())>0) {
                        $stateEmployees+=$minUnit*2;
                    } else {
                        $stateEmployees+=$minUnit;
                    }
                }
            }
            
        }

        
        $step1 = array(
                'url' => "/register/complete", 
                'name' => "Datos del empleador",
                'state' => $stateRegister,
                'stateMessage' => "Continuar",);
        $step2 = array(
                'url' => "/manage/employees", 
                'name' => "Datos de los empleados",
                'state' => $stateEmployees,
                'stateMessage' => "Continuar",);
        $steps [] =$step1;
        $steps [] =$step2;
        return $this->render('RocketSellerTwoPickBundle:General:dashBoard.html.twig', 
            array('steps' => $steps ) );
    }
}
?>