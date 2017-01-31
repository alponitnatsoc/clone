<?php

namespace RocketSeller\TwoPickBundle\Controller;


use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
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
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Traits\NotificationMethodsTrait;
use RocketSeller\TwoPickBundle\Entity\NoveltyType;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    use NotificationMethodsTrait;

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
     * @param integer $id - Liquidation ID
     * @param integer (1|2|3) $type - Tipo de respuesta que se espera
     *              1 - Si es HTML para imprimir
     *              2 - Respuesta en PDF
     *              3 - Enviar por correo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, $id, $type)
    {
        /** @var Liquidation $liquidation */
        $liquidation = $this->liquidationDetail($id);

        $employerHasEmployee = $liquidation->getEmployerHasEmployee();

        $data = json_decode($liquidation->getDetailLiquidation(), true);
        $totalLiq = $this->totalLiquidation($data);

        foreach ($data as $key => $liq) {
            $payroll_code = $liq["CON_CODIGO"];
            /** @var NoveltyType $noveltyType */
            $noveltyType = $this->noveltyTypeByPayrollCode($payroll_code);

            if ($noveltyType) {
                $tmp[$key]["novelty"]["name"] = $noveltyType->getName();
                $tmp[$key]["liq"] = $liq;
                switch ($noveltyType->getNaturaleza()):
                    case "DED":
                        $deducciones[] = $tmp[$key];
                        break;
                    case "DEV":
                        $devengos[] = $tmp[$key];
                        break;
                    default:
                        break;
                endswitch;
            }
        }

        $id_ehe = $employerHasEmployee->getIdEmployerHasEmployee();

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

        $data = array(
            "employeeInfo" => $employeeInfo,
            "contractInfo" => $contractInfo,
            "deducciones" => $deducciones,
            "totalDeducciones" => $totalLiq["totalDed"],
            "devengos" => $devengos,
            "totalDevengos" => $totalLiq["totalDev"],
            "totalLiq" => $totalLiq["total"],
            "employer" => $employerInfo
        );

        switch ($type):
            case 1:
                return $this->render("RocketSellerTwoPickBundle:Liquidation:liquidation-pdf.html.twig",
                    $data
                );
                break;
            case 2:
                $html = $this->renderView('RocketSellerTwoPickBundle:Liquidation:liquidation-pdf.html.twig',
                    $data
                );

                return new Response(
                    $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                    200,
                    array(
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="liquidacion-' . $employeeInfo["document"] . '-' . $id . '.pdf"'
                    )
                );
                break;
            case 3:
                $method = $request->getMethod();

                if ($method == 'POST') {
                    $params = $request->request->all();

                    $toEmail = $params["toEmail"];
                } else {
                    $toEmail = "plinio.romero@symplifica.com";
                }

                $path = $this->get('kernel')->getRootDir(). "/../web/public/docs/tmp/liquidations/" . $employeeInfo["document"] . "-" . $id . ".pdf";

                if (!file_exists($path)) {

                    $this->get('knp_snappy.pdf')->generateFromHtml(
                        $this->renderView(
                            'RocketSellerTwoPickBundle:Liquidation:liquidation-pdf.html.twig',
                            $data
                        ),
                        $path
                    );

                }

                /** @var \RocketSeller\TwoPickBundle\Mailer\TwigSwiftMailer $smailer */
                $smailer = $this->get('symplifica.mailer.twig_swift');

                $fromEmail = $this->getUser()->getEmail();

                $send = $smailer->sendEmail($this->getUser(), "RocketSellerTwoPickBundle:Liquidation:send-email.txt.twig", $fromEmail, $toEmail, $path);
                if ($send) {
                }

                $response = new JsonResponse();
                $response->setData(array(
                    'data' => $send
                ));
                $response->setStatusCode(200);

                return $response;

                break;
            default:
                break;
        endswitch;
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
    	/** @var User $user */
	    $user = $this->getUser();
	    
        $em = $this->getDoctrine()->getManager();
        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $this->getEmployee($id);
        /** @var \RocketSeller\TwoPickBundle\Entity\Person $person */
        $person = $employee->getPersonPerson();
        /** @var Employer $employer */
        $employer = $this->getEmployer($id);
        $employerPerson = $employer->getPersonPerson();
	    
	    if($employerPerson != $user->getPersonPerson()) {
	    	return $this->redirectToRoute('show_dashboard');
	    }
	    
        $usernameEmployer = $employerPerson->getNames();

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

        $nowDate = new \DateTime();
        $diff = $nowDate->diff($contract[0]->getTestPeriod());

        $isTestPeriod = true;
        if ($diff->invert > 0) {
            $isTestPeriod = false;
        }

        $frec = $contract[0]->getFrequencyFrequency();
        $frequency = null;
        if ($frec){
            $frequency = $frec->getIdFrequency();
        }

        $employerHasEmployee = $contract[0]->getEmployerHasEmployeeEmployerHasEmployee();

        $contractInfo = array(
            'contractType' => $contract[0]->getContractTypeContractType()->getName(),
            'contractPeriod' => $contract[0]->getTimeCommitmentTimeCommitment()->getName(),
            'salary' => $contract[0]->getSalary(),
            'vacationDays' => "",
            'startDay' => $startDate,
            'startDate' => $startDate,
            'idEmperHasEmpee' => $employerHasEmployee->getIdEmployerHasEmployee(),
            'frequency' => $frequency,
            'testPeriod' => $isTestPeriod
        );

        $form = $this->createForm(new LiquidationType());

        /** @var Payroll $payroll */
        $payroll = $contract[0]->getActivePayroll();
        $novelties = null;
        if ($payroll) {
            $novelties = $payroll->getNovelties();
        }
        $llamadosAtencion = $this->noveltiesByGroup("Suspensión");

        if ( !($liquidation = $this->liquidationByTypeAndEmHEmAndContract($id, 1, $contract[0]->getIdContract())) ) {
            $liquidation = new Liquidation();
            $liquidation->setContract($contract[0]);
            $liquidation->setEmployerHasEmployee($employerHasEmployee);
            $liquidationType = $em->getRepository('RocketSellerTwoPickBundle:LiquidationType')->findOneBy(array(
                "name" => "Definitiva"
            ));
            $liquidation->setLiquidationType($liquidationType);

            $em->persist($liquidation);
            $em->flush();
        }

        $id_liq = $liquidation->getId();

        $repoDocType = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:DocumentType");
        /** @var DocumentType $docType */
        $docType = $repoDocType->findOneBy(array(
            "name" => "Carta de renuncia"
        ));

        $em->flush();

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
        $employerPerson = $employer->getPersonPerson();
        $usernameEmployer = $employerPerson->getNames();

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

        $frequency = $contract[0]->getFrequencyFrequency()->getIdFrequency();

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

        /** @var Payroll $payroll */
        $payroll = $contract[0]->getActivePayroll();
        $novelties = $payroll->getNovelties();
        $llamadosAtencion = $this->noveltiesByGroup("llamado_atencion");

        /** @var Liquidation $liquidation */
        $liquidation = $this->liquidationDetail($id_liq);

        /** @var Notification $notification */
        $notification = $this->notificationByPersonLiquidation($id_liq, $employerPerson->getIdPerson());
        $relatedLink = null;
        if ($notification) {
            $relatedLink = $notification->getRelatedLink();
        }

        $form = $this->createForm(new LiquidationType());

        return $this->render("RocketSellerTwoPickBundle:Liquidation:final-steps.html.twig", array(
            "employeeInfo" => $employeeInfo,
            "contractInfo" => $contractInfo,
            "form" => $form->createView(),
            "payroll" => $payroll,
            "novelties" => $novelties,
            "llamadosAtencion" => $llamadosAtencion,
            "id_liq" => $id_liq,
            "relatedLink" => $relatedLink,
            "liquidationReason" => $liquidation->getLiquidationReason()->getPayrollCode()
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
        $endDate = $contract[0]->getEndDate();
        $endDay = null;
        if ($endDate) {
            $endDay = strftime("%d de %B de %Y", $endDate->getTimestamp());
        }

        $contractInfo = array(
            'contractType' => $contract[0]->getContractTypeContractType()->getName(),
            'contractPeriod' => $contract[0]->getTimeCommitmentTimeCommitment()->getName(),
            'salary' => $contract[0]->getSalary(),
            'vacationDays' => "",
            'startDay' => strftime("%d de %B de %Y", $startDate->getTimestamp()),
            'startDate' => $startDate,
            'endDay' => $endDay,
            'endDate' => $endDate,
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
                        break;
                    case "DEV":
                        $devengos[] = $tmp[$key];
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

        $lastWorkDay = $liquidation->getLastWorkDay();

//         $documentNumber = $employerInfo["document"];
//         $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:getClientListPaymentmethods', array('documentNumber' => $documentNumber), array('_format' => 'json'));
//         $responsePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true);

        $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods', array('idUser' => $this->getUser()->getId()), array('_format' => 'json'));
        $responsePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true);

        $paymentMethodsType = array();

        if (isset($responsePaymentsMethods["payment-methods"]) && is_array($responsePaymentsMethods["payment-methods"])) {
            foreach ($responsePaymentsMethods["payment-methods"] as $pmt) {
                if (isset($pmt['bank']) && $pmt['bank'] != null) {
                    $paymentMethodsType['bankAccounts'][] = $pmt;
                } else {
                    $paymentMethodsType['creditCards'][] = $pmt;
                }
            }
        }

        $html = $this->render("RocketSellerTwoPickBundle:Liquidation:detail-liquidation.html.twig", array(
            'data' => $data,
            'employeeInfo' => $employeeInfo,
            'contractInfo' => $contractInfo,
            'totalLiq' => $totalLiq["total"],
            'devengos' => ( isset($devengos) )?$devengos:null,
            'deducciones' => ( isset($deducciones) )?$deducciones:null,
            'employee_id' => $employee_id,
            'period' => $period,
            'totalDeducciones' => $totalLiq["totalDed"],
            'totalDevengos' => $totalLiq["totalDev"],
            'employer' => $employerInfo,
//             'paymentMethods' => isset($responsePaymentsMethods["payment-methods"]) ? $responsePaymentsMethods["payment-methods"] : false,
            'paymentMethods' => $paymentMethodsType,
            'id_liq' => $id_liq,
            'lastWorkDay' => $lastWorkDay
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
        $employee_id = $id_ehe; //@todo el 9 es para los mocks

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

        $tmp = $devengos = $deducciones = null;
        foreach ($data as $key => $liq) {
            $payroll_code = $liq["CON_CODIGO"];
            /** @var NoveltyType $noveltyType */
            $noveltyType = $this->noveltyTypeByPayrollCode($payroll_code);

            if ($noveltyType) {
                $tmp[$key]["novelty"]["name"] = $noveltyType->getName();
                $tmp[$key]["liq"] = $liq;
                switch ($noveltyType->getNaturaleza()):
                    case "DED":
                        $deducciones[] = $tmp[$key];
                        break;
                    case "DEV":
                        $devengos[] = $tmp[$key];
                        break;
                    default:
                        break;
                endswitch;
            }
        }

        $period = $liquidation->getPeriod();

        $viewData = array(
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
        );

        // Inactivar empleado
        $em = $this->getDoctrine()->getManager();
        $employerHasEmployee = $liquidation->getEmployerHasEmployee()->setState(-1);
        $em->persist($employerHasEmployee);
        $em->flush();

        return $this->render("RocketSellerTwoPickBundle:Liquidation:pay-liquidation-confirm.html.twig",
            $viewData
        );
    }

    public function cartasLiquidacionAction($ref, $id)
    {
        /** @var Liquidation $liquidation */
        $liquidation = $this->liquidationDetail($id);
        $fechaFin = $liquidation->getLastWorkDay();
        $empHasEmpe = $liquidation->getEmployerHasEmployee();
        $empleador = $empHasEmpe->getEmployerEmployer();
        $empleado = $empHasEmpe->getEmployeeEmployee();
        $contacto = $liquidation->getContract();

        $data = array(
            'endDate' => strftime("%d de %B de %Y", $fechaFin->getTimestamp()) ,
            'employer' => array(
                'name' => utf8_encode($empleador->getPersonPerson()->getNames()),
                'document' => $empleador->getPersonPerson()->getDocument(),
                'documentType' => utf8_encode($empleador->getPersonPerson()->getDocumentType())
            ),
            'employee' => array(
                'name' => utf8_encode($empleado->getPersonPerson()->getNames()),
                'document' => $empleado->getPersonPerson()->getDocument(),
                'documentType' => utf8_encode($empleado->getPersonPerson()->getDocumentType())
            ),
            'contract' => array(
                'position' => utf8_encode($contacto->getPositionPosition()->getName()),
                'startDate' => strftime("%d de %B de %Y", $contacto->getStartDate()->getTimestamp()),
                'city' => ($contacto->getWorkplaceWorkplace()->getCity()->getName())
            )
        );

        $html = $this->renderView('RocketSellerTwoPickBundle:Liquidation:carta-' . $ref . '.html.twig', array(
            'data' => $data
        ));

//         return $this->render('RocketSellerTwoPickBundle:Liquidation:carta-' . $ref . '.html.twig', array(
//             'data' => $data
//         ));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $ref .'-' . $data["employee"]["document"] . '.pdf"'
            )
        );
    }
	
	public function correoLiquidacionAction($eheId)
	{
		$em = $this->getDoctrine()->getManager();
		
		/** @var EmployerHasEmployee $ehe */
		$ehe = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->findOneBy(array("idEmployerHasEmployee" => $eheId));
		
		$employer = $ehe->getEmployerEmployer();
		$employee = $ehe->getEmployeeEmployee();
		
		/** @var User $user */
		$user = $this->getUser();
		
		if($user->getPersonPerson()->getIdPerson() != $employer->getPersonPerson()->getIdPerson()){
			return $this->redirectToRoute('show_dashboard');
		}

        $context=array(
            'emailType'=>'liquidation',
            'toEmail'=>'servicioalcliente@symplifica.com',
            'userName'=>$employer->getPersonPerson()->getFullName(),
            'employerSociety'=> $employer->getIdSqlSociety(),
            'documentNumber'=>$employer->getPersonPerson()->getDocument(),
            'userEmail'=>$employer->getPersonPerson()->getEmail(),
            'phone'=>$employer->getPersonPerson()->getPhones()->first()->getPhoneNumber(),
            'employeeName'=>$employee->getPersonPerson()->getFullName(),
            'sqlNumber'=>$ehe->getIdEmployerHasEmployee()
        );
        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
		return new Response(200);
	}
}
