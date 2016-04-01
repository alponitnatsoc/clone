<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DashBoardController extends Controller
{

    /**
     * Maneja el registro de una nueva persona con los datos básicos,
     * TODO agregar todos los campos de los wireframes
     * @param el Request que manjea el form que se imprime
     * @return La vista de el formulario de la nueva persona
     * */
    public function showDashBoardAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        //para el render se envía un array steps en el cuals e le puede agregar el estado el usuario
        /* @var User $user  */
        $user = $this->getUser();
        if($user->getStatus()>=2 )
            return $this->forward('RocketSellerTwoPickBundle:DashBoardEmployer:showDashBoard');
        $paymentState = $user->getPaymentState();
        $stateRegister = 0;
        $stateEmployees = 0;
        $stateAfiliation = 0;
        $idCurrentEmployee = -1;
        /** @var Employer $employer */
        $employer = $user->getPersonPerson()->getEmployer();
        if ($employer == null) {
            $stateRegister+=10;
        } else {
            //el registro del empleador está completo
            $stateRegister = $employer->getRegisterState();
            //ahora vamos a ver el de los empleados
            $employees = $employer->getEmployerHasEmployees();
            //se calcula cuantos empleados tenemos 0 ó *
            $numEmployees = count($employees);
            //para agregar procentajes respectivos la minima unidad es el registro del 
            //empleado sin el contrato, cuando se le agrega el contrato es otra unidad 
            //minima de un 100%
            //si existen empleados se puede empezar a subir el 0%
            if ($numEmployees > 0) {
                $minUnit = 100 / ($numEmployees + 1);
                /** @var EmployerHasEmployee $value */
                foreach ($employees as $key => $value) {
                    //para cada empleado se mira si tiene por lo menos 1 contrato
                    if($value->getEmployeeEmployee()->getRegisterState()!=100){
                        $idCurrentEmployee=$value->getEmployeeEmployee()->getIdEmployee();
                    }
                    $stateEmployees+=$value->getEmployeeEmployee()->getRegisterState();
                    if ($value->getEmployeeEmployee()->getEntities()->count() != 0) {
                    }
                }
                if ($employer->getEntities()->count() > 0) {
                }
                $stateEmployees = $stateEmployees / $numEmployees;
            }
        }


        $step1 = array(
            'url' => $paymentState==1?"":$this->generateUrl('edit_profile'),
            'name' => "Mis datos como empleador",
            'state' => $stateRegister,
            'paso' => 1,
            'boxStyle' => "big",
            'stateMessage' => $stateRegister != 100 ? "Iniciar" : "Editar",);
        $steps ['0'] = $step1;
        $step2 = array(
            'url' => $paymentState==1? "" :($stateRegister != 100 ? "" :($stateEmployees==0?$this->generateUrl('register_employee', array('id' => -1)):$this->generateUrl('manage_employees'))),
            'name' => "Datos de mis empleados",
            'state' => $stateEmployees,
            'paso' => 2,
            'boxStyle' => "big",
            'stateMessage' => $stateEmployees != 100 ? "Iniciar" : "Editar",);
        $steps ['1'] = $step2;

        if ($stateEmployees == 100&&$paymentState!=1) {
            $step3 = array(
                'url' => $this->generateUrl('register_employee', array('id' => -1)),
                'name' => "Agregar un nuevo Empleado?",
                'state' => 0,
                'paso' => 2,
                'boxStyle' => "small",
                'stateMessage' => "Iniciar",);
            $steps ['2'] = $step3;
        }
        $step4 = array(
            'url' => $paymentState==1?"":($stateEmployees != 100 ? "" : $this->generateUrl('subscription_choices')),
            'name' => "Subscripción a Symplifica",
            'paso' => 3,
            'state' => $paymentState ? 100 : 0,
            'boxStyle' => "big",
            'stateMessage' => !$paymentState ? "Iniciar" : "Editar",);
        $steps ['3'] = $step4;

        $step5 = array(
            'url' => $paymentState != 1 ? "" : $this->generateUrl('matrix_choose'),
            'name' => "Finalizar afiliación",
            'paso' => 4,
            'state' => $stateAfiliation,
            'boxStyle' => "big",
            'stateMessage' => $stateAfiliation != 100 ? "Iniciar" : "Editar",);
        $steps ['4'] = $step5;

        return $this->render('RocketSellerTwoPickBundle:General:dashBoard.html.twig', array('steps' => $steps));
    }

}

?>