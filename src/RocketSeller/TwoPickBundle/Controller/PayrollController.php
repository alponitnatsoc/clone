<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use RocketSeller\TwoPickBundle\Traits\LiquidationMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\NoveltyTypeMethodsTrait;

class PayrollController extends Controller
{

    use NoveltyTypeMethodsTrait;

use LiquidationMethodsTrait;

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
                $frequencyPay = $contract->getPayMethodPayMethod()->getFrequencyFrequency()->getPayrollCode();
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

    /**
     * Trae la informacion del empleado desde el ws de nomina de SQL
     * @param type $employerHasEmployee
     */
    private function getInfoNominaSQL($employerHasEmployee)
    {
        $employeeId = $employerHasEmployee->getIdEmployerHasEmployee();

        $generalPayroll = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll', array(
            'employeeId' => $employeeId,
            'period' => null,
            'month' => null,
            'year' => null
                ), array('_format' => 'json')
        );

        return json_decode($generalPayroll->getContent(), true);
    }

    private function getSalary($dataNomina)
    {
        if ($dataNomina && !empty($dataNomina)) {
            foreach ($dataNomina as $key => $value) {
                if (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '1') {
                    return (int) ceil($value['NOMI_VALOR_LOCAL']);
                }
            }
        }
        return false;
    }

    private function getTotalAportes($dataNomina)
    {
        if ($dataNomina && !empty($dataNomina)) {
            $aporteSalud = $aportePension = 0;
            foreach ($dataNomina as $key => $value) {
                if (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '3010') {
                    $aporteSalud = $value['NOMI_VALOR_LOCAL'];
                } elseif (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '3020') {
                    $aportePension = $value["NOMI_VALOR_LOCAL"];
                }
            }
            return array(
                'total' => ceil($aporteSalud + $aportePension),
                'salud' => ceil($aporteSalud),
                'pension' => ceil($aportePension)
            );
        }
        return false;
    }

    private function getTotalPILA($salary)
    {
        return array(
            'total' => (int) ceil(($salary * 0.12) + ceil($salary * 0.085) + ceil($salary * 0.00348) + ceil($salary * 0.09)),
            'pension' => (int) ceil($salary * 0.12),
            'salud' => (int) ceil($salary * 0.085),
            'arl' => (int) ceil($salary * 0.00348),
            'parafiscales' => (int) ceil($salary * 0.09)
        );
    }

    private function getData($payrollToPay = false)
    {
        $user = $this->getUser();
        $employerHasEmployees = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        $employeesData = array();

        /* @var $employerHasEmployee EmployerHasEmployee */
        foreach ($employerHasEmployees as $employerHasEmployee) {
            if ($employerHasEmployee->getState() > 0) {
                $contracts = $employerHasEmployee->getContracts();
                /* @var $contract Contract */
                foreach ($contracts as $contract) {
                    if ($contract->getState() > 0) {
                        /* @var $payroll Payroll */
                        $payroll = $contract->getActivePayroll();
                        if ($payrollToPay === false) {
                            $payrollToPay2 = array($payroll->getIdPayroll());
                        } else {
                            $payrollToPay2 = $payrollToPay;
                        }
                        if (in_array($payroll->getIdPayroll(), $payrollToPay2)) {
                            //PENDIENTE - validar que payroll corresponda al periodo y fecha actual, para no pagar facturas pasadas ni futuras
                            /* @var $purchaseOrdersDescription PersistentCollection */
                            $purchaseOrdersDescription = $payroll->getPurchaseOrdersDescription();
                            $purchaseOrdersStatus = $purchaseOrdersDescription->isEmpty();

                            if ($purchaseOrdersStatus) {
                                $employeesData[$payroll->getIdPayroll()] = array();
                                $employeesData[$payroll->getIdPayroll()]['idPayroll'] = $payroll->getIdPayroll();
                                $employeesData[$payroll->getIdPayroll()]['payroll'] = $payroll;
                                $employeesData[$payroll->getIdPayroll()]['employerHasEmployee'] = $employerHasEmployee;
                                $employeesData[$payroll->getIdPayroll()]['payMethod'] = $contract->getPayMethodPayMethod();

                                $detailNomina = $this->getInfoNominaSQL($employerHasEmployee);

                                $employeesData[$payroll->getIdPayroll()]['detailNomina'] = $detailNomina;

                                $totalLiquidation = $this->totalLiquidation($detailNomina);
                                $employeesData[$payroll->getIdPayroll()]['totalLiquidation'] = $totalLiquidation;

                                $salary = $this->getSalary($detailNomina);
                                $employeesData[$payroll->getIdPayroll()]['salary'] = $salary;

                                $totalAportes = $this->getTotalAportes($detailNomina);
                                $employeesData[$payroll->getIdPayroll()]['totalAportes'] = $totalAportes;

                                if ($payroll->getPeriod() == 4) {
                                    $employeesData[$payroll->getIdPayroll()]['PILA'] = $this->getTotalPILA($salary);
                                } else {
                                    $employeesData[$payroll->getIdPayroll()]['PILA'] = $this->getTotalPILA(0);
                                    ;
                                }
                            } else {
                                $this->addFlash('error', 'Ya hay una orden de pago en proceso');
                            }
                        } else {
                            $this->addFlash('error', 'No existe nomina activa');
                        }
                    }
                }
            }
        }
        if (!empty($employeesData)) {
            return $employeesData;
        }
        return false;
    }

    public function payAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $data = $this->getData();
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

            $payrollToPay = $request->request->get('payrollToPay');
            $data = $this->getData($payrollToPay);
            if ($data) {
                $documentNumber = $this->getUser()->getPersonPerson()->getDocument();
                $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:getClientListPaymentmethods', array('documentNumber' => $documentNumber), array('_format' => 'json'));
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

            $payrollToPay = $request->request->get('payrollToPay');
            $data = $this->getData($payrollToPay);
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
                    $data[$key]['paymentMethod'] = $paymentMethod[$key];
                }

                if ($this->pagarNomina($data, $total)) {
                    return $this->render('RocketSellerTwoPickBundle:Payroll:confirm.html.twig', array(
                                'dataNomina' => $data,
                                'total' => $total
                    ));
                } else {
                    return $this->redirectToRoute('payroll');
                }
            } else {
                return $this->redirectToRoute("payroll");
            }
        } else {
            return $this->redirectToRoute("payroll");
        }
    }

    private function dataByPaymethod($dataNomina)
    {
        $responce = array();
        foreach ($dataNomina as $idPayroll => $data) {
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
        foreach ($byPaymethod as $paymentMethodId => $dataPayroll) {
            $purchaseOrderResponse = $this->createPurchaseOrder($paymentMethodId, $dataPayroll['total']);
            /* @var $purchaseOrder PurchaseOrders */
            $purchaseOrder = $purchaseOrderResponse['purchaseOrder'];
            if ($purchaseOrderResponse['response']) {
                foreach ($dataPayroll as $idPayroll => $dataTotales) {
                    if ($idPayroll != 'total') {
                        $purchaseOrderDetail = $this->createPurchaseOrderDetail($purchaseOrder, $dataNomina[$idPayroll], $dataTotales);
                        if ($purchaseOrderDetail) {
                            $response = true;
                        } else {
                            $response = false;
                        }
                    }
                }
                //tax 
                $response = true;
            } else {
                $response = false;
            }
            $em->persist($purchaseOrder);
        }

        $em->flush();

        return $response;
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

        $em->persist($purchaseOrder);
        $em->flush(); //para obtener el id que se debe enviar a novopay

        $request = new Request();
        $request->setMethod('POST');
        $request->request->set('documentNumber', $this->getUser()->getPersonPerson()->getDocument());
        $request->request->set('MethodId', $methodId);
        $request->request->set('totalAmount', $totalAmount);
        $request->request->set('taxAmount', 0);
        $request->request->set('taxBase', 0);
        $request->request->set('commissionAmount', 0);
        $request->request->set('commissionBase', 0);
        $request->request->set('chargeMode', 2);
        $request->request->set('chargeId', $purchaseOrder->getIdPurchaseOrders());

        $paymentAprovalResponse = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postPaymentAproval', array('request' => $request), array('_format' => 'json'));

        $paymentAproval = json_decode($paymentAprovalResponse->getContent(), true);

        if (isset($paymentAproval['charge-rc'])) {
            $purchaseOrdersStatusRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
            /* @var $purchaseOrdersStatus PurchaseOrdersStatus */
            $purchaseOrdersStatus = $purchaseOrdersStatusRepo->findOneBy(array('idNovoPay' => $paymentAproval['charge-rc']));
            $purchaseOrder->setPurchaseOrdersStatus($purchaseOrdersStatus);

            $em->persist($purchaseOrder);
            $em->flush(); //guardar el resultado de novopay
        } else {
            $purchaseOrdersStatus = false;
        }

        if ($paymentAprovalResponse->getStatusCode() == Response::HTTP_CREATED && $purchaseOrdersStatus->getName() == 'Aprobado') {
            return array('response' => true, 'purchaseOrder' => $purchaseOrder, 'paymentAproval' => $paymentAproval);
        } else {
            if ($purchaseOrdersStatus) {
                $this->addFlash('error', '<b>' . $purchaseOrdersStatus->getDescription() . '</b>, No se pudo realizar el pago!');
            } else {
                $this->addFlash('error', 'No se pudo realizar el pago!');
            }
            return array('response' => false, 'purchaseOrder' => $purchaseOrder, 'paymentAproval' => $paymentAproval);
        }
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
                $purchaseOrderDescription->setTaxTax(null);
                $purchaseOrderDescription->setValue($amount);

                $purchaseOrdersStatusRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
                /* @var $purchaseOrdersStatus PurchaseOrdersStatus */
                $purchaseOrdersStatus = $purchaseOrdersStatusRepo->findOneBy(array('idNovoPay' => 'S1'));
                $purchaseOrderDescription->setPurchaseOrdersStatus($purchaseOrdersStatus);

                $request = new Request();
                $request->setMethod('POST');
                $request->request->set('documentNumber', $this->getUser()->getPersonPerson()->getDocument());
                $request->request->set('chargeId', $purchaseOrder->getIdPurchaseOrders());
                $request->request->set('beneficiaryId', $beneficiaryId);
                $request->request->set('beneficiaryAmount', $amount);

                if (strtolower($key) == "pila") {
                    $request->request->set('dispersionType', 2);
                } else {
                    $request->request->set('dispersionType', 1);
                }

                $paymentResponse = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postClientPayment', array('request' => $request), array('_format' => 'json'));

                $payment = json_decode($paymentResponse->getContent(), true);

                if (isset($payment['transfer-id'])) {
                    /* @var $payPay Pay */
                    $payPay = $this->createPay($purchaseOrderDescription, $dataNomina, $dataTotales, $payment['transfer-id']);
                    $purchaseOrderDescription->addPayPay($payPay);
                    $em->persist($payPay);
                }

                $purchaseOrder->addPurchaseOrderDescription($purchaseOrderDescription);
                $em->persist($purchaseOrderDescription);
                $em->persist($purchaseOrder);

                if (in_array($paymentResponse->getStatusCode(), array(Response::HTTP_CREATED, Response::HTTP_ACCEPTED, Response::HTTP_OK))) {
                    $response = true;
                } else {
                    $response = false;
                }
            }
        }

        $em->flush();
        return $response;
    }

    private function createPay(PurchaseOrdersDescription $purchaseOrderDescription, $dataNomina, $dataTotales, $transfer_id)
    {
        $pago = new Pay();
        $pago->setIdDispercionNovo($transfer_id);
        $pago->setMessage("");
        $pago->setPayMethodPayMethod($dataNomina['payMethod']);
        $pago->setPurchaseOrdersDescription($purchaseOrderDescription);
        $pago->setStatus('Pendiente');
        $pago->setUserIdUser($this->getUser());

        $purchaseOrdersStatusRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
        /* @var $purchaseOrdersStatus PurchaseOrdersStatus */
        $purchaseOrdersStatus = $purchaseOrdersStatusRepo->findOneBy(array('idNovoPay' => 'S1'));
        $pago->setPurchaseOrdersStatusPurchaseOrdersStatus($purchaseOrdersStatus);

        return $pago;
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

}
