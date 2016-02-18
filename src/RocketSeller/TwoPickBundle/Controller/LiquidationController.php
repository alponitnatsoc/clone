<?php

namespace RocketSeller\TwoPickBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use RocketSeller\TwoPickBundle\Entity\Liquidation;

use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\LiquidationMethodsTrait;
use RocketSeller\TwoPickBundle\Form\LiquidationType;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Traits\NoveltyTypeMethodsTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Traits\NoveltyMethodsTrait;

/**
 * Liquidation controller.
 *
 */
class LiquidationController extends Controller
{

    use EmployerHasEmployeeMethodsTrait;
    use LiquidationMethodsTrait;
    use NoveltyTypeMethodsTrait;
    use NoveltyMethodsTrait;

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
            'usernameEmployer' => $usernameEmployer,
            'idPerson' => $person->getIdPerson()
        );

        /** @var \RocketSeller\TwoPickBundle\Entity\Contract $contract */
        $contract = $this->getActiveContract($id);
        $startDate = $contract[0]->getStartDate();

        $frequency = $contract[0]->getPayMethodPayMethod()->getFrequencyFrequency()->getIdFrequency();

        $employerHasEmployee = $contract[0]->getEmployerHasEmployeeEmployerHasEmployee();

        $contractInfo = array(
            'contractType' => $contract[0]->getContractTypeContractType()->getName(),
            'contractPeriod' => $contract[0]->getTimeCommitmentTimeCommitment()->getName(),
            'salary' => $contract[0]->getSalary(),
            'vacationDays' => "",
            'startDay' => strftime("%d de %B de %Y", $startDate->getTimestamp()),
            'startDate' => $startDate,
            'idEmperHasEmpee' => $employerHasEmployee->getIdEmployerHasEmployee(),
            'frequency' => $frequency
        );

        $form = $this->createForm(new LiquidationType());

        /** @var Payroll $payroll */
        $payroll = $contract[0]->getActivePayroll();
        $novelties = $payroll->getNovelties();
