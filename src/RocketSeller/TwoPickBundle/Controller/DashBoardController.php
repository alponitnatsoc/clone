<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
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
        $idCurrentEmployee=-1;
        /** @var Employer $employer */
        $employer=$user->getPersonPerson()->getEmployer();
        if ($employer==null) {
            $stateRegister+=10;
            
        }else{
            //el registro del empleador está completo
            $stateRegister=$employer->getRegisterState();
            //ahora vamos a ver el de los empleados
            $employees=$employer->getEmployerHasEmployees();
            //se calcula cuantos empleados tenemos 0 ó *
            $numEmployees=count($employees);
            //para agregar procentajes respectivos la minima unidad es el registro del 
            //empleado sin el contrato, cuando se le agrega el contrato es otra unidad 
            //minima de un 100%
            
            //si existen empleados se puede empezar a subir el 0%
            if ($numEmployees>0) {
                $minUnit=100/($numEmployees*2);
                /** @var EmployerHasEmployee $value */
                foreach ($employees as $key => $value) {
                    //para cada empleado se mira si tiene por lo menos 1 contrato
                    if (count($value->getContracts())>0) {
                        $stateEmployees+=$minUnit*2;
                    } else {
                        $idCurrentEmployee=$value->getEmployeeEmployee()->getIdEmployee();
                        //si nó el contrato todavía no se ha diligenciado y solo se
                        //puede sumar 1 unidad minima al porcentaje
                        $stateEmployees+=$minUnit;
                    }
                }
            }
            
        }

        
        $step1 = array(
                'url' => $this->generateUrl('edit_profile'), 
                'name' => "Datos del empleador",
                'state' => $stateRegister,
                'stateMessage' => "Continuar",);
        $step2 = array(
                'url' => $this->generateUrl('register_employee', array('id'=>$idCurrentEmployee)),
                'name' => "Datos de los empleados",
                'state' => $stateEmployees,
                'stateMessage' => "Continuar",);

        $step4 = array(
            'url' => $this->generateUrl('matrix_choose'),
            'name' => "Datos de los empleados",
            'state' => $stateEmployees,
            'stateMessage' => "Continuar",);
        $steps ['0'] =$step1;
        $steps ['1'] =$step2;
        if($stateEmployees==100){
            $step3 = array(
                'url' => $this->generateUrl('register_employee', array('id'=>$idCurrentEmployee)),
                'name' => "Agregar un nuevo Empleado?",
                'state' => 0,
                'stateMessage' => "Iniciar",);
            $steps ['2'] =$step3;

        }
        $steps ['3'] =$step4;
        return $this->render('RocketSellerTwoPickBundle:General:dashBoard.html.twig',
            array('steps' => $steps ) );
    }
}
?>