<?php

namespace RocketSeller\TwoPickBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use RocketSeller\TwoPickBundle\Entity\Employee;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\Beneficiary;
use RocketSeller\TwoPickBundle\Form\PersonBeneficiaryRegistration;

/**
 * Employee controller.
 *
 */
class EmployeeController extends Controller
{

    /**
     * Lists all Employee entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('RocketSellerTwoPickBundle:Employee')->findAll();

        return $this->render('RocketSellerTwoPickBundle:Employee:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a Employee entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RocketSellerTwoPickBundle:Employee')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Employee entity.');
        }

        return $this->render('RocketSellerTwoPickBundle:Employee:show.html.twig', array(
            'entity'      => $entity,
        ));
    }

    /**
    * Maneja el registro de un beneficiario a un empleado con los datos básicos,
    * @param el Request que maneja el form que se imprime
    * @return La vista de el formulario de la nueva persona
    **/
    public function addBeneficiaryAction(Request $request, Employee $employee, Entity $entity=null) {

        if(is_null($entity)) {
            $entities = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity')
                ->findByEmployeeEmployee($employee);
            return $this->render(
                'RocketSellerTwoPickBundle:Registration:addBeneficiarySelectEntity.html.twig',
                array(
                    'entities' => $entities,
                    'employee' => $employee
                )
            );
        } else {
            $beneficiary = new Beneficiary();
            $form = $this->createForm(new PersonBeneficiaryRegistration(), $beneficiary);
            return $this->render(
                'RocketSellerTwoPickBundle:Registration:addBeneficiary.html.twig',
                array('form' => $form->createView())
            );
        }
    }
        /**
    * el dashboard de los empleados de cada empleador que le permite editar la información
    * y agregar nuevos empleados
    * TODO eliminar empleados
    * @param el Request que manjea el form que se imprime
    * @return La vista de el formulario manager
    **/
    public function manageEmployeesAction(Request $request)
    {
        $user=$this->getUser();
        $employeesData=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        return $this->render(
            'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig',array(
                'employees'=>$employeesData)
        );
    }
}
