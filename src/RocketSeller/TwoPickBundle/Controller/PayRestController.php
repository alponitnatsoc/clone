<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\PayMethodsTrait;
use RocketSeller\TwoPickBundle\Controller\ProcedureController;

class PayRestController extends FOSRestController
{

    use EmployerHasEmployeeMethodsTrait;

use PayMethodsTrait;

    /**
     * Obtener pagos de un empleado relacionado con un empleador employerhasemployee.<br/>
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
    public function getPaymentsByEheAction($id)
    {
        $data = $this->showPayments($id);

        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }

    /**
     * Insert a new client into the payments system(3.1 in Novopayment).<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Inserts a new client into the payments system.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="documentType", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="documentType.")
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     * (name="name", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="first name.")
     * (name="lastName", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="last name.")
     * (name="year", nullable=false, requirements="([0-9]){4}", strict=true, description="year of birth.")
     * (name="month", nullable=false, requirements="([0-9]){2}", strict=true, description="month of birth.")
     * (name="day", nullable=false, requirements="([0-9]){2}", strict=true, description="day of birth.")
     * (name="phone", nullable=false, requirements="([0-9])+", strict=true, description="phone.")
     * (name="email", nullable=false, strict=true, description="email.")
     *
     * @return View
     */
    public function postPayMembresiaAction(Request $request)
    {

        $user = $this->getUser();
        $employeesData = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        $employer = $user->getPersonPerson()->getEmployer();
        $salaries = array();
        $payrolls = array();
        $novelties = array();
        $aportes = array();
        foreach ($employeesData as $employerHasEmployee) {
            if ($employerHasEmployee->getState() == 1) {
                $contracts = $employerHasEmployee->getContracts();
                foreach ($contracts as $contract) {
                    if ($contract->getState() == 1) {
                        $activePayroll = $contract->getActivePayroll();
                        $payrolls = $contract->getPayrolls();
                    }
                }
            }
        }
        $procedureType = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:ProcedureType')
                ->findByName("Registro empleador y empleados");
        $procedure = $this->forward('RocketSellerTwoPickBundle:Procedure:procedure', array(
            'employerId' => $employer->getIdEmployer(),
            'idProcedureType' => $procedureType[0]->getIdProcedureType()
        ));

        return $this->redirectToRoute('show_dashboard_employer');

        $parameters = $request->request->all();
        //$documentType = $parameters['documentType'];

        $view = View::create();
        $view->setData(true);
        $view->setStatusCode(200);



        return $view;
    }

    /**
     * Obtener el detalle de un pago.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener detalle de un contrato.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $id - Id del pago
     *
     * @return View
     */
    public function getPayMembresiaAction($id)
    {
        $data = $this->payDetail($id);

        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }

}
