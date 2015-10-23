<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersRepository;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class PurchaseOrdersController extends Controller
{
    public function indexAction()
    {
        if (is_object($this->getUser()) && $this->getUser() instanceof UserInterface ) {


//             $purchaseOrdersRepository = new PurchaseOrdersRepository($em, $class);
            $em = $this->container->get('doctrine')->getEntityManager();
            $purchaseOrdersRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");

            $orders = $purchaseOrdersRepository->getOrders();
            return $this->render('RocketSellerTwoPickBundle:General:purchase-orders.html.twig', array(
                'orders' => $orders
            ));
        } else {
            throw new AccessDeniedException('Debe estar logueado para ingresar a esta sección');
        }
    }
}