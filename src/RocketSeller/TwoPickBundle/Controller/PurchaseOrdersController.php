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
                $dateCreated = $order->getDateCreated()->format('d/m/Y');
                $orders[$key]['purchaseOrderDateCreated'] = $dateCreated;
                $lastModified = $order->getDateModified()->format('d/m/Y');
                $orders[$key]['lastModified'] = $lastModified;
            }

            return $this->render('RocketSellerTwoPickBundle:General:purchase-orders.html.twig', array(
                'orders' => $orders
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
            $data[$key]['purchaseOrderType'] = $po->getPurchaseOrdersTypePurchaseOrdersType()->getName();
            $dateCreated = $po->getDateCreated()->format('d/m/Y');
            $data[$key]['purchaseOrderDateCreated'] = $dateCreated;
            $lastModified = $po->getDateModified()->format('d/m/Y');
            $data[$key]['lastModified'] = $lastModified;
            $data[$key]['invoiceNumber'] = $po->getInvoiceNumber();
        }
        $detail = array();
        foreach($dataDescription as $key => $pod) {
            $detail[$key]['idPurchaseOrdersDescription'] = $pod->getIdPurchaseOrdersDescription();
            $detail[$key]['taxName'] = $pod->getTaxTax()->getName();
            $detail[$key]['description'] = $pod->getDescription();
            $prod = $pod->getProductProduct();
            $detail[$key]['product'] = $prod->getName();
        }

        $details = array(
            'purchaseOrder' => $data,
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

    /**
     * Metodo que se utiliza para actualizar el numero de la factura en una orden de compra
     * @param Request $request
     * @param idPO - Parametro recibido por POST, indica el ID de la orden de compra a actualizar
     * @param invoiceNumber - Parametro recibido por POST, indica el numero de la factura que se va
     *                      a agregar a la orden de compra
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * Retorna un json con los estados de la transaccion, success = 1, error = 2
     */
    public function updateInvoiceNumberAction(Request $request) {

        $idPO = $request->request->get('idPO');
        $invoiceNumber = $request->request->get('invoiceNumber');
        $em = $this->getDoctrine()->getManager();
        $purchaseOrdersRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");
        $dataPO = $purchaseOrdersRepository->findByIdPurchaseOrders($idPO);

        foreach ($dataPO as $po) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($po);
            $em->flush();
            try {
                $status = 1;
                $po->setInvoiceNumber($invoiceNumber);
            } catch(\Exception $e) {
                $status = 2;
            }
            $em->persist($po);
            $em->flush();
        }

        $res = array(
            'status' => $status
        );
        return new JsonResponse($res);
    }
}