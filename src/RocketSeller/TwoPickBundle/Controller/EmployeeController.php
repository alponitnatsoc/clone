<?php

namespace RocketSeller\TwoPickBundle\Controller;


use RocketSeller\TwoPickBundle\Form\PayMethod;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use RocketSeller\TwoPickBundle\Entity\Employee;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Beneficiary;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasBeneficiary;
use RocketSeller\TwoPickBundle\Form\EmployeeBeneficiaryRegistration;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Form\EmployerRegistration;
use RocketSeller\TwoPickBundle\Form\PersonEmployeeRegistration;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


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
            $form = $this->createForm(new EmployeeBeneficiaryRegistration(), $beneficiary);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $employeeBeneficiary = new EmployeeHasBeneficiary();
                $employeeBeneficiary->setEmployeeEmployee($employee);
                $employeeBeneficiary->setBeneficiaryBeneficiary($beneficiary);
                $em = $this->getDoctrine()->getManager();
                $em->persist($beneficiary);
                $em->flush();
                $em->persist($employeeBeneficiary);
                $em->flush();
                return $this->redirectToRoute('manage_employees');
            }
            return $this->render(
                'RocketSellerTwoPickBundle:Registration:addBeneficiary.html.twig',
                array('form' => $form->createView())
            );
        }
    }


    /**
    * Maneja el registro de un beneficiario a un empleado con los datos básicos,
    * @param el Request que maneja el form que se imprime
    * @return La vista de el formulario de la nueva persona
    **/
    public function manageBeneficiaryAction(Request $request, Employee $employee, Beneficiary $beneficiary=null) {
        if(is_null($beneficiary)) {
            $beneficiaries = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:EmployeeHasBeneficiary')
                ->findByEmployeeEmployee($employee);
                if($beneficiaries){
                    return $this->render(
                        'RocketSellerTwoPickBundle:Employee:employeeBeneficiary.html.twig',
                        array(
                            'beneficiaries' => $beneficiaries,
                            'employee' => $employee
                        )
                    );
                }
        } else {
            $form = $this->createForm(new EmployeeBeneficiaryRegistration(), $beneficiary);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $employeeBeneficiary = new EmployeeHasBeneficiary();
                $employeeBeneficiary->setEmployeeEmployee($employee);
                $employeeBeneficiary->setBeneficiaryBeneficiary($beneficiary);
                $em = $this->getDoctrine()->getManager();
                $em->persist($beneficiary);
                $em->flush();
                $em->persist($employeeBeneficiary);
                $em->flush();
                return $this->redirectToRoute('manage_employees');
            }
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
    * @return La vista de el formulario manager
    **/
    public function manageEmployeesAction()
    {
        $user=$this->getUser();
        $employeesData=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        return $this->render(
            'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig',array(
                'employees'=>$employeesData)
        );
    }
    /**
    * Maneja el formulario de un nuevo empleado
    * @param el Request y el Id del empleado, si lo desean editar
    * @return La vista de el formulario de la nuevo empleado
    **/
    public function newEmployeeAction( $id)
    {
        /** @var User $user */
        $user=$this->getUser();
        $employee=null;
        if ($id==-1) {
            $employee= new Employee();
        }else{
            $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
            //verify if the Id exists or it belongs to the logged user
            /** @var Employee $employee */
            $employee= $repository->find($id);
            /** @var EmployerHasEmployee $ee */
            $idEmployer=$user->getPersonPerson()->getEmployer()->getIdEmployer();
            $flag=false;
            foreach($employee->getEmployeeHasEmployers() as $ee){
                if($ee->getEmployerEmployer()->getIdEmployer()==$idEmployer){
                    $flag=true;
                    break;
                }
            }
            if($employee==null||!$flag){
                $employeesData=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                return $this->render(
                    'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig',array(
                    'employees'=>$employeesData));
            }
        }
        $userWorkplaces= $user->getPersonPerson()->getEmployer()->getWorkplaces();
        $form = $this->createForm(new PersonEmployeeRegistration($id,$userWorkplaces), $employee, array(
            'action' => $this->generateUrl('api_public_post_new_employee_submit'),
            'method' => 'POST',
        ));
        return $this->render(
            'RocketSellerTwoPickBundle:Registration:EmployeeForm.html.twig',
            array('form' => $form->createView())
        );
    }
    /**
     * Retorna los campos especificos para el metodo de pago solicitado
     *
     * @param $id
     * @return Response
     */
    public function postPayMethodAction($id){
        $repository=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PayMethodFields");
        $fields=$repository->findBy(array('payTypePayType'=> $id));
        $options=array();
        foreach($fields as $field){
            $options[]=$field;
        }
        $form = $this->createForm(new PayMethod($fields));
        return $this->render(
            'RocketSellerTwoPickBundle:Registration:generalFormRender.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Muestra los beneficiarios del empleado
     * @return la vista de los beneficiarios
     */
    public function showBeneficiaryAction($id)
    {

        $employee = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:Employee')
        ->find($id);
        if($employee){
            $beneficiaries = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:EmployeeHasBeneficiary')
            ->findByEmployeeEmployee($employee);            
            if(!$beneficiaries){
                return $this->redirectToRoute('manage_employees');
            }else{
                return $this->render(
                'RocketSellerTwoPickBundle:Employee:employeeBeneficiary.html.twig',
                array('beneficiaries' => $beneficiaries,
                      'employee' => $employee
                        ));
            }
        }else{
            return $this->redirectToRoute('manage_employees');
        }

    }
}
