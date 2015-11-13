<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

            return $this->render('RocketSellerTwoPickBundle:PurchaseOrders:purchase-orders.html.twig', array(
                'orders' => $orders,
                'updateInvoiceNumberService' => $this->generateUrl("api_public_put_update_invoice_number")
            ));
        } else {
            throw new AccessDeniedException('Debe estar logueado para ingresar a esta sección');
        }
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


        return $this->render('RocketSellerTwoPickBundle:PurchaseOrders:purchase-orders-create.html.twig', array(
            'form' => $form->createView()
        ));
    }
}