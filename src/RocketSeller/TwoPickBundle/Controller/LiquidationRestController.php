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
use Symfony\Component\Validator\Constraints\Date;
use RocketSeller\TwoPickBundle\Entity\Liquidation;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Traits\LiquidationReasonMethodsTrait;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\LiquidationReason;
use RocketSeller\TwoPickBundle\Traits\NotificationMethodsTrait;

class LiquidationRestController extends FOSRestController
{
    use EmployerHasEmployeeMethodsTrait;
    use LiquidationMethodsTrait;
    use NoveltyTypeMethodsTrait;
    use LiquidationReasonMethodsTrait;
    use NotificationMethodsTrait;

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
     * (name="id_liq")
     *
     * @return View
     */
    public function postFinalLiquidationStep1Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = array(
            "username" => $this->getUser()->getUsername(),
            "last_work_day" => $request->get("last_work_day", null),
            "last_work_month" => $request->get("last_work_month", null),
            "last_work_year" => $request->get("last_work_year", null),
            "liquidation_reason" => $request->get("liquidation_reason", null),
            "id_liq" => $request->get("id_liq", null)
        );

        $parameters = $request->request->all();
        $day = $parameters["last_work_day"];
        $month = $parameters["last_work_month"];
        $year = $parameters["last_work_year"];
        $liquidation_reason = $parameters["liquidation_reason"];
        $id_liq = $parameters["id_liq"];

        /** @var Liquidation $liquidation */
        $liquidation = $this->liquidationDetail($id_liq);
        $date = $year . "-" . $month . "-" . $day;
        $lastWorkDay = new \DateTime($date);
        $liquidation->setLastWorkDay($lastWorkDay);

        /** @var LiquidationReason $liquidation_reason */
        $liquidation_reason = $this->liquidationReasonByPayrollCode($liquidation_reason);

        $liquidation->setLiquidationReason($liquidation_reason);
        $employerHasEmployee = $liquidation->getEmployerHasEmployee();
        $idEmperHasEmpee = $employerHasEmployee->getIdEmployerHasEmployee();

        $em->persist($liquidation);
        $em->flush();

        $employeeName = $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames();
        $employerPerson = $employerHasEmployee->getEmployerEmployer()->getPersonPerson();

        $liquidationReason = $liquidation_reason->getPayrollCode();
        $notification = $this->notificationByPersonLiquidation($id_liq, $employerPerson->getIdPerson());

        if ( ($liquidationReason == 7 || $liquidationReason == 10) && !$notification) {
            $notification = new Notification();
            $notification->setAccion("Subir carta de renuncia");
            $notification->setDescription("Subir carta de renuncia firmada por " . $employeeName);
            $notification->setPersonPerson($employerPerson);
            $notification->activate();
            $notification->setType("alert");
            $notification->setTitle("Subir carta de renuncia");
            $notification->setLiquidation($liquidation);
            $em->persist($notification);
            $em->flush();

            $repoDocType = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:DocumentType");
            /** @var DocumentType $docType */
            $docType = $repoDocType->findOneBy(array(
                "name" => "Carta de renuncia"
            ));
	        //TODO(a-santamaria): crear bien related link falta entityType, entityId y docCode
//
//            $relatedLink = $this->generateUrl("documentos_employee", array(
//                    "idNotification" => $notification->getId(),
//                    "id" => $employerPerson->getIdPerson(),
//                    "idDocumentType" => $docType->getIdDocumentType()
//                )
//            );
//
//            $notification->setRelatedLink($relatedLink);
            $em->persist($notification);
            $em->flush();
        }


        $data = array(
            "url" => $this->generateUrl("final_liquidation_steps", array(
                "id" => $idEmperHasEmpee,
                "id_liq" => $id_liq,
                "step" => 2
            ))
        );

        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }
	
	/**
	 * Obtener datos previewpreliquidacion.<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Obtener datos previewpreliquidacion.",
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
	public function postPreviewPreLiquidationAction(Request $request)
	{
		$data = array();
		$view = View::create();
		
		$parameters = $request->request->all();
		
		$employee_id = $parameters["employee_id"];
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
		 * Verificar si ya se han enviado parametros para la liquidacion final
		 */
		$response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:getFinalLiquidationParameters", array("employeeId" => $employee_id), $format);
		if($response->getStatusCode() != 200 && $response->getStatusCode() != 201 && $response->getStatusCode() != 404){
			$data = $response->getContent();
			$view->setData("0 - " . $employee_id . " - " . $response->getStatusCode());
			$view->setStatusCode(410);
			return $view;
		}
		
		/**
		 * Enviar a SQL los parametros para calcular la liquidacion
		 */
		$req = new Request();
		$req->request->set("employee_id", $employee_id);
		$req->request->set("year", $year);
		$req->request->set("month", $month);
		$req->request->set("period", $period);
		$req->request->set("cutDate", $cutDate);
		$req->request->set("processDate", $processDate);
		$req->request->set("retirementCause", $retirementCause);
		
		if ($response->getStatusCode() == 404) {
			$response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postAddFinalLiquidationParameters", array("request" => $req), $format);
			if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
				$data = $response->getContent();
				$view->setData("1.a - " . $employee_id . " - " . $response->getStatusCode());
				$view->setStatusCode(410);
				return $view;
			}
		} else {
			/**
			 * Actualizar los parametros para calcular la liquidacion
			 */
			$response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postModifyFinalLiquidationParameters", array("request" => $req), $format);
			if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
				$data = $response->getContent();
				$view->setData("1.m - " . $employee_id . " - " . $response->getStatusCode());
				$view->setStatusCode(410);
				return $view;
			}
		}
		
		/**
		 * Obtener datos de la preliquidacion antes de consolidarla
		 */
		$response = $this->forward('RocketSellerTwoPickBundle:PayrollMethodRest:getGeneralPayrolls', array(
		  'employeeId' => $employee_id,
		  'period' => $period,
		  'mockFinalLiquidation' => true
		),
		  $format
		);
		$data["detail"] = json_decode($response->getContent(), true);
		
		$data["totalLiq"] = $this->totalLiquidation($data["detail"]);
		
		/**
		 * Unprocess liquidacion definitiva coz its only a preview
		 * first unprocess process 3 (liquidacion definitiva)
		 */
		$unprocesReq = new Request();
		$unprocesReq->request->add(array(
		  'cod_process' => '3',
		  'employee_id' => $employee_id,
		  'execution_type' => 'D'
		));
		$result = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postProcessExecution',
		                            array("request" => $unprocesReq), $format);
		
		/**
		 * Unprocess liquidacion definitiva coz its only a preview
		 * second unprocess service 620 (parameters liquidation)
		 */
		if($result->getStatusCode() == 200) {
			$response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postUnprocessFinalLiquidationParameters",
			                            array("request" => $req), $format);
			if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
				$data = $response->getContent();
				$view->setData("1.m - " . $employee_id . " - " . $response->getStatusCode());
				$view->setStatusCode(410);
				return $view;
			}
		}
		
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

        $employee_id = $parameters["employee_id"]; //@todo el 9 es para los mocks
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
         * Verificar si ya se han enviado parametros para la liquidacion final
         */
        $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:getFinalLiquidationParameters", array("employeeId" => $employee_id), $format);
        //         var_dump("getFinalLiquidationParameters " . $response->getContent());
        if($response->getStatusCode() != 200 && $response->getStatusCode() != 201 && $response->getStatusCode() != 404){
            $data = $response->getContent();
            $view->setData("0 - " . $employee_id . " - " . $response->getStatusCode());
            $view->setStatusCode(410);
            return $view;
        }

        /**
         * Enviar a SQL los parametros para calcular la liquidacion
         */
        $req = new Request();
        $req->request->set("employee_id", $employee_id);
        $req->request->set("year", $year);
        $req->request->set("month", $month);
        $req->request->set("period", $period);
        $req->request->set("cutDate", $cutDate);
        $req->request->set("processDate", $processDate);
        $req->request->set("retirementCause", $retirementCause);

        if ($response->getStatusCode() == 404) {
            $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postAddFinalLiquidationParameters", array("request" => $req), $format);
            if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
                $data = $response->getContent();
                $view->setData("1.a - " . $employee_id . " - " . $response->getStatusCode());
                $view->setStatusCode(410);
                return $view;
            }
        } else {
            /**
             * Actualizar los parametros para calcular la liquidacion
             */
            $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postModifyFinalLiquidationParameters", array("request" => $req), $format);
            if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
                $data = $response->getContent();
                $view->setData("1.m - " . $employee_id . " - " . $response->getStatusCode());
                $view->setStatusCode(410);
                return $view;
            }
        }

        /**
         * Solicitar que se procese la liquidacion, antes de ser consolidada, preliquidacion
         */
//         $req = new Request();
//         $req->request->set("employee_id", $employee_id);
//         $req->request->set("execution_type", "P");

//         $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postExecuteFinalLiquidation", array("request" => $req), $format);
//         if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
//             $data = $response->getContent();
//             $view->setData("2 - " . $employee_id . " - " . $req->request->get("execution_type") . " -- " . $data);
//             $view->setStatusCode(410);
//             return $view;
//         }

        /**
         * Obtener datos de la preliquidacion antes de consolidarla
         */
        $response = $this->forward('RocketSellerTwoPickBundle:PayrollMethodRest:getGeneralPayrolls', array(
                'employeeId' => $employee_id,
                'period' => $period,
                'mockFinalLiquidation' => true
            ),
            $format
        );
        $data["detail"] = json_decode($response->getContent(), true);

        $data["totalLiq"] = $this->totalLiquidation($data["detail"]);

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
        $employee_id = $idEmperHasEmpee; //@todo el 9 es para los mocks
        $username = $parameters["username"];
        $frequency = $parameters["frequency"];
        $retirementCause = $parameters["retirementCause"];
        $id_liq = $parameters["id_liq"];

        /**
         * @var Liquidation $liquidation
         */
        $liquidation = $this->liquidationDetail($id_liq);
        $lastDayWork = $liquidation->getLastWorkDay();
        $day = $lastDayWork->format("d");
        $month = $lastDayWork->format("m");
        $year = $lastDayWork->format("Y");
        $cutDate = $processDate = $lastDayWork->format("d-m-Y");

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
         * Verificar si ya se han enviado parametros para la liquidacion final
         */
        $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:getFinalLiquidationParameters", array("employeeId" => $employee_id), $format);
//         var_dump("getFinalLiquidationParameters " . $response->getContent());
        if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
            $data = $response->getContent();
            $view->setData("0 - " . $employee_id . " - " . $response->getStatusCode());
            $view->setStatusCode(410);
            return $view;
        }

        $req = new Request();
        $req->request->set("employee_id", $employee_id);
        $req->request->set("year", $year);
        $req->request->set("month", $month);
        $req->request->set("period", $period);
        $req->request->set("cutDate", $cutDate);
        $req->request->set("processDate", $processDate);
        $req->request->set("retirementCause", $retirementCause);

        if ($response->getContent() == null) {
            /**
             * Enviar a SQL los parametros para calcular la liquidacion
             */
            $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postAddFinalLiquidationParameters", array("request" => $req), $format);
            if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
                $data = $response->getContent();
                $view->setData("1.a - " . $employee_id . " - " . $response->getStatusCode());
                $view->setStatusCode(410);
                return $view;
            }
        } else {
            /**
             * Actualizar los parametros para calcular la liquidacion
             */
            $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postModifyFinalLiquidationParameters", array("request" => $req), $format);
            if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
                $data = $response->getContent();
                $view->setData("1.m - " . $employee_id . " - " . $response->getStatusCode());
                $view->setStatusCode(410);
                return $view;
            }
        }

        /**
         * Solicitar que se procese la liquidacion, antes de ser consolidada, preliquidacion
         */
//         $req = new Request();
//         $req->request->set("employee_id", $employee_id);
//         $req->request->set("execution_type", "P");

//         $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postExecuteFinalLiquidation", array("request" => $req), $format);
//         if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
//             $data = $response->getContent();
//             $view->setData("2 - " . $employee_id . " - " . $req->request->get("execution_type") . " -- " . $data);
//             $view->setStatusCode(410);
//             return $view;
//         }

        /**
         * Obtener datos de la preliquidacion antes de consolidarla
         */
        $response = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll', array(
                'employeeId' => $employee_id,
                'period' => $period
            ),
            $format
        );
//         Desprocesar

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

        $employee_id = $parameters["employee_id"]; //@todo el 9 es para los mocks
        $period = $parameters["period"];

        $format = array('_format' => 'json');

        /**
         * Solicitar que se procese la liquidacion y se consolida
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

//         /**
//          * Consultar la liquidacion
//          */
//         $response = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll', array(
//                 'employeeId' => $employee_id,
//                 'period' => $period
//             ),
//             $format
//         );

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
    public function postDownloadPdfAction(Request $request)
    {
        $parameters = $request->request->all();

        $data = array(
            "employeeInfo" => json_decode($parameters["employeeInfo"], true),
            "contractInfo" => json_decode($parameters["contractInfo"], true),
            "deducciones" => json_decode($parameters["deducciones"], true),
            "totalDeducciones" => $parameters["totalDeducciones"],
            "devengos" => json_decode($parameters["devengos"], true),
            "totalDevengos" => $parameters["totalDevengos"],
            "totalLiq" => $parameters["totalLiq"],
            "employer" => json_decode($parameters["employer"], true)
        );

        $html = $this->renderView('RocketSellerTwoPickBundle:Liquidation:liquidation-pdf.html.twig',
            $data
        );

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="liquidacion.pdf"'
            )
        );
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

        $contract = $liquidation->getContract();
        $payroll = $contract->getActivePayroll();

        $total = $liquidation->getCost();

        $purchaseOrdersStatusRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
        /** @var $purchaseOrdersStatus PurchaseOrdersStatus */
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

        $purchaseOrderDescription = new PurchaseOrdersDescription();
        $purchaseOrderDescription->setDescription("Pago liquidacion definitiva");
        $purchaseOrderDescription->setPurchaseOrders($purchaseOrder);
        $purchaseOrderDescription->setPurchaseOrdersStatus($purchaseOrdersStatus);
        $purchaseOrderDescription->setValue($total);

        $purchaseOrder->addPurchaseOrderDescription($purchaseOrderDescription);
        $purchaseOrder->setPayMethodId($params["payment_method_liq"]);
        $purchaseOrderDescription->setPayrollPayroll($payroll);

        $productRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Product');
        /** Product $product */
        $product = $productRepo->findOneBy(array('simpleName' => 'PN'));
        $purchaseOrderDescription->setProductProduct($product);

        $liquidation->setIdPurchaseOrder($purchaseOrder);

        $em->persist($purchaseOrderDescription);
        $em->persist($purchaseOrder);
        $em->persist($liquidation);

        $em->flush();

        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getPayPurchaseOrder', array("idPurchaseOrder" => $purchaseOrder->getIdPurchaseOrders()), $format);

        $view = View::create();
        if($insertionAnswer->getStatusCode()!=200){
            $view->setStatusCode(500)->setData(array('error'=>array("msnj"=>"No se pudo realizar el cobro, intenta nuevamente")));
            return $view;
        }

        $data = array(
            "url" => $this->generateUrl("pay_liquidation", array(
                "id" => $id_liq
            ))
        );

        $view->setData($data)->setStatusCode(200);
        return $view;
    }
}
