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
use Symfony\Component\Validator\Constraints\Date;

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

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $purchaseOrder = new PurchaseOrders();

            $purchaseOrdersTypePurchaseOrdersType = $form->get('purchaseOrdersType');
            $payrollPayroll = $form->get('payroll');
            $pos = $form->get('purchaseOrdersStatus');

            if ($payrollPayroll->getData()) {
                $purchaseOrder->setPayrollPayroll($payrollPayroll->getData());
            }
            $purchaseOrder->setPurchaseOrdersStatusPurchaseOrdersStatus($pos->getData());
            $purchaseOrder->setPurchaseOrdersTypePurchaseOrdersType($purchaseOrdersTypePurchaseOrdersType->getData());

            $dateCreated = date("Y-m-d H:i:s");
            $purchaseOrder->setDateCreated(new \DateTime($dateCreated));
            $dateModified = date("Y-m-d H:i:s");
            $purchaseOrder->setDateModified(new \DateTime($dateModified));
            $idUser = $this->getUser();
            $purchaseOrder->setIdUser($idUser);
            $name = $form->get("name");
            $purchaseOrder->setName($name->getData());

            $em->persist($purchaseOrder);
            $em->flush();

            return $this->render('RocketSellerTwoPickBundle:PurchaseOrders:purchase-orders-success.html.twig');
        }


        return $this->render('RocketSellerTwoPickBundle:PurchaseOrders:purchase-orders-create.html.twig', array(
            'form' => $form->createView()
        ));
    }
}