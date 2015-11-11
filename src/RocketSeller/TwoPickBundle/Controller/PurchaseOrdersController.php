<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersRepository;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use RocketSeller\TwoPickBundle\Form\PurchaseOrders as FormPurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\Payroll;

class PurchaseOrdersController extends Controller
{
    public function indexAction()
    {
        if (is_object($this->getUser()) && $this->getUser() instanceof UserInterface ) {

            $ordersByUser = $this->getUser()->getPurchaseOrders();

            $orders = array();
            foreach ($ordersByUser as $key => $order) {
                $orders[$key]['id'] = $order->getIdPurchaseOrders();
                $orders[$key]['type'] = $order->getPurchaseOrdersTypePurchaseOrdersType()->getName();
                $dateCreated = $order->getDateCreated()->format('d/m/Y');
                $orders[$key]['dateCreated'] = $dateCreated;
                $lastModified = $order->getDateModified()->format('d/m/Y');
                $orders[$key]['lastModified'] = $lastModified;
            }

            return $this->render('RocketSellerTwoPickBundle:General:purchase-orders.html.twig', array(
                'orders' => $orders,
                'updateInvoiceNumberService' => $this->generateUrl("api_public_put_update_invoice_number")
            ));
        } else {
            throw new AccessDeniedException('Debe estar logueado para ingresar a esta sección');
        }
    }

    /**
     * Obtener todos los datos de una orden de compra
     * @param int $id - Id de la orden de compra para obtener su correspondiente detalle
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function detailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $purchaseOrdersDescriptionRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
        $purchaseOrdersRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");

        $dataDescription = $purchaseOrdersDescriptionRepository->findByPurchaseOrdersPurchaseOrders($id);
        $dataPO = $purchaseOrdersRepository->findByIdPurchaseOrders($id);

        $data = array();
        foreach ($dataPO as $key => $po) {
            $data[$key]['type'] = $po->getPurchaseOrdersTypePurchaseOrdersType()->getName();
            $dateCreated = $po->getDateCreated()->format('d/m/Y');
            $data[$key]['dateCreated'] = $dateCreated;
            $lastModified = $po->getDateModified()->format('d/m/Y');
            $data[$key]['lastModified'] = $lastModified;
            $data[$key]['invoiceNumber'] = $po->getInvoiceNumber();
            $data[$key]['id'] = $po->getIdPurchaseOrders();
            $data[$key]['name'] = $po->getName();
            $data[$key]['user'] = $po->getIdUser()->getId();
            $payroll = $po->getPayrollPayroll();
            if ($payroll) {
                $data[$key]['idPayroll'] = $payroll->getIdPayroll();
            } else {
                $data[$key]['idPayroll'] = null;
            }
            $descriptions = $po->getPurchaseOrderDescriptions();
            if ($descriptions && count($descriptions) > 0) {
                foreach ($descriptions as $k => $description) {
                    $data[$key]['descriptions']['ids'][$k] = $description->getIdPurchaseOrdersDescription();
                }
            } else {
                $data[$key]['descriptions'] = null;
            }
            $status = $po->getPurchaseOrdersStatusPurchaseOrdersStatus();
            $data[$key]['idStatus'] = $status->getIdPurchaseOrdersStatus();
        }
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
        return new JsonResponse($details);
    }

    public function createAction(Request $request)
    {
        $form = $this->createForm(new FormPurchaseOrders());

//         var_dump($form->isSubmitted());
        $form->handleRequest($request);
//         var_dump($form->isSubmitted());
        if ($form->isSubmitted() && $form->isValid()) {
//             $em = $this->getDoctrine()->getManager();
//             $em->persist($form);
//             $em->flush();
            $purchaseOrder = new PurchaseOrders();
            $purchaseOrdersTypePurchaseOrdersType = $form->get('purchaseOrdersType');
            $payrollPayroll = $form->get('payroll');
            $pos = $form->get('purchaseOrdersStatus');

            $purchaseOrder->setPayrollPayroll($payrollPayroll);
            $purchaseOrder->setPurchaseOrdersStatusPurchaseOrdersStatus($pos);
            $purchaseOrder->setPurchaseOrdersTypePurchaseOrdersType($purchaseOrdersTypePurchaseOrdersType);
        }


        return $this->render('RocketSellerTwoPickBundle:General:purchase-orders-create.html.twig', array(
            'form' => $form->createView()
        ));
    }
}