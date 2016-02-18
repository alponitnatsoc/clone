<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\LiquidationMethodsTrait;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Traits\NoveltyTypeMethodsTrait;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyType;
use Symfony\Component\Validator\Constraints\Date;
use RocketSeller\TwoPickBundle\Entity\Liquidation;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\Pay;

class LiquidationRestController extends FOSRestController
{
    use EmployerHasEmployeeMethodsTrait;
    use LiquidationMethodsTrait;
    use NoveltyTypeMethodsTrait;

    /**
     * Obtener las liquidaciones de un empleado relacionado con un empleador employerhasemployee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener las liquidaciones de un empleado",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $id - Id de la relacion EmployerHasEmployee
     *
     * @return View
     */
    public function getLiquidationsByEheAction($id)
    {
        $data = $this->showLiquidations($id);

        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }

    /**
     * Obtener el detalle de una liquidacion.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener detalle de una liquidacion.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $id - Id de la liquidacion
     *
     * @return View
     */
    public function getLiquidationDetailAction($id)
    {
        $data = $this->liquidationDetail($id);
        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }

    /**
     * Primer paso liquidacion final.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Paso 1 liquidacion final",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     * (name="username", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="Username o email")
     * (name="last_work_day", nullable=false, requirements="([0-9])+", strict=true, description="Ultimo dia de trabajo")
     * (name="last_work_month", nullable=false, requirements="([0-9])+", strict=true, description="")
     * (name="last_work_year", nullable=false, requirements="([0-9])+", strict=true, description="")
     * (name="liquidation_reason", nullable=false, requirements="([0-9])+", strict=true, description="")
     *
     * @return View
     */
    public function postFinalLiquidationStep1Action(Request $request)
    {
//         $em = $this->getDoctrine()->getManager();

        $data = array(
            "username" => $this->getUser()->getUsername(),
            "last_work_day" => $request->get("last_work_day", null),
            "last_work_month" => $request->get("last_work_month", null),
            "last_work_year" => $request->get("last_work_year", null),
            "liquidation_reason" => $request->get("liquidation_reason", null)
        );

        $parameters = $request->request->all();
        $day = $parameters["last_work_day"];
        $month = $parameters["last_work_month"];
        $year = $parameters["last_work_year"];

//         $dateStart = new \DateTime($year . "-" . $month . "-" . $day);
//         $payroll = $parameters["idPayroll"];

//         $noveltyType = $this->noveltyTypeByGroup("retiro");

//         $retiro = new Novelty();
//         $retiro->setDateStart($dateStart);
//         $retiro->setName("Retiro");
//         $retiro->setNoveltyTypeNoveltyType($noveltyType[0]);

//         $em->persist($retiro);
//         $em->flush();

        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }

