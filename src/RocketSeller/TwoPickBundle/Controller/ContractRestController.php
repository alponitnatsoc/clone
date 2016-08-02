<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\ContractMethodsTrait;

class ContractRestController extends FOSRestController
{
    use EmployerHasEmployeeMethodsTrait;
    use ContractMethodsTrait;

    /**
     * Obtener los contratos de un empleado relacionado con un empleador employerhasemployee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener los contratos de un empleado",
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
    public function getContractsByEheAction($id)
    {
        $data = $this->showContracts($id);

        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }

    /**
     * Obtener el detalle de un contrato.
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
     * @param integer $id - Id del contrato
     *
     * @return View
     */
    public function getContractDetailAction($id)
    {
        $data = $this->contractDetail($id);

        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }
}