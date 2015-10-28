<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersRepository;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;

class PurchaseOrdersController extends Controller
{
    public function indexAction()
    {
        if (is_object($this->getUser()) && $this->getUser() instanceof UserInterface ) {

            $em = $this->container->get('doctrine')->getEntityManager();
            $purchaseOrdersRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");
// echo $this->getUser()->getId();
            $ordersByUser = $purchaseOrdersRepository->getOrdersForEmployer($this->getUser()->getId());
// $orders = $purchaseOrdersRepository->getOrders();
// var_dump($orders);
            $orders = array();
            foreach ($ordersByUser as $key => $order) {
                $orders[$key]['idPurchaseOrders'] = $order->getIdPurchaseOrders();
                $orders[$key]['purchaseOrdersTypePurchaseOrdersType'] = $order->getPurchaseOrdersTypePurchaseOrdersType();
                $orders[$key]['payrollPayroll'] = $order->getPayrollPayroll();
                $orders[$key]['purchaseOrdersStatusPurchaseOrdersStatus'] = $order->getPurchaseOrdersStatusPurchaseOrdersStatus();
            }

            return $this->render('RocketSellerTwoPickBundle:General:purchase-orders.html.twig', array(
                'orders' => $orders
            ));
        } else {
            throw new AccessDeniedException('Debe estar logueado para ingresar a esta sección');
        }
    }

    public function detailAction($id)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $purchaseOrdersRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");

        $data = $purchaseOrdersRepository->getPurchaseOrderDescription($id);
        $detail = array();
        foreach($data as $pod) {
            $detail['idPurchaseOrdersDescription'] = $pod->getIdPurchaseOrdersDescription();
            $detail['taxTax'] = $pod->getTaxTax();
            $detail['purchaseOrdersPurchaseOrders'] = $pod->getPurchaseOrdersPurchaseOrders();
            $prod = $pod->getProductProduct();
            $detail['productProduct'] = $prod->getIdProduct();
        }
        return new JsonResponse($detail);
    }
}