    /**
     * Obtener datos preliquidacion.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener datos preliquidacion.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
     *    (name="username", nullable=false, requirements="(.*)", strict=true, description="Username of employer.")
     *    (name="year", nullable=false, requirements="([0-9])+", strict=true, description="Year of end of the contract(format: YYYY)")
     *    (name="month", nullable=false, requirements="([0-9])+", strict=true, description="Month of end of the contract(format: MM)")
     *    (name="day", nullable=false, requirements="([0-9])+", strict=true, description="Day of end of the contract(format: DD)")
     *    (name="frequency", nullable=false, requirements="([0-9])+", strict=true, description="Pago de nominca (diario, quincena, mensual)")
     *    (name="cutDate", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Date of end of the contract(format: DD-MM-YYYY).")
     *    (name="processDate", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Date of the end of contract(format: DD-MM-YYYY)")
     *    (name="retirementCause", nullable=false, requirements="([0-9])+", strict=true, description="ID of the retirement cause.")
     *
     * @return View
     */
    public function postPreLiquidationAction(Request $request)
    {
        $data = array();
        $view = View::create();

        $parameters = $request->request->all();

        $employee_id = $parameters["employee_id"] . "9"; //@todo el 9 es para los mocks
        $username = $parameters["username"];
        $year = $parameters["year"];
        $month = $parameters["month"];
        $day = $parameters["day"];
        $frequency = $parameters["frequency"];
        $cutDate = $parameters["cutDate"];
        $processDate = $parameters["processDate"];
        $retirementCause = $parameters["retirementCause"];

        /**
         * Dato que se envia a SQL dependiendo de como se le paga la nomina al empleado (quincenal o mensual)
         * period = 4 si la nomina se paga mensual o diaria, y cuando el empleado termina contrato despues del 15 del mes
         * period = 2 si la nomina se paga quincenal y termina contrato antes del 15 del mes
         * @var integer $period
         */
        $period = 4;
        /**
         * $frequency frecuencia de pago de la nomina (diario = 1, mensual = 2 o quincenal = 3) con base en la entidad frequency
         */
        if ($frequency == 3 && $day <= 15) {
            $period = 2;
        }

        $format = array('_format' => 'json');
        /**
         * Enviar a SQL los parametros para calcular la liquidacion
         */
        $req = new Request();
        $req->request->set("employee_id", $employee_id);
        $req->request->set("username", $username);
        $req->request->set("year", $year);
        $req->request->set("month", $month);
        $req->request->set("period", $period);
        $req->request->set("cutDate", $cutDate);
        $req->request->set("processDate", $processDate);
        $req->request->set("retirementCause", $retirementCause);

        $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postAddFinalLiquidationParameters", array("request" => $req), $format);
        if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
            $data = $response->getContent();
            $view->setData("1 - " . $employee_id . " - " . $response->getStatusCode());
            $view->setStatusCode(410);
            return $view;
        }

        /**
         * Solicitar que se procese la liquidacion, antes de ser consolidada, preliquidacion
         */
        $req = new Request();
        $req->request->set("employee_id", $employee_id);
        $req->request->set("execution_type", "P");

        $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postExecuteFinalLiquidation", array("request" => $req), $format);
        if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
            $data = $response->getContent();
            $view->setData("2 - " . $employee_id . " - " . $req->request->get("execution_type") . " -- " . $data);
            $view->setStatusCode(410);
            return $view;
        }
//         $data = $response->getContent();

        /**
         * Obtener datos de la preliquidacion antes de consolidarla
         */
        $response = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll', array(
                'employeeId' => $employee_id,
                'period' => $period
            ),
            $format
        );
        $data["detail"] = json_decode($response->getContent(), true);

        $data["totalLiq"] = $this->totalLiquidation($data["detail"]);

