<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\LiquidationMethodsTrait;

class LiquidationRestController extends FOSRestController
{
    use EmployerHasEmployeeMethodsTrait;
    use LiquidationMethodsTrait;

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

}