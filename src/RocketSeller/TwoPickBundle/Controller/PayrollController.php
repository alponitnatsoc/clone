<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\NoveltyType;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class PayrollController extends Controller
{

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
    public function createPayrollToContract($idContract, $deleteActivePayroll = false, $period = null, $month = null, $year = null)
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
                    unset($dataNomina[$key]);
                    return array(
                        'dataNomina' => $dataNomina,
                        'salary' => $value['NOMI_VALOR_LOCAL']
                    );
                }
            }
        }
        return false;
    }

    private function getAportes($dataNomina)
    {
        if ($dataNomina && !empty($dataNomina)) {
            $aporteSalud = $aportePension = 0;
            foreach ($dataNomina as $key => $value) {
                if (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '3010') {
                    unset($dataNomina[$key]);
                    $aporteSalud = $value['NOMI_VALOR_LOCAL'];
                } elseif (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '3020') {
                    unset($dataNomina[$key]);
                    $aportePension = $value["NOMI_VALOR_LOCAL"];
                }
            }
            return array(
                'dataNomina' => $dataNomina,
                'aporteSalud' => $aporteSalud,
                'aportePension' => $aportePension
            );
        }
        return false;
    }

    private function getData($payrollToPay = false)
    {
        $user = $this->getUser();
        $employerHasEmployees = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        $salaries = array();
        $payrolls = array();
        $aportes = array();
        $dataNomina = array();
        $novelties = array();
        $employeesData = array();
        $payMethod = array();

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
                            $employeesData[$payroll->getIdPayroll()] = $employerHasEmployee;
                            $payMethod[$payroll->getIdPayroll()] = $contract->getPayMethodPayMethod();
                            $purchaseOrdersDescription = $payroll->getPurchaseOrdersDescription();
                            $purchaseOrdersStatus = false;
                            if ($purchaseOrdersDescription) {
                                $purchaseOrdersStatus = $purchaseOrdersDescription->getPurchaseOrdersStatus();
                            }
                            if ((!$purchaseOrdersDescription || empty($purchaseOrdersDescription) || $purchaseOrdersDescription === null)) {
                                if ((!$purchaseOrdersStatus || empty($purchaseOrdersStatus) || $purchaseOrdersStatus === null)) {
                                    $payrolls[$payroll->getIdPayroll()] = $payroll;
                                    $dataNomina[$payroll->getIdPayroll()] = $this->getInfoNominaSQL($employerHasEmployee);
                                    $salary = $this->getSalary($dataNomina[$payroll->getIdPayroll()]);
                                    $salaries[$payroll->getIdPayroll()] = 0;
                                    if ($salary) {
                                        $salaries[$payroll->getIdPayroll()] = $salary['salary'];
                                        $dataNomina[$payroll->getIdPayroll()] = $salary['dataNomina'];
                                    }
                                    $aporte = $this->getAportes($dataNomina[$payroll->getIdPayroll()]);
                                    $aportes[$payroll->getIdPayroll()] = array();
                                    if ($aporte) {
                                        $aportes[$payroll->getIdPayroll()]['aporteSalud'] = $aporte['aporteSalud'];
                                        $aportes[$payroll->getIdPayroll()]['aportePension'] = $aporte['aportePension'];
                                        $novelties[$payroll->getIdPayroll()] = $aporte['dataNomina'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return array(
            "employerHasEmployees" => $employeesData,
            "salaries" => $salaries,
            "aportes" => $aportes,
            "payrolls" => $payrolls,
            "novelties" => $novelties,
            "payMethod" => $payMethod
        );
    }

    public function payAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $data = $this->getData();

        return $this->render('RocketSellerTwoPickBundle:Payroll:pay.html.twig', array(
                    "employerHasEmployees" => $data['employerHasEmployees'],
                    "salaries" => $data['salaries'],
                    "aportes" => $data['aportes'],
                    "payrolls" => $data['payrolls'],
                    "novelties" => $data['novelties']
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

            return $this->render('RocketSellerTwoPickBundle:Payroll:calculate.html.twig', array(
                        "employerHasEmployees" => $data['employerHasEmployees'],
                        "salaries" => $data['salaries'],
                        "aportes" => $data['aportes'],
                        "payrolls" => $data['payrolls'],
                        "payMethod" => $data['payMethod'],
            ));
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

            $total = 0;
            foreach ($data['salaries'] as $key => $salario) {
                $total += (int) $salario;
                $total += ($data['aportes'][$key]['aporteSalud'] + $data['aportes'][$key]['aportePension']);
            }
            //$this->createPurchaseOrder($data['payrolls'], $total);

            return $this->render('RocketSellerTwoPickBundle:Payroll:confirm.html.twig', array(
                        "employerHasEmployees" => $data['employerHasEmployees'],
                        "salaries" => $data['salaries'],
                        "aportes" => $data['aportes'],
                        "payrolls" => $data['payrolls'],
                        "payMethod" => $data['payMethod'],
                        "total" => $total
            ));
        } else {
            return $this->redirectToRoute("payroll");
        }
    }

    private function createPurchaseOrder($payrolls, $total)
    {

        $em = $this->getDoctrine()->getManager();

        $purchaseOrder = new PurchaseOrders();

        $purchaseOrdersType = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersType');
        $purchaseOrdersType = $purchaseOrdersType->findOneBy(array('name' => 'Pago nomina'));
        $purchaseOrder->setPurchaseOrdersTypePurchaseOrdersType($purchaseOrdersType);

        $payroll = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Payroll');
        $payroll = $payroll->findBy(array('contractContract' => $contract->getIdContract()))[0];
        $purchaseOrder->setPayrollPayroll($payroll);

        $purchaseOrdersStatus = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
        $purchaseOrdersStatus = $purchaseOrdersStatus->findBy(array('name' => 'Pagada'))[0];
        $purchaseOrder->setPurchaseOrdersStatusPurchaseOrdersStatus($purchaseOrdersStatus);


        $dateCreated = date("Y-m-d H:i:s");
        $purchaseOrder->setDateCreated(new \DateTime($dateCreated));
        $dateModified = date("Y-m-d H:i:s");
        $purchaseOrder->setDateModified(new \DateTime($dateModified));
        $idUser = $this->getUser();
        $purchaseOrder->setIdUser($idUser);
        $purchaseOrder->setName('Pago Nomina');
        $purchaseOrder->setValue($subTotal);

        $em->persist($purchaseOrder);
        $em->flush();

        return true;
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
    }

}