//         @todo enviar parametros a sql
//         $.ajax({
//             url: "/api/public/v1/adds/finals/liquidations/parameters",
//             type: 'POST',
//             data: {
//                 employee_id: "123123123",
//                 username: "nanana",
//                 year: 2016,
//                 month: 2,
//                 period: 2,
//                 cutDate: "08-02-2016",
//                 processDate: "08-02-2016",
//                 retirementCause: 6,
//                 //             last_work_day: form.find("select[name='rocketseller_twopickbundle_liquidation[lastWorkDay][day]']").val(),
//             }
//         }).done(function (data) {
//             alert(data);
//         }).fail(function (jqXHR, textStatus, errorThrown) {
//             alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
//         });
//         @todo preconsolidar liquidacion en sql
//             $.ajax({
//                 url: "/api/public/v1/executes/finals/liquidations",
//                 type: 'POST',
//                 data: {
//                     employee_id: "123123123",
//                     execution_type: "P"
//                 }
//             }).done(function (data) {
//                 alert(data);
//             }).fail(function (jqXHR, textStatus, errorThrown) {
//                 alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
//             });
//         @todo obtener informacion preliquidacion
//                 $.ajax({
//                     url: "/api/public/v1/generals/1231231239/payrolls/2",
//                     type: 'GET',
//                     data: {
//                     }
//                 }).done(function (data) {
//                     alert(data);
//                 }).fail(function (jqXHR, textStatus, errorThrown) {
//                     alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
//                 });

        $view->setData($data)->setStatusCode(200);
        return $view;
    }

    /**
     * submit preliquidacion.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener datos preliquidacion.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
     *    (name="username", nullable=false, requirements="(.*)", strict=true, description="Username of employer.")
     *    (name="year", nullable=false, requirements="([0-9])+", strict=true, description="Year of end of the contract(format: YYYY)")
     *    (name="month", nullable=false, requirements="([0-9])+", strict=true, description="Month of end of the contract(format: MM)")
     *    (name="day", nullable=false, requirements="([0-9])+", strict=true, description="Day of end of the contract(format: DD)")
     *    (name="frequency", nullable=false, requirements="([0-9])+", strict=true, description="Pago de nominca (diario, quincena, mensual)")
     *    (name="cutDate", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Date of end of the contract(format: DD-MM-YYYY).")
     *    (name="processDate", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Date of the end of contract(format: DD-MM-YYYY)")
     *    (name="retirementCause", nullable=false, requirements="([0-9])+", strict=true, description="ID of the retirement cause.")
     *
     * @return View
     */
    public function postFinalPreLiquidationSubmitAction(Request $request)
    {

        $data = array();
        $view = View::create();

        $parameters = $request->request->all();

        $idEmperHasEmpee = $parameters["employee_id"];
        $employee_id = $idEmperHasEmpee . "9"; //@todo el 9 es para los mocks
        $username = $parameters["username"];
        $year = $parameters["year"];
        $month = $parameters["month"];
        $day = $parameters["day"];
        $frequency = $parameters["frequency"];
        $cutDate = $parameters["cutDate"];
        $processDate = $parameters["processDate"];
        $retirementCause = $parameters["retirementCause"];
        $id_liq = $parameters["id_liq"];

        /**
         * Dato que se envia a SQL dependiendo de como se le paga la nomina al empleado (quincenal o mensual)
         * period = 4 si la nomina se paga mensual o diaria, y cuando el empleado termina contrato despues del 15 del mes
         * period = 2 si la nomina se paga quincenal y termina contrato antes del 15 del mes
         * @var integer $period
         */
        $period = 4;
        /**
         * $frequency frecuencia de pago de la nomina (diario = 1, mensual = 2 o quincenal = 3) con base en la entidad frequency
         */
        if ($frequency == 3 && $day <= 15) {
            $period = 2;
        }

        $format = array('_format' => 'json');
        /**
         * Enviar a SQL los parametros para calcular la liquidacion
         */
        $req = new Request();
        $req->request->set("employee_id", $employee_id);
        $req->request->set("username", $username);
        $req->request->set("year", $year);
        $req->request->set("month", $month);
        $req->request->set("period", $period);
        $req->request->set("cutDate", $cutDate);
        $req->request->set("processDate", $processDate);
        $req->request->set("retirementCause", $retirementCause);

        $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postAddFinalLiquidationParameters", array("request" => $req), $format);
        if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
            $data = $response->getContent();
            $view->setData("1 - " . $employee_id . " - " . $response->getStatusCode());
            $view->setStatusCode(410);
            return $view;
        }

        /**
         * Solicitar que se procese la liquidacion, antes de ser consolidada, preliquidacion
         */
        $req = new Request();
        $req->request->set("employee_id", $employee_id);
        $req->request->set("execution_type", "P");

        $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postExecuteFinalLiquidation", array("request" => $req), $format);
        if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
            $data = $response->getContent();
            $view->setData("2 - " . $employee_id . " - " . $req->request->get("execution_type") . " -- " . $data);
            $view->setStatusCode(410);
            return $view;
        }

        $em = $this->getDoctrine()->getManager();
        $date = $year . "-" . $month . "-" . $day;
        $lastWorkDay = new \DateTime($date);
        /**
         * Actualizar datos de liquidacion en DB
         * @var Liquidation $liquidation
         */
        $liquidation = $this->liquidationDetail($id_liq);
        $liquidation->setLastWorkDay($lastWorkDay);
        $em->persist($liquidation);
        $em->flush();

        $data = array(
            "employee_id" => $employee_id,
            "period" => $period,
            "url" => $this->generateUrl("final_liquidation_detail", array(
                "employee_id" => $employee_id,
                "period" => $period,
                "id" => $idEmperHasEmpee,
                "id_liq" => $id_liq
            ))
        );

        $view->setData($data)->setStatusCode(200);
        return $view;
    }

    /**
     * submit final liquidacion.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "submit final liquidacion.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
     *    (name="period", nullable=false, requirements="([0-9])+", strict=true, description="Period.")
     *
     * @return View
     */
    public function postFinalLiquidationSubmitAction(Request $request)
    {

        $data = array();
        $view = View::create();

        $parameters = $request->request->all();

        $employee_id = $parameters["employee_id"] . "9"; //@todo el 9 es para los mocks
        $period = $parameters["period"];

        $format = array('_format' => 'json');

        /**
         * Solicitar que se procese la liquidacion, antes de ser consolidada, preliquidacion
         */
        $req = new Request();
        $req->request->set("employee_id", $employee_id);
        $req->request->set("execution_type", "C");

        $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postExecuteFinalLiquidation", array("request" => $req), $format);
        if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
            $data = $response->getContent();
            $view->setData("2 - " . $employee_id . " - " . $req->request->get("execution_type") . " -- " . $data);
            $view->setStatusCode(410);
            return $view;
        }

        $data = array(
            "data" => $response->getContent()
        );

        $view->setData($data)->setStatusCode(200);
        return $view;
    }

    /**
     * Generar PDF liquidacion.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Generar PDF liquidacion.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *    (name="employer_name", nullable=false, strict=true, description="Employer name")
     *    (name="employee_name", nullable=false, strict=true, description="Employee name")
     *    (name="contract_id", nullable=false, requirements="([0-9])+", strict=true, description="contract id")
     *    (name="url", nullable=false, strict=true, description="url")
     *
     * @return Response
     */
    public function postGeneratePdfAction(Request $request)
    {
        $parameters = $request->request->all();

        $employerName = $parameters["employer_name"];
        $employeeName = $parameters["employee_name"];
        $idContract = $parameters["contract_id"];
        $url = $parameters["url"];

//         $filename = $employerName . "-" . $employeeName . "-" . $idContract;
//         $path = $this->get('kernel')->getRootDir() . "/../web/public/docs/generados/liq-def-" . $filename;
//         if (!file_exists($path)) {
//             $pdf = $this->get('knp_snappy.pdf')->generate($url, $path);
//         }

        return new Response(
            $this->get('knp_snappy.pdf')->getOutput($url), 200, array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="file.pdf"'
            )
        );

