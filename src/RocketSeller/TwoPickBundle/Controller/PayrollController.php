<?php

namespace RocketSeller\TwoPickBundle\Controller;

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
                    if ((int) $day <= 15) {
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

    public function payAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $data = $this->getInfoPayroll($this->getUser()->getPersonPerson()->getEmployer());

        $novelties = array();
        if ($data) {
            foreach ($data as $key => $value) {
                foreach ($value["detailNomina"] as $key2 => $value2) {
                    $grupo = isset($value2["CON_CODIGO_DETAIL"]["grupo"]) ? $value2["CON_CODIGO_DETAIL"]["grupo"] : false;
                    if ($grupo && $grupo != "no_show") {
                        if (!isset($novelties[$key])) {
                            $novelties[$key] = array();
                        }
                        array_push($novelties[$key], $value2);
                    }
                }
            }
        }
        dump($data);
        return $this->render('RocketSellerTwoPickBundle:Payroll:pay.html.twig', array(
                    'dataNomina' => $data,
                    'novelties' => $novelties
        ));
    }

    public function calculateAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            /* @var $user User */
            $user = $this->getUser();
            $payrollToPay = $request->request->get('payrollToPay');
            $data = $this->getInfoPayroll($this->getUser()->getPersonPerson()->getEmployer(), $payrollToPay);
            if ($data) {
                $documentNumber = $this->getUser()->getPersonPerson()->getDocument();
                $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods', array('idUser' => $user->getId()), array('_format' => 'json'));
                dump($clientListPaymentmethods);
                $responcePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true);

                return $this->render('RocketSellerTwoPickBundle:Payroll:calculate.html.twig', array(
                            'dataNomina' => $data,
                            'paymentMethods' => isset($responcePaymentsMethods["payment-methods"]) ? $responcePaymentsMethods["payment-methods"] : false
                ));
            } else {
                return $this->redirectToRoute("payroll");
            }
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
            $payrollToPay = $request->request->get('payrollToPay');
            $data = $this->getInfoPayroll($user->getPersonPerson()->getEmployer(), $payrollToPay);
            if ($data) {
                $total = 0;
                $paymentMethod = $request->request->get('paymentMethod');
                foreach ($data as $key => $value) {
                    /* @var $payMethod PayMethod */
                    $payMethod = $value['payMethod'];
                    if (isset($value['PILA']['total'])) {
                        if (strtolower($payMethod->getPayTypePayType()->getName()) == 'en efectivo') {
                            $total += ($value['PILA']['total']);
                        } else {
                            $total += ($value['totalLiquidation']['total'] + $value['PILA']['total']);
                        }
                    } else {
                        if (strtolower($payMethod->getPayTypePayType()->getName()) == 'en efectivo') {
                            $total += 0;
                        } else {
                            $total += ($value['totalLiquidation']['total']);
                        }
                    }

                    if (isset($paymentMethod[$key])) {
                        $data[$key]['paymentMethod'] = $paymentMethod[$key];
                    } else {
                        $data[$key]['paymentMethod'] = 'efectivo';
                    }
                }
                $purchaseOrders = $this->pagarNomina($data, $total);
                $responce = null;
                foreach ($purchaseOrders as $key => $purchaseOrder) {
                    $responce = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getPayPurchaseOrder', array(
                        "idPurchaseOrder" => $purchaseOrder->getIdPurchaseOrders()
                            ), array('_format' => 'json')
                    );
                    $responceC = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postExecutePayrollLiquidation', array(
                        "employee_id" => $purchaseOrder->getPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee(),
                        'execution_type' => 'C'
                            ), array('_format' => 'json')
                    );
                }
                if (!empty($responce) && $responce !== null) {
                    //dump($responce);
                    //die;
                    $data2 = json_decode($responce->getContent(), true);
                    if ($responce->getStatusCode() == Response::HTTP_OK) {
                        $this->addFlash('success', $data2);
                        $this->redirectToRoute('payroll_success', array());

                        //$this->redirect($this->generateUrl('payroll_success'), 301);
                        //$this->sendEmailPaySuccessAction();
                        return $this->render('RocketSellerTwoPickBundle:Payroll:confirm.html.twig', array(
                                    'dataNomina' => $data,
                                    'total' => $total
                        ));
                    }
                    $this->addFlash('error', $responce->getContent());
                    return $this->redirectToRoute("payroll_error");
                }
                $this->addFlash('error', 'Error RocketSellerTwoPickBundle:PaymentMethodRest:getPayPurchaseOrder');
                return $this->redirectToRoute("payroll_error");
            } else {
                $this->addFlash('error', $data);
                return $this->redirectToRoute('payroll_error');
            }
        } else {
            return $this->redirectToRoute("payroll");
        }
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

    public function payrollErrorAction(Request $request)
    {
        return $this->render('RocketSellerTwoPickBundle:Payroll:error.html.twig', array(
                    'user' => $this->getUser()
        ));
    }

}
