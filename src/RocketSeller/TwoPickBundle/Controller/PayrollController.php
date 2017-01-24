<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Config;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Traits\PayrollMethodsTrait;

class PayrollController extends Controller
{

    use PayrollMethodsTrait;

    /**
     * Crear Payroll para el contrato
     *
     * @param String $idContract
     * @param Boolean $deleteActivePayroll para indicar que se elimina el payroll activo
     * @param String $period
     * @param String $month
     * @param String $year
     * @return Payroll
     *
     */
    public function createPayrollToContractAction($idContract, $deleteActivePayroll = false, $period = null, $month = null, $year = null)
    {
        $contractRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract');
        /* @var $contract Contract */
        $contract = $contractRepo->findOneBy(array('idContract' => $idContract));
        if ($contract && !empty($contract) && $contract !== null && $contract->getState() == 1) {
            $em = $this->getDoctrine()->getManager();

            if ($period == null && $month == null && $year == null) {
                $frequencyPay = $contract->getFrequencyFrequency()->getPayrollCode();
                /* @var $startDate date */
                $startDate = $contract->getStartDate();
                $day = $startDate->format('d');
                $month = $startDate->format('m');
                $year = $startDate->format('Y');
                if ($month < date('m')) { //si el mes de inicio del contrato es menor al mes actual
                    if ($year <= date('Y')) {//y el año de inicio del contrato es menor al año actual
                        $month = date('m'); //el mes del payroll es el actual para no general payroll de periodos anteriores
                    }
                }
                if ($year < date('Y')) {
                    $year = date('Y');
                }
                if ($frequencyPay == 'J') {
                    $period = '4';
                } elseif ($frequencyPay == 'Q') {
                    if ((int)$day <= 15) {
                        $period = '2';
                    } else {
                        $period = '4';
                    }
                } elseif ($frequencyPay == 'M') {
                    $period = '4';
                }
            }

            $updateActivePayroll = true;
            /* @var $payrollActive Payroll */
            $payrollActive = $contract->getActivePayroll();

            $payrollRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Payroll');
            /* @var $payrollRep Payroll */
            $payrollRep = $payrollRepo->findOneBy(array(
                'contractContract' => $contract->getIdContract(),
                'period' => $period,
                'month' => $month,
                'year' => $year
            ));

            if (!$payrollActive || empty($payrollActive) || $payrollActive === null) {
                if (!$payrollRep || empty($payrollRep) || $payrollRep === null) {
                    $payroll = new Payroll();
                    $payroll->setContractContract($contract);
                    $payroll->setPeriod($period);
                    $payroll->setMonth($month);
                    $payroll->setYear($year);
                    $em->persist($payroll);
                    $em->flush();
                } else {
                    $payroll = $payrollRep;
                }
            } else {
                if (!$payrollRep || empty($payrollRep) || $payrollRep === null) {
                    if ($deleteActivePayroll) {
                        $payroll = $payrollActive;
                        $payroll->setPeriod($period);
                        $payroll->setMonth($month);
                        $payroll->setYear($year);
                        $em->persist($payroll);
                        $em->flush();
                    } else {
                        if ($payrollActive->getMonth() == $month && $payrollActive->getYear() == $year && $payrollActive->getPeriod() == $period) {
                            $updateActivePayroll = false;
                        } else {
                            $payroll = new Payroll();
                            $payroll->setContractContract($contract);
                            $payroll->setPeriod($period);
                            $payroll->setMonth($month);
                            $payroll->setYear($year);
                            $em->persist($payroll);
                            $em->flush();
                        }
                    }
                } else {
                    if ($deleteActivePayroll) {
                        if ($payrollActive->getMonth() == $month && $payrollActive->getYear() == $year && $payrollActive->getPeriod() == $period) {
                            $updateActivePayroll = false;
                        } else {
                            $payroll = $payrollRep;
                            $payrollActive->setContractContract(null);
                            $contract->setActivePayroll(null);
                            $em->persist($payrollActive);
                            $em->remove($payrollActive);
                            $em->flush();
                        }
                    } else {
                        $payroll = $payrollRep;
                    }
                }
            }

            if ($updateActivePayroll) {
                $contract->setActivePayroll($payroll);
                $em->persist($contract);
                $em->flush();
            }
            return true;
        } else {
            return false;
        }
    }

    public function payAction($idNotif)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $request = $this->container->get('request');
        /** @var User $user */
        $user=$this->getUser();
        if($idNotif!=-1){
            $request->setMethod("POST");
            $request->request->add(array(
                "notificationId" => $idNotif,
                "status" => 0,
            ));
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NotificationRest:postChangeStatus', array("request" => $request), array('_format' => 'json'));
            if ($insertionAnswer->getStatusCode() != 200) {
                return false;
            }
        }
        $request->setMethod("GET");
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRestSecured:getPay', array("idUser" => $user->getId()), array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            return false;
        }
        $ANS=json_decode($insertionAnswer->getContent(), true);
        $ANS=json_decode($ANS, true);

        return $this->render('RocketSellerTwoPickBundle:Payroll:pay.html.twig', $ANS);

    }

    public function calculateAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            /* @var $user User */
            $user = $this->getUser();
            $userPerson = $user->getPersonPerson();
            $payrollToPay = $request->request->all();
            if (count($payrollToPay) == 0) {
                //por seguridad se verifica que exista por lo menos un item a pagar
                return $this->redirectToRoute("payroll");
            }
            $realtoPay=array();
            $paidPO=array();
            foreach ($payrollToPay as $key=>$value ) {

                $exploded = explode("-", $value);
                if ($exploded[0] == 'p') {
                    $realtoPay[]=$key;

                }else{
                    $paidPO[]=$key;
                }

            }
            $request->setMethod("POST");
            $request->request->add(array(
                "idUser" => $user->getId(),
                "podsToPay" => $realtoPay,
                "podsPaid" => $paidPO,
            ));
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRestSecured:postCalculatePay', array('_format' => 'json'));
            if ($insertionAnswer->getStatusCode() != 200) {
                return false;
            }

            $ANS=json_decode($insertionAnswer->getContent(), true);
            $ANS=json_decode($ANS, true);
            return $this->render('RocketSellerTwoPickBundle:Payroll:calculate.html.twig', $ANS);

        } else {
            return $this->redirectToRoute("payroll");
        }
    }

    public function confirmAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            /* @var $user User */
            $user = $this->getUser();
            $userPerson = $user->getPersonPerson();
            $payrollToPay = $request->request->all();

            if (count($payrollToPay) == 0) {
                //por seguridad se verifica que exista por lo menos un item a pagar
                return $this->redirectToRoute("show_dashboard");
            }

            $poStatusProcesando = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')
                ->findOneBy(array('idNovoPay' => 'S2'));

            $realtoPay=array();
            $paymethodid=$payrollToPay["paymentMethod"];
            unset($payrollToPay["paymentMethod"]);
            foreach ($payrollToPay as $key=>$value ) {
                $realtoPay[]=$key;

                /** @var PurchaseOrdersDescription $pod */
                $pod = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')
                    ->find($key);
	              if($pod->getPurchaseOrders()!=null && $pod->getPurchaseOrders()->getPurchaseOrdersStatus() == $poStatusProcesando) {
                    return $this->redirectToRoute("show_dashboard");
                }
            }

            $request->setMethod("POST");
            $request->request->add(array(
                "idUser" => $user->getId(),
                "podsToPay" => $realtoPay,
                "paymentMethod" => $paymethodid,
            ));
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRestSecured:postConfirm', array('request'=>$request), array('_format' => 'json'));
            if ($insertionAnswer->getStatusCode() != 200) {
                if($insertionAnswer->getStatusCode()==421)
                    return $this->redirectToRoute("show_employer");
                return $this->redirectToRoute("list_pods_description");
            }

            return $this->redirectToRoute("payroll_result",json_decode($insertionAnswer->getContent(), true));

        } else {
            return $this->redirectToRoute("manage_employees");
        }
    }

    public function createNewPayroll(Payroll $payroll)
    {
        $this->addFlash('success', 'createNewPayroll');
        $this->addFlash('success', json_encode($payroll));
    }

    public function payrollSuccessAction(Request $request)
    {
        return $this->render('RocketSellerTwoPickBundle:Payroll:confirm.html.twig', array(
            'dataNomina' => $data,
            'total' => $total
        ));
    }

    private function dataByPaymethod($dataNomina)
    {
        $responce = array();
        //dump($dataNomina);
        foreach ($dataNomina as $idPayroll => $data) {
            if (isset($data['paymentMethod'])) {
                $responce[$data['paymentMethod']][$idPayroll]['idPayroll'] = $idPayroll;

                $responce[$data['paymentMethod']][$idPayroll]['PILA'] = $data['PILA']['total'];

                /* @var $payMethod PayMethod */
                $payMethod = $data['payMethod'];
                if (strtolower($payMethod->getPayTypePayType()->getName()) == 'en efectivo') {
                    $responce[$data['paymentMethod']][$idPayroll]['nomina'] = 0;
                    $responce[$data['paymentMethod']][$idPayroll]['total'] = $data['PILA']['total'];
                } else {
                    $responce[$data['paymentMethod']][$idPayroll]['nomina'] = $data['totalLiquidation']['total'];
                    $responce[$data['paymentMethod']][$idPayroll]['total'] = $data['totalLiquidation']['total'] + $data['PILA']['total'];
                }
            }
        }
        foreach ($responce as $paymentMethodId => $data) {
            $total = 0;
            foreach ($data as $idPayroll => $value) {
                $total += $value['total'];
            }
            $responce[$paymentMethodId]['total'] = $total;
        }
        return $responce;
    }

    private function pagarNomina($dataNomina, $total)
    {
        $response = $mensaje = false;
        $em = $this->getDoctrine()->getManager();
        $byPaymethod = $this->dataByPaymethod($dataNomina);
        $purchaseOrderArray = array();
        foreach ($byPaymethod as $paymentMethodId => $dataPayroll) {
            /* @var $purchaseOrder PurchaseOrders */
            $purchaseOrder = $this->createPurchaseOrder($paymentMethodId, $dataPayroll['total']);
            if ($purchaseOrder) {
                foreach ($dataPayroll as $idPayroll => $dataTotales) {
                    if ($idPayroll != 'total') {
                        $purchaseOrder = $this->createPurchaseOrderDetail($purchaseOrder, $dataNomina[$idPayroll], $dataTotales);
                    }
                }
                $em->persist($purchaseOrder);
                $em->flush();
                $purchaseOrderArray[] = $purchaseOrder;
            }
        }
        return $purchaseOrderArray;
    }

    /**
     *
     * @param type $total
     * @return PurchaseOrders
     */
    private function createPurchaseOrder($methodId, $totalAmount)
    {
        $em = $this->getDoctrine()->getManager();
        $purchaseOrder = new PurchaseOrders();
        $purchaseOrder->setIdUser($this->getUser());
        $purchaseOrder->setName('Pago Nomina');
        $purchaseOrder->setValue((floatval($totalAmount)));
        $purchaseOrder->setPayMethodId($methodId);
        $purchaseOrdersStatusRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
        /** @var $purchaseOrdersStatus PurchaseOrdersStatus */
        $purchaseOrdersStatus = $purchaseOrdersStatusRepo->findOneBy(array('name' => 'Pendiente'));
        $purchaseOrder->setPurchaseOrdersStatus($purchaseOrdersStatus);
        $em->persist($purchaseOrder);
        $em->flush(); //para obtener el id que se debe enviar a novopay
        return $purchaseOrder;
    }

    /**
     *
     * @param type $dataNomina
     * @param type $dataTotales
     * @return PurchaseOrdersDescription
     */
    private function createPurchaseOrderDetail(PurchaseOrders $purchaseOrder, $dataNomina, $dataTotales)
    {
        $em = $this->getDoctrine()->getManager();
        $purchaseOrderDescription = null;
        foreach ($dataTotales as $key => $amount) {
            if ((strtolower($key) == "pila" || strtolower($key) == "nomina") && ($amount != 0)) {

                /* @var $payroll Payroll */
                $payroll = $dataNomina['payroll'];
                /* @var $employee EmployerHasEmployee */
                $employee = $dataNomina['employerHasEmployee'];
                $beneficiaryId = $employee->getIdEmployerHasEmployee();

                $purchaseOrderDescription = new PurchaseOrdersDescription();
                $purchaseOrderDescription->setPurchaseOrders($purchaseOrder);
                $purchaseOrderDescription->setPayrollPayroll($payroll);
                $productRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Product');
                if (strtolower($key) == "pila") {
                    /* @var $product Product */
                    $product = $productRepo->findOneBy(array('simpleName' => 'PP'));
                    $purchaseOrderDescription->setDescription('Pago PILA Empleado');
                } else {
                    /* @var $product Product */
                    $product = $productRepo->findOneBy(array('simpleName' => 'PN'));
                    $purchaseOrderDescription->setDescription('Pago Nomina Empleado');
                }
                $purchaseOrderDescription->setProductProduct($product);
                $purchaseOrderDescription->setValue($amount);

                $purchaseOrdersStatusRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
                /** @var $purchaseOrdersStatus PurchaseOrdersStatus */
                $purchaseOrdersStatus = $purchaseOrdersStatusRepo->findOneBy(array('name' => 'Pendiente'));
                $purchaseOrderDescription->setPurchaseOrdersStatus($purchaseOrdersStatus);

                $purchaseOrder->addPurchaseOrderDescription($purchaseOrderDescription);
                $em->persist($purchaseOrderDescription);
                $em->persist($purchaseOrder);
            }
        }

        $em->flush();
        return $purchaseOrder;
    }

    public function detailAction($idPayroll, Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('RocketSellerTwoPickBundle:Payroll:detail.html.twig', array());
    }

    public function voucherAction($idPayroll, Request $request)
    {
        $payrollRepo = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Payroll');
        $payroll = $payrollRepo->findBy(array('idPayroll' => $idPayroll))[0];

        $contract = $payroll->getContractContract();
        $employerEmployee = $contract->getEmployerHasEmployeeEmployerHasEmployee();
        $employee = $employerEmployee->getEmployeeEmployee();
        $documentNumber = $employerEmployee->getEmployeeEmployee()->getPersonPerson()->getDocument();
        $employeer = $employerEmployee->getEmployerEmployer();

        $generalPayroll = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll', array(
            'employeeId' => $documentNumber,
            'period' => null,
            'month' => null,
            'year' => null
        ), array('_format' => 'json')
        );
        $generalPayroll = json_decode($generalPayroll->getContent(), true);

        return $this->render('RocketSellerTwoPickBundle:Payroll:comprobante.html.twig', array(
            'employeer' => $employeer,
            'employee' => $employee,
            'contract' => $contract,
            'generalPayroll' => $generalPayroll,
            'payroll' => $payroll,
            'periodo' => 'Octubre 1 al 15  de 2015'
        ));
    }

    public function voucherToPdfAction($idPayroll, Request $request)
    {
        $pageUrl = $this->generateUrl('payroll_voucher', array('idPayroll' => $idPayroll), true); // use absolute path!

        return new Response(
            $this->get('knp_snappy.pdf')->getOutput($pageUrl), 200, array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="file.pdf"'
            )
        );

        $this->get('knp_snappy.pdf')->getOutput($pageUrl, 'file.pdf');

        $file = 'file.pdf';
        $filename = 'filename.pdf';
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        @readfile($file);
    }

    public function payrollErrorAction($result, $idPO)
    {
        $url = "";
        $po=null;
        if ($idPO != -1) {
            $url = $this->generateUrl("download_documents", array('ref' => 'factura', 'id' => $idPO, 'type' => 'pdf'));
            /** @var PurchaseOrders $po */
            $po= $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrders")->find($idPO);
        }
        return $this->render('RocketSellerTwoPickBundle:Payroll:error.html.twig', array(
            'result' => $result,
            'url' => $url,
            'po' => $po
        ));
    }
    
    public function showDetailsAction($idPayRoll, Request $request){
        if($idPayRoll!=-1){
            /** @var Payroll $payroll */
            $payroll=$this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Payroll")->find($idPayRoll);
            if($payroll){
                $SQLNovelties=$payroll->getSqlNovelties();
            }
            $total = 0;
            $dev = 0;
            $ded = 0;
            /** @var Novelty $nov */
            foreach ($SQLNovelties as $nov){
               if ($nov->getNoveltyTypeNoveltyType()->getNaturaleza()=="DEV"){
                    $total += $nov->getSqlValue();
                    $dev += $nov->getSqlValue();
               }else{
                   $total -= $nov->getSqlValue();
                   $ded += $nov->getSqlValue();
               }
            }
            return $this->render('RocketSellerTwoPickBundle:Payroll:showDetails.html.twig', array(
                'novelties'=>$SQLNovelties,
                'total'=> $total,
                'dev'=> $dev,
                'ded'=> $ded,
            ));
        }
    }


}