//         $view = View::create();
//         $view->setData($pdf)->setStatusCode(200);
//         return $view;

//         $this->get('knp_snappy.pdf')->generate('http://www.google.fr', '/path/to/the/file.pdf');
//         $pdf = new Response(
//             $this->get('knp_snappy.pdf')->generateFromHtml(
//                 $html,
//                 $this->get('kernel')->getRootDir() . "/../web/public/docs/generados/liq-def-" . $filename
//                 )
//             //             200,
//             //             array(
//             //                 'Content-Type'          => 'application/pdf',
//             //                 'Content-Disposition'   => 'attachment; filename="certificadoLaboral.pdf"'
//             //             )
//             );
    }

    /**
     * Pagar liquidacion<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Pagar liquidacion.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *    (name="payment_method_liq", nullable=false, strict=true, description="paymentMethodLiq")
     *    (name="id_liq", description="id liquidation")
     *
     * @return Response
     */
    public function postPayLiquidationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $format = array('_format' => 'json');

        $params = $request->request->all();
        $id_liq = $params["id_liq"];

        /** @var Liquidation $liquidation */
        $liquidation = $this->liquidationDetail($id_liq);
        $employerPerson = $liquidation->getEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
        $employerDocument = $employerPerson->getDocument();

        $employee = $liquidation->getEmployerHasEmployee()->getEmployeeEmployee();
        $employeePerson = $employee->getPersonPerson();
        $employeeDocument = $employeePerson->getDocument();

        $contract = $liquidation->getContract();
        $employeePayMethod = $contract->getPayMethodPayMethod();
        $employeePayType = $employeePayMethod->getPayTypePayType();

        $total = $liquidation->getCost();

        $purchaseOrdersStatusRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
        /* @var $purchaseOrdersStatus PurchaseOrdersStatus */
        $purchaseOrdersStatus = $purchaseOrdersStatusRepo->findOneBy(array('name' => 'Pendiente'));

        $purchaseOrder = new PurchaseOrders();
        $purchaseOrder->setIdUser($this->getUser());
        $purchaseOrder->setName('Liquidacion definitiva');
        $purchaseOrder->setValue((floatval($total)));
        $purchaseOrder->setPurchaseOrdersStatus($purchaseOrdersStatus);
        $dateCreated = date("Y-m-d H:i:s");
        $purchaseOrder->setDateCreated(new \DateTime($dateCreated));
        $dateModified = date("Y-m-d H:i:s");
        $purchaseOrder->setDateModified(new \DateTime($dateModified));

        $em->persist($purchaseOrder);

        $purchaseOrderDescription = new PurchaseOrdersDescription();
        $purchaseOrderDescription->setDescription("Pago liquidacion definitiva");
        $purchaseOrderDescription->setPurchaseOrders($purchaseOrder);
        $purchaseOrderDescription->setPurchaseOrdersStatus($purchaseOrdersStatus);
        $purchaseOrderDescription->setValue($total);

        $em->persist($purchaseOrderDescription);
        $em->flush();

        $req = new Request();
        $req->setMethod("POST");
        $req->request->set("documentNumber", $employerDocument);
        $req->request->set("MethodId", $params["payment_method_liq"]);
        $req->request->set("totalAmount", $total);
        $req->request->set("chargeMode", 2);
        $req->request->set("chargeId", $purchaseOrder->getIdPurchaseOrders());
        $req->request->set("taxAmount", 0);
        $req->request->set("taxBase", 0);
        $req->request->set("commissionAmount", 0);
        $req->request->set("commissionBase", 0);

        $responsePA = $this->forward("RocketSellerTwoPickBundle:PaymentsRest:postPaymentAproval", array("request" => $req), $format);

        $dataResPA = json_decode($responsePA->getContent(), true);
        $dataResPA["charge-id"];

        $desc = $purchaseOrderDescription->getDescription();
        $purchaseOrderDescription->setDescription($desc . " - " . $dataResPA["charge-id"]);
        $em->persist($purchaseOrderDescription);

        $req = new Request();
        $req->setMethod("POST");
        $req->request->set("documentNumber", $employerDocument);
        $req->request->set("beneficiaryId", $employeeDocument);
        $req->request->set("beneficiaryAmount", $total);
        $req->request->set("dispersionType", 1);
        $req->request->set("chargeId", $purchaseOrder->getIdPurchaseOrders());

        $responseCP = $this->forward("RocketSellerTwoPickBundle:PaymentsRest:postClientPayment", array("request" => $req), $format);

        $dataResCP = json_decode($responseCP->getContent(), true);
        $idDispersionNovo = $dataResCP["transfer-id"];

        $pay = new Pay();
//         $pay->setPayMethodPayMethod($employeePayMethod);
//         $pay->setPayTypePayType($employeePayType);
        $pay->setPurchaseOrdersDescription($purchaseOrderDescription);
        $pay->setUserIdUser($this->getUser());
        $pay->setIdDispercionNovo($idDispersionNovo);
        $em->persist($pay);
        $em->flush();

//         postPaymentAprovalAction
//         postClientPaymentAction

//         getClientSpecificChargeAction

        $view = View::create();

        $data = array(
            "params" => array(
                "RES-C-P" => $dataResCP,
                "RES-P-A" => $dataResPA
            ),
            "url" => $this->generateUrl("pay_liquidation", array(
                "id" => $id_liq
            ))
        );

        $view->setData($data)->setStatusCode(200);
        return $view;
    }
}