<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\Contract;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class PayrollController extends Controller
{

    public function payTestAction($idContract)
    {
        $this->createPayrollToContract($idContract);
        exit;
    }

    /**
     * Crear Payroll para el contrato
     * 
     * @param String $idContract
     * @param Boolean $deleteOldPayroll para indicar que se eliminan los payroll asociados al contrato
     * @param String $period
     * @param String $month
     * @param String $year
     * @return Payroll
     * 
     */
    public function createPayrollToContract($idContract, $deleteOldPayroll = false, $period = null, $month = null, $year = null)
    {
        $contractRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract');
        /* @var $contract Contract */
        $contract = $contractRepo->findOneBy(array('idContract' => $idContract));
        if ($contract && !empty($contract) && $contract !== null && $contract->getState() == 1) {
            $em = $this->getDoctrine()->getManager();
            if ($deleteOldPayroll) {
                $payrolls = $contract->getPayrolls();
                $contract->setActivePayroll(null);
                if (count($payrolls) > 0) {
                    foreach ($payrolls as $key => $payroll) {
                        $payroll->setContractContract(null);
                        $em->persist($payroll);
                        $em->remove($payroll);
                        $payrolls->removeElement($payroll);
                        $em->flush();
                    }
                }
            }

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

            $payrollRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Payroll');
            if (!$deleteOldPayroll) {
                $payroll = $payrollRepo->findOneBy(array(
                    'contractContract' => $contract->getIdContract(),
                    'period' => $period,
                    'month' => $month,
                    'year' => $year
                ));
            }

            if (!$deleteOldPayroll && $payroll && !empty($payroll) && $payroll !== null) {
                $contract->setActivePayroll($payroll);
                $em->persist($contract);
                $em->flush();
            } else {
                $payroll = new Payroll();
                $payroll->setContractContract($contract);
                $payroll->setMonth($month);
                $payroll->setYear($year);
                $payroll->setPeriod($period);

                $em->persist($payroll);
                $em->flush();

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
     * Trae la informacion del empleado desde el ws de nomina
     * @param type $employerHasEmployee
     */
    private function getInfoNominaSQL($employerHasEmployee)
    {
        $employeeId = $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getDocument(); //parametro para el mock
        //$employeeId = $employerHasEmployee->getIdEmployerHasEmployee(); //parametro para el ws real

        $generalPayroll = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll', array(
            'employeeId' => $employeeId,
            'period' => null,
            'month' => null,
            'year' => null
                ), array('_format' => 'json')
        );

        $generalPayroll = json_decode($generalPayroll->getContent(), true);
        dump($generalPayroll);
        exit;
    }

    public function payAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $user = $this->getUser();
        $employeesData = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        $salaries = array();
        $payrolls = array();
        $novelties = array();
        $aportes = array();
        foreach ($employeesData as $employerHasEmployee) {
            if ($employerHasEmployee->getState() == 1) {
                $dataNomina = $this->getInfoNominaSQL($employerHasEmployee);
            }
        }

        return $this->render('RocketSellerTwoPickBundle:Payroll:pay.html.twig', array(
                    "employees" => $employeesData,
                    "salaries" => $salaries,
                    "aportes" => $aportes,
                    "payrolls" => $payrolls,
                    "novelties" => $novelties
        ));
    }

    public function calculateAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            $employees = $request->request->get('employee');
            $payrolls = $request->request->get('payroll');
            $user = $this->getUser();
            $employer = $user->getPersonPerson()->getEmployer();
            $repository = $this->getDoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
            $query = $repository->createQueryBuilder('e');
            $query->andWhere('e.idEmployerHasEmployee IN (:ids)')
                    ->setParameter('ids', $employees)
                    ->andWhere('e.employerEmployer = :employer')
                    ->setParameter('employer', $employer->getIdEmployer());
            $employeesData = $query->getQuery()->getResult();
            $salaries = $aportes = $payMethod = array();
            $total = 0;
            foreach ($employeesData as $employerHasEmployee) {
                if ($employerHasEmployee->getState() == 1) {
                    $contracts = $employerHasEmployee->getContracts();
                    $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = 0;
                    foreach ($contracts as $contract) {
                        if ($contract->getState() == 1) {
                            $aportes[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary() * 0.04;
                            $payMethod[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getPayMethodPayMethod();
                            $frecuecia = $contract->getPayMethodPayMethod()->getFrequencyFrequency()->getPayrollCode();
                            if ($frecuecia == 'J') {
                                $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary() / 30;
                            } elseif ($frecuecia == 'M') {
                                $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary() / 1;
                            } elseif ($frecuecia == 'Q') {
                                $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary() / 2;
                            } else {
                                $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary();
                            }
                        }
                        $total += $salaries[$employerHasEmployee->getIdEmployerHasEmployee()];
                    }
                }
            }
            return $this->render('RocketSellerTwoPickBundle:Payroll:calculate.html.twig', array(
                        "employees" => $employeesData,
                        "salaries" => $salaries,
                        "aportes" => $aportes,
                        "payMethod" => $payMethod,
                        "payrolls" => $payrolls,
                        "total" => $total
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
            $employees = $request->request->get('employee');
            $payrolls = $request->request->get('payroll');
            $user = $this->getUser();
            $employer = $user->getPersonPerson()->getEmployer();
            $repository = $this->getDoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
            $query = $repository->createQueryBuilder('e');
            $query->andWhere('e.idEmployerHasEmployee IN (:ids)')
                    ->setParameter('ids', $employees)
                    ->andWhere('e.employerEmployer = :employer')
                    ->setParameter('employer', $employer->getIdEmployer());
            $employeesData = $query->getQuery()->getResult();
            $salaries = $aportes = $payMethod = array();
            $total = 0;
            foreach ($employeesData as $employerHasEmployee) {
                if ($employerHasEmployee->getState() == 1) {
                    $contracts = $employerHasEmployee->getContracts();
                    $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = 0;
                    foreach ($contracts as $contract) {
                        if ($contract->getState() == 1) {
                            $aportes[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary() * 0.04;
                            $payMethod[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getPayMethodPayMethod();
                            $frecuecia = $contract->getPayMethodPayMethod()->getFrequencyFrequency()->getPayrollCode();
                            if ($frecuecia == 'J') {
                                $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary() / 30;
                            } elseif ($frecuecia == 'M') {
                                $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary() / 1;
                            } elseif ($frecuecia == 'Q') {
                                $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary() / 2;
                            } else {
                                $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary();
                            }
                            $subTotal = $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] + $aportes[$employerHasEmployee->getIdEmployerHasEmployee()];
                            $total += $subTotal;
                            $this->createPurchaseOrder($employerHasEmployee, $contract, $subTotal);
                        }
                    }
                }
            }
            return $this->render('RocketSellerTwoPickBundle:Payroll:confirm.html.twig', array(
                        "employees" => $employeesData,
                        "salaries" => $salaries,
                        "aportes" => $aportes,
                        "payMethod" => $payMethod,
                        "payrolls" => $payrolls,
                        "total" => $total
            ));
        } else {
            return $this->redirectToRoute("payroll");
        }
    }

    public function createPurchaseOrder($employerHasEmployee, $contract, $subTotal)
    {

        $em = $this->getDoctrine()->getManager();

        $purchaseOrder = new PurchaseOrders();

        $purchaseOrdersType = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersType');
        $purchaseOrdersType = $purchaseOrdersType->findBy(array('name' => 'Pago nomina'))[0];
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
