<?php

namespace RocketSeller\TwoPickBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use RocketSeller\TwoPickBundle\Entity\Liquidation;

use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\LiquidationMethodsTrait;
use RocketSeller\TwoPickBundle\Form\LiquidationType;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Employer;
use Symfony\Component\HttpFoundation\Request;

/**
 * Liquidation controller.
 *
 */
class LiquidationController extends Controller
{

    use EmployerHasEmployeeMethodsTrait;
    use LiquidationMethodsTrait;

    /**
     * Lists all Liquidation entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('RocketSellerTwoPickBundle:Liquidation')->findAll();

        return $this->render('RocketSellerTwoPickBundle:Liquidation:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a Liquidation entity.
     *
     */
    public function showAction($id)
    {
//         $em = $this->getDoctrine()->getManager();

//         $entity = $em->getRepository('RocketSellerTwoPickBundle:Liquidation')->find($id);
        $entity = $this->liquidationDetail($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Liquidation entity.');
        }

        return $this->render('RocketSellerTwoPickBundle:Liquidation:show.html.twig', array(
            'entity' => $entity
        ));
    }

    /**
     * List Liquidation by relation EmployerHasEmployee
     * @var Integer $id - Id de la relacion EmployerHasEmployee
     */
    public function listByEhEAction($id)
    {
        $entities = $this->showLiquidations($id);

        return $this->render("RocketSellerTwoPickBundle:Liquidation:list.html.twig", array(
            "entities" => $entities,
            "idEhE" => $id
        ));
    }

    /**
     * REalizar la liquidacion final de un empleado
     * @param integer $id - Id de la relacion EmployerHasEmployee
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function finalLiquidationAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $this->getEmployee($id);
        /** @var \RocketSeller\TwoPickBundle\Entity\Person $person */
        $person = $employee->getPersonPerson();
        /** @var Employer $employer */
        $employer = $this->getEmployer($id);
        $usernameEmployer = $employer->getPersonPerson()->getNames();

        $employeeInfo = array(
            'name' => $person->getNames(),
            'id' => $employee->getIdEmployee(),
            'idEmperHasEmpee' => $id,
            'lastName1' => $person->getLastName1(),
            'lastName2' => $person->getLastName2(),
            'document' => $person->getDocument(),
            'documentType' => $person->getDocumentType(),
            'docExpeditionPlace' => $person->getDocumentExpeditionPlace(),
            'usernameEmployer' => $usernameEmployer
        );

        /** @var \RocketSeller\TwoPickBundle\Entity\Contract $contract */
        $contract = $this->getActiveContract($id);
        $startDate = $contract[0]->getStartDate();

        $frequency = $contract[0]->getPayMethodPayMethod()->getFrequencyFrequency()->getIdFrequency();

        $contractInfo = array(
            'contractType' => $contract[0]->getContractTypeContractType()->getName(),
            'contractPeriod' => $contract[0]->getTimeCommitmentTimeCommitment()->getName(),
            'salary' => $contract[0]->getSalary(),
            'vacationDays' => "",
            'startDay' => strftime("%d de %B de %Y", $startDate->getTimestamp()),
            'startDate' => $startDate,
            'idEmperHasEmpee' => $contract[0]->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee(),
            'frequency' => $frequency
        );

        $form = $this->createForm(new LiquidationType());

        /** @var Payroll $payroll */
        $payroll = $contract[0]->getActivePayroll();
        $novelties = $payroll->getNovelties();
//         echo count($novelties);
//         echo $payroll->getIdPayroll();
//         echo $contract[0]->getIdContract();


        return $this->render("RocketSellerTwoPickBundle:Liquidation:final.html.twig", array(
            "employeeInfo" => $employeeInfo,
            "contractInfo" => $contractInfo,
            "form" => $form->createView(),
            "payroll" => $payroll,
            "novelties" => $novelties
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function finalLiquidationDetailAction($employee_id, $period)
    {
        $format = array('_format' => 'json');
//             $employee_id = $request->request->get("employee_id");
//             $period = $request->request->get("period");
        /**
         * Obtener datos de la preliquidacion antes de consolidarla
         */
        $response = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll', array(
                'employeeId' => $employee_id,
                'period' => $period
            ),
            $format
        );
        $data = json_decode($response->getContent(), true);

        return $this->render("RocketSellerTwoPickBundle:Liquidation:detail-liquidation.html.twig", array(
            'data' => $data
        ));
    }
}
