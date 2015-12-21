<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\PayMethodsTrait;

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
    public function getPayDetailAction($id)
    {
        $data = $this->payDetail($id);

        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }
}