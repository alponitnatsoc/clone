<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Validator\ConstraintViolationList;
use RocketSeller\TwoPickBundle\Entity\Contract;

class EmployerRestController extends FOSRestController
{
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
        $em = $this->getDoctrine()->getManager();

        $details = array();

        switch ($type) {
            case "contrato":

                $contractRepository = $em->getRepository("RocketSellerTwoPickBundle:Contract");
                /** @var Contract $contract */
                $contract = $contractRepository->findOneBy(
                    array(
                        "idContract" => $id
                    )
                );
                $details["benefics"] = $contract->getBenefits();
                $details["contractType"] = $contract->getContractTypeContractType();
                $details["document"] = $contract->getDocumentDocument();
                $details["employeeContractType"] = $contract->getEmployeeContractTypeEmployeeContractType();
                $details["payMethod"] = $contract->getPayMethodPayMethod();
                $details["payrolls"] = $contract->getPayrolls();
                $details["position"] = $contract->getPositionPosition();
                $details["salary"] = $contract->getSalary();
                $details["state"] = $contract->getState();
                $details["timeCommitment"] = $contract->getTimeCommitmentTimeCommitment();
                $details["workplaces"] = $contract->getWorkplaces();
            break;

            default:
                ;
            break;
        }

        $view = View::create();
        $view->setData($details)->setStatusCode(200);

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