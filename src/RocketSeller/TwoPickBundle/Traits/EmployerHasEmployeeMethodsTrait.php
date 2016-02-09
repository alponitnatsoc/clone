<?php
namespace RocketSeller\TwoPickBundle\Traits;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\Pay;

trait EmployerHasEmployeeMethodsTrait
{
    protected function showContracts($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $repository->find($id);
        $contracts = $employerHasEmployee->getContracts();

        return $contracts;
    }

    protected function showLiquidations($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $repository->find($id);
        $liquidations = $employerHasEmployee->getLiquidations();

        return $liquidations;
    }

    protected function showPayments($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $repository->find($id);
        $contracts = $this->showContracts($id);
        /** @var Contract $contract */
        foreach($contracts as $contract) {
            if ($contract->getState() == "Active") {
                $payrolls = $contract->getPayrolls()->getValues();
                break;
            }
        }

        $pagosRecibidos = array();
        /** @var Payroll $payroll */
        foreach($payrolls as $payroll) {
            $purchaseOrders = $payroll->getPurchaseOrders()->getValues();
            /** @var PurchaseOrders $po */
            foreach($purchaseOrders as $key => $po) {
                if ($po->getPurchaseOrdersStatusPurchaseOrdersStatus()->getIdPurchaseOrdersStatus() == 1) {
                    $pagosRecibidos[$key]["dateCreated"] = $po->getDateCreated();
                    $pagosRecibidos[$key]["dateModified"] = $po->getDateModified();
                    $pagosRecibidos[$key]["valor"] = $po->getValue();
                    $idPO = $po->getIdPurchaseOrders();
                    $payRepository = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Pay");
                    /** @var Pay $pay */
                    $pay = $payRepository->findOneBy(
                        array(
                            "purchaseOrdersPurchaseOrders" => $idPO
                        )
                    );
                    $pagosRecibidos[$key]["idPay"] = $pay->getIdPay();
                }
            }
        }

        return $pagosRecibidos;
    }

    protected function getEmployee($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $repository->find($id);
        $employee = $employerHasEmployee->getEmployeeEmployee();

        return $employee;
    }

    protected function getActiveContract($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $repository->find($id);

        $contract = $employerHasEmployee->getContractByState(1);

        return $contract;
    }
}