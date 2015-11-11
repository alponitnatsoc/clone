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
     * Obtener todos los datos de una orden de compra.<br/>
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
     * @param integer $id - Id de la orden de compra para obtener su correspondiente detalle
     *
     * @return View
     */
    public function getDetailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $purchaseOrdersDescriptionRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
        $purchaseOrdersRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");

        $dataDescription = $purchaseOrdersDescriptionRepository->findByPurchaseOrdersPurchaseOrders($id);
        $po = $purchaseOrdersRepository->findOneBy(
            array('idPurchaseOrders' => $id)
        );

        $data = array();
        $data['type'] = $po->getPurchaseOrdersTypePurchaseOrdersType()->getName();
        $dateCreated = $po->getDateCreated()->format('d/m/Y');
        $data['dateCreated'] = $dateCreated;
        $lastModified = $po->getDateModified()->format('d/m/Y');
        $data['lastModified'] = $lastModified;
        $data['invoiceNumber'] = $po->getInvoiceNumber();
        $data['id'] = $po->getIdPurchaseOrders();
        $data['name'] = $po->getName();
        $data['user'] = $po->getIdUser()->getId();
        $payroll = $po->getPayrollPayroll();
        if ($payroll) {
            $data['idPayroll'] = $payroll->getIdPayroll();
        } else {
            $data['idPayroll'] = null;
        }
        $descriptions = $po->getPurchaseOrderDescriptions();
        if ($descriptions && count($descriptions) > 0) {
            foreach ($descriptions as $k => $description) {
                $data['descriptions']['ids'][$k] = $description->getIdPurchaseOrdersDescription();
            }
        } else {
            $data['descriptions'] = null;
        }
        $status = $po->getPurchaseOrdersStatusPurchaseOrdersStatus();
        $data['idStatus'] = $status->getIdPurchaseOrdersStatus();

        $detail = array();
        foreach($dataDescription as $key => $pod) {
            $detail[$key]['idDescription'] = $pod->getIdPurchaseOrdersDescription();
            $detail[$key]['taxName'] = $pod->getTaxTax()->getName();
            $detail[$key]['description'] = $pod->getDescription();
            $prod = $pod->getProductProduct();
            $detail[$key]['product'] = $prod->getName();
        }

        $details = array(
            'purchaseOrderData' => $data,
            'details' => $detail
        );

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