//         echo count($novelties);
//         echo $payroll->getIdPayroll();
//         echo $contract[0]->getIdContract();
        $llamadosAtencion = $this->noveltiesByGroup("llamado_atencion");

        if ( !($liquidation = $this->liquidationByTypeAndEmHEmAndContract($id, 1, $contract[0]->getIdContract())) ) {
            $liquidation = new Liquidation();
            $liquidation->setContract($contract[0]);
    //         $liquidation->setCost($cost);
    //         $liquidation->setDaysToLiquidate($daysToLiquidate);
            $liquidation->setEmployerHasEmployee($employerHasEmployee);
    //         $liquidation->setIdPurchaseOrder($idPurchaseOrder);
    //         $liquidation->setLastWorkDay($lastWorkDay);
            $liquidationType = $em->getRepository('RocketSellerTwoPickBundle:LiquidationType')->findOneBy(array(
                "name" => "Definitiva"
            ));
            $liquidation->setLiquidationType($liquidationType);

            $em->persist($liquidation);
            $em->flush();
        }

        $id_liq = $liquidation->getId();

        return $this->render("RocketSellerTwoPickBundle:Liquidation:final.html.twig", array(
            "employeeInfo" => $employeeInfo,
            "contractInfo" => $contractInfo,
            "form" => $form->createView(),
            "payroll" => $payroll,
            "novelties" => $novelties,
            "llamadosAtencion" => $llamadosAtencion,
            "id_liq" => $id_liq
        ));
    }

    public function finalLiquidationStepsAction($id, $step, $id_liq)
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
            'usernameEmployer' => $usernameEmployer,
            'idPerson' => $person->getIdPerson()
        );

        /** @var \RocketSeller\TwoPickBundle\Entity\Contract $contract */
        $contract = $this->getActiveContract($id);
        $startDate = $contract[0]->getStartDate();

        $frequency = $contract[0]->getPayMethodPayMethod()->getFrequencyFrequency()->getIdFrequency();

        $employerHasEmployee = $contract[0]->getEmployerHasEmployeeEmployerHasEmployee();

        $contractInfo = array(
            'contractType' => $contract[0]->getContractTypeContractType()->getName(),
            'contractPeriod' => $contract[0]->getTimeCommitmentTimeCommitment()->getName(),
            'salary' => $contract[0]->getSalary(),
            'vacationDays' => "",
            'startDay' => strftime("%d de %B de %Y", $startDate->getTimestamp()),
            'startDate' => $startDate,
            'idEmperHasEmpee' => $employerHasEmployee->getIdEmployerHasEmployee(),
            'frequency' => $frequency
        );

        $form = $this->createForm(new LiquidationType());

        /** @var Payroll $payroll */
        $payroll = $contract[0]->getActivePayroll();
        $novelties = $payroll->getNovelties();
        //         echo count($novelties);
        //         echo $payroll->getIdPayroll();
        //         echo $contract[0]->getIdContract();
        $llamadosAtencion = $this->noveltiesByGroup("llamado_atencion");

        $liquidation = $this->liquidationDetail($id_liq);

        $liquidation->getId();

        return $this->render("RocketSellerTwoPickBundle:Liquidation:final-steps.html.twig", array(
            "employeeInfo" => $employeeInfo,
            "contractInfo" => $contractInfo,
            "form" => $form->createView(),
            "payroll" => $payroll,
            "novelties" => $novelties,
            "llamadosAtencion" => $llamadosAtencion
        ));
    }

    /**
     * @param string $employee_id - Id del empleado en SQL
     * @param integer $period - periodo de pago de nomina
     * @param integer $id - Id de la relacion employer_has_employee
     * @param integer $id_liq - Id de la liquidacion
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function finalLiquidationDetailAction($employee_id, $period, $id, $id_liq)
    {
        $format = array('_format' => 'json');

        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $this->getEmployee($id);
        /** @var \RocketSeller\TwoPickBundle\Entity\Person $person */
        $person = $employee->getPersonPerson();

        $employeeInfo = array(
            'name' => $person->getNames(),
            'lastName1' => $person->getLastName1(),
            'lastName2' => $person->getLastName2(),
            'documentType' => $person->getDocumentType(),
            'document' => $person->getDocument(),
            'docExpeditionPlace' => $person->getDocumentExpeditionPlace()
        );

        $employer = $this->getEmployer($id);
        $personEmployer = $employer->getPersonPerson();
        $employerInfo = array(
            'name' => $personEmployer->getNames(),
            'lastName1' => $personEmployer->getLastName1(),
            'lastName2' => $personEmployer->getLastName2(),
            'documentType' => $personEmployer->getDocumentType(),
            'document' => $personEmployer->getDocument()
        );

        /** @var \RocketSeller\TwoPickBundle\Entity\Contract $contract */
        $contract = $this->getActiveContract($id);
        $startDate = $contract[0]->getStartDate();

        $contractInfo = array(
            'contractType' => $contract[0]->getContractTypeContractType()->getName(),
            'contractPeriod' => $contract[0]->getTimeCommitmentTimeCommitment()->getName(),
            'salary' => $contract[0]->getSalary(),
            'vacationDays' => "",
            'startDay' => strftime("%d de %B de %Y", $startDate->getTimestamp()),
            'startDate' => $startDate,
            'id' => $contract[0]->getIdContract()
        );

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

        $totalLiq = $this->totalLiquidation($data);

        foreach ($data as $key => $liq) {
            $payroll_code = $liq["CON_CODIGO"];
            $noveltyType = $this->noveltyTypeByPayrollCode($payroll_code);

            if ($noveltyType) {
                $tmp[$key]["novelty"] = $noveltyType;
                $tmp[$key]["liq"] = $liq;
                switch ($noveltyType->getNaturaleza()):
                    case "DED":
                        $deducciones[] = $tmp[$key];
//                         $totalDeducciones += $this->totalLiquidation($tmp[$key]["liq"]);
                        break;
                    case "DEV":
                        $devengos[] = $tmp[$key];
//                         $totalDevengos += $this->totalLiquidation($tmp[$key]["liq"]);
                        break;
                    default:
                        break;
                endswitch;
            }
        }

        $em = $this->getDoctrine()->getManager();
        /**
         * Actualizar datos de liquidacion en DB
         * @var Liquidation $liquidation
         */
        $liquidation = $this->liquidationDetail($id_liq);
        $liquidation->setCost($totalLiq["total"]);
        $liquidation->setDetailLiquidation(json_encode($data));
        $liquidation->setPeriod($period);
        $em->persist($liquidation);
        $em->flush();

//         $this->get('knp_snappy.pdf')->generate('http://www.google.fr', '/path/to/the/file.pdf');
        $documentNumber = $employerInfo["document"];
        $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:getClientListPaymentmethods', array('documentNumber' => $documentNumber), array('_format' => 'json'));
