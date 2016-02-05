<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PayrollController extends Controller
{

    public function payAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        //if($this->isGranted('PAYROLL', $this->getUser())) {
        $user = $this->getUser();
        $employeesData = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        $salaries = array();
        $payrolls = array();
        $novelties = array();
        $aportes = array();
        foreach ($employeesData as $employerHasEmployee) {
            if ($employerHasEmployee->getState() == 'Active') {
                $contracts = $employerHasEmployee->getContracts();
                $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = 0;
                foreach ($contracts as $contract) {
                    if ($contract->getState() == 'Active') {
                        $repository = $this->getDoctrine()
                                ->getRepository('RocketSellerTwoPickBundle:Payroll');
                        $query = $repository->createQueryBuilder('p');
                        $query->andWhere('p.contractContract = :contract')
                                ->setParameter('contract', $contract->getIdContract());
                        $payroll = $query->getQuery()->setMaxResults(1)->getOneOrNullResult();

                        $novelties[$employerHasEmployee->getIdEmployerHasEmployee()] = array();

                        if (!$payroll) {
                            $payroll = new Payroll();
                            $payroll->setContractContract($contract);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($payroll);
                            $em->flush();
                        } else {
                            foreach ($payroll->getPayrollDetails() as $detail) {
                                $repository = $this->getDoctrine()
                                        ->getRepository('RocketSellerTwoPickBundle:Novelty');
                                $query = $repository->createQueryBuilder('n');
                                $query->andWhere('n.payrollDetailPayrollDetail = :payroll')
                                        ->setParameter('payroll', $detail->getIdPayrollDetail());
                                $novelties[$employerHasEmployee->getIdEmployerHasEmployee()][] = $query->getQuery()->getResult()[0];
                            }
                        }
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
                        $payrolls[$employerHasEmployee->getIdEmployerHasEmployee()] = $payroll;
                        $aportes[$employerHasEmployee->getIdEmployerHasEmployee()] = $contract->getSalary() * 0.04;
                    }
                }
            }
        }

        return $this->render('RocketSellerTwoPickBundle:Payroll:pay.html.twig', array(
                    "employees" => $employeesData,
                    "salaries" => $salaries,
                    "aportes" => $aportes,
                    "payrolls" => $payrolls,
                    "novelties" => $novelties
        ));
        //} else {
        //    throw $this->createAccessDeniedException("No tiene suficientes permisos");
        // }
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
                if ($employerHasEmployee->getState() == 'Active') {
                    $contracts = $employerHasEmployee->getContracts();
                    $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = 0;
                    foreach ($contracts as $contract) {
                        if ($contract->getState() == 'Active') {
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
                if ($employerHasEmployee->getState() == 'Active') {
                    $contracts = $employerHasEmployee->getContracts();
                    $salaries[$employerHasEmployee->getIdEmployerHasEmployee()] = 0;
                    foreach ($contracts as $contract) {
                        if ($contract->getState() == 'Active') {
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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('RocketSellerTwoPickBundle:Payroll:comprobante.html.twig', array());
    }

}
