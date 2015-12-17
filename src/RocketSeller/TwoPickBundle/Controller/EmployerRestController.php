<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Validator\ConstraintViolationList;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Liquidation;

use RocketSeller\TwoPickBundle\Traits\GetTransactionDetailTrait;

class EmployerRestController extends FOSRestController
{
    use GetTransactionDetailTrait;
    /**
     * Obtener el detalle de una transaccion
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener todos los datos de una orden de compra.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $id - Id de la transaccion
     * @param string $type - Tipo de transaccon (pago, contrato, liquidacion)
     *
     * @return View
     *
     */
    public function getTransactionDetailAction($type, $id)
    {
        $details = $this->transactionDetail($type, $id);

        $view = View::create();
        $view->setData($details)->setStatusCode(200);

        return $view;
    }

    /**
     * Obtener el listado de Pagos o Contratos de un usuario
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener todos los datos de una orden de compra.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param string $type - Tipo de información a listar (pagos, contratos, novedades)
     * @param integer $id - Id del usuario
     *
     *  @return View
     */
    public function getListByUserAction($type, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $user */
        $user = $userRepository->findOneBy(
            array(
                "id" => $id
            )
        );

        $data = array();
        switch ($type) {
            case "payments":
                if ($user) {
                    $data = $user->getPayments();
                }
                break;
            case "contracts":
                if ($user) {
                    $employerHasEmployee = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                    $contracts = array();
                    foreach($employerHasEmployee as $ehe) {
                        $contracts[] = $ehe->getContracts();
                    }
                    foreach($contracts as $contract) {
                        /** @var Contract $contract */
                        $data[] = $contract;
                    }
                }
                break;
            default:
                break;
        }

        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }

    /**
     * Get the validation errors
     *
     * @param ConstraintViolationList $errors Validator error list
     *
     * @return View
     */
    protected function getErrorsView(ConstraintViolationList $errors)
    {
        $msgs = array();
        $errorIterator = $errors->getIterator();
        foreach ($errorIterator as $validationError) {
            $msg = $validationError->getMessage();
            $params = $validationError->getMessageParameters();
            $msgs[$validationError->getPropertyPath()][] = $this->get('translator')->trans($msg, $params, 'validators');
        }
        $view = View::create($msgs);
        $view->setStatusCode(400);

        return $view;
    }

}