//         var_dump($clientListPaymentmethods);
        $responcePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true);

        $html = $this->render("RocketSellerTwoPickBundle:Liquidation:detail-liquidation.html.twig", array(
            'data' => $data,
            'employeeInfo' => $employeeInfo,
            'contractInfo' => $contractInfo,
            'totalLiq' => $totalLiq["total"],
            'tmp' => $tmp,
            'devengos' => $devengos,
            'deducciones' => $deducciones,
            'employee_id' => $employee_id,
            'period' => $period,
            'totalDeducciones' => $totalLiq["totalDed"],
            'totalDevengos' => $totalLiq["totalDev"],
            'employer' => $employerInfo,
            'paymentMethods' => isset($responcePaymentsMethods["payments"]) ? $responcePaymentsMethods["payments"] : false,
            'id_liq' => $id_liq
        ));

        return $html;
    }

    public function generatePdfAction(Request $request)
    {
        $parameters = $request->request->all();

        $url = $parameters["url"];

        return new Response(
            $this->get('knp_snappy.pdf')->getOutput($url), 200, array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="file.pdf"'
            )
        );
    }

    public function payLiquidationAction($id, Request $request)
    {
        /** @var Liquidation $liquidation */
        $liquidation = $this->liquidationDetail($id);
        $total = $liquidation->getCost();

        $id_ehe = $liquidation->getEmployerHasEmployee()->getIdEmployerHasEmployee();
        $employee_id = $id_ehe . "9"; //@todo el 9 es para los mocks

        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $this->getEmployee($id_ehe);
        /** @var \RocketSeller\TwoPickBundle\Entity\Person $person */
        $person = $employee->getPersonPerson();

        $employeeInfo = array(
            'name' => $person->getNames(),
            'lastName1' => $person->getLastName1(),
            'lastName2' => $person->getLastName2(),
            'documentType' => $person->getDocumentType(),
            'document' => $person->getDocument(),
            'docExpeditionPlace' => $person->getDocumentExpeditionPlace()
        );

        $employer = $this->getEmployer($id_ehe);
        $personEmployer = $employer->getPersonPerson();
        $employerInfo = array(
            'name' => $personEmployer->getNames(),
            'lastName1' => $personEmployer->getLastName1(),
            'lastName2' => $personEmployer->getLastName2(),
            'documentType' => $personEmployer->getDocumentType(),
            'document' => $personEmployer->getDocument()
        );

        /** @var \RocketSeller\TwoPickBundle\Entity\Contract $contract */
        $contract = $this->getActiveContract($id_ehe);
        $startDate = $contract[0]->getStartDate();

        $contractInfo = array(
            'contractType' => $contract[0]->getContractTypeContractType()->getName(),
            'contractPeriod' => $contract[0]->getTimeCommitmentTimeCommitment()->getName(),
            'salary' => $contract[0]->getSalary(),
            'vacationDays' => "",
            'startDay' => strftime("%d de %B de %Y", $startDate->getTimestamp()),
            'startDate' => $startDate,
            'id' => $contract[0]->getIdContract()
        );

        $data = json_decode($liquidation->getDetailLiquidation(), true);

        $totalLiq = $this->totalLiquidation($data);

        foreach ($data as $key => $liq) {
            $payroll_code = $liq["CON_CODIGO"];
            $noveltyType = $this->noveltyTypeByPayrollCode($payroll_code);

            if ($noveltyType) {
                $tmp[$key]["novelty"] = $noveltyType;
                $tmp[$key]["liq"] = $liq;
                switch ($noveltyType->getNaturaleza()):
                case "DED":
                    $deducciones[] = $tmp[$key];
                    //                         $totalDeducciones += $this->totalLiquidation($tmp[$key]["liq"]);
                    break;
                case "DEV":
                    $devengos[] = $tmp[$key];
                    //                         $totalDevengos += $this->totalLiquidation($tmp[$key]["liq"]);
                    break;
                default:
                    break;
                endswitch;
            }
        }

        $period = $liquidation->getPeriod();


        return $this->render("RocketSellerTwoPickBundle:Liquidation:pay-liquidation-confirm.html.twig", array(
            "total" => $total,
            'employeeInfo' => $employeeInfo,
            'contractInfo' => $contractInfo,
            'totalLiq' => $totalLiq["total"],
            'tmp' => $tmp,
            'devengos' => $devengos,
            'deducciones' => $deducciones,
            'employee_id' => $employee_id,
            'period' => $period,
            'totalDeducciones' => $totalLiq["totalDed"],
            'totalDevengos' => $totalLiq["totalDev"],
            'employer' => $employerInfo,
            'id_liq' => $id
        ));
    }

    public function cartasLiquidacionAction($ref)
    {
        $html = $this->renderView('RocketSellerTwoPickBundle:Liquidation:carta-' . $ref . '.html.twig', array(
        ));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="fichero.pdf"'
            )
        );
    }
}