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
                $orders[$key]['idPurchaseOrders'] = $order->getIdPurchaseOrders();
                $orders[$key]['purchaseOrderType'] = $order->getPurchaseOrdersTypePurchaseOrdersType()->getName();
                $newDate = $order->getDateCreated()->format('d/m/Y');
                $orders[$key]['purchaseOrderDateCreated'] = $newDate;
            }

            return $this->render('RocketSellerTwoPickBundle:General:purchase-orders.html.twig', array(
                'orders' => $orders
            ));
        } else {
            throw new AccessDeniedException('Debe estar logueado para ingresar a esta sección');
        }
    }

    /**
     * Obtener el detalle de una orden de compra
     * @param int $id - Id de la orden de compra para obtener su correspondiente detalle
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function detailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $purchaseOrdersRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");

        $data = $purchaseOrdersRepository->findByPurchaseOrdersPurchaseOrders($id);

        $detail = array();
        foreach($data as $key => $pod) {
            $detail[$key]['idPurchaseOrdersDescription'] = $pod->getIdPurchaseOrdersDescription();
            $detail[$key]['taxName'] = $pod->getTaxTax()->getName();
            $detail[$key]['description'] = $pod->getDescription();
            $prod = $pod->getProductProduct();
            $detail[$key]['product'] = $prod->getName();
        }
        return new JsonResponse($detail);
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
            $payroll = $form->get('payroll');
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