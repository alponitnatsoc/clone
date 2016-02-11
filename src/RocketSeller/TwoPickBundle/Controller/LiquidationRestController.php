<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\LiquidationMethodsTrait;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Traits\NoveltyTypeMethodsTrait;

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

        $data = array(
            "username" => $this->getUser()->getUsername(),
            "last_work_day" => $request->get("last_work_day", null),
            "last_work_month" => $request->get("last_work_month", null),
            "last_work_year" => $request->get("last_work_year", null),
            "liquidation_reason" => $request->get("liquidation_reason", null)
        );
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
        $data = json_decode($response->getContent(), true);

        $data = $this->totalLiquidation($data);

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

    private function totalLiquidation($data)
    {
        $total = 0;
        foreach ($data as $key => $info) {
            $payroll_code = $info["CON_CODIGO"];
            /** @var \RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyType */
            $noveltyType = $this->noveltyTypeByPayrollCode($payroll_code);
            if ($noveltyType) {
//                 var_dump($info["NOMI_VALOR"] . " - " . $noveltyType->getNaturaleza());
                switch ($noveltyType->getNaturaleza()):
                    case "DED":
                        $total -= $info["NOMI_VALOR"];
                        break;
                    case "DEV":
                        $total += $info["NOMI_VALOR"];
                        break;
                    default:
                        break;
                endswitch;
            }
        }
        return $total;
    }
}