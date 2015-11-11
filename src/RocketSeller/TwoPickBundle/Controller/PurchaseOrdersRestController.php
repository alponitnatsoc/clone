<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Validator\ConstraintViolationList;

class PurchaseOrdersRestController extends FOSRestController
{

    /**
     * Metodo que se utiliza para actualizar el numero de la factura en una orden de compra.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Metodo que se utiliza para actualizar el numero de la factura en una orden de compra.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="idPO", nullable=false, strict=true, description="ID de la orden de compra a actualizar.")
     * @RequestParam(name="invoiceNumber", nullable=false, strict=true, description="numero de la factura que se va
     *                      a agregar a la orden de compra.")
     *
     * @return View
     */
    public function putUpdateInvoiceNumberAction(ParamFetcher $paramFetcher) {

        $idPO = $paramFetcher->get('idPO');
        $invoiceNumber = $paramFetcher->get('invoiceNumber');
        $em = $this->getDoctrine()->getManager();
        $purchaseOrdersRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");
        $dataPO = $purchaseOrdersRepository->findOneBy(
            array('idPurchaseOrders' => $idPO)
        );

        $em->persist($dataPO);
        $em->flush();
        $dataPO->setInvoiceNumber($invoiceNumber);

        $view = View::create();

        $errors = $this->get('validator')->validate($dataPO, array('Update'));

        if (count($errors) == 0) {
            $em->persist($dataPO);
            $em->flush();
            $view->setData($dataPO)->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
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