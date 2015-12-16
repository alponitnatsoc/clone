<?php
namespace RocketSeller\TwoPickBundle\Traits;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\Liquidation;

trait GetTransactionDetailTrait
{
    protected function transactionDetail($type, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $details = array();

        switch ($type) {
            case "contract":

                $contractRepository = $em->getRepository("RocketSellerTwoPickBundle:Contract");
                /** @var Contract $contract */
                $contract = $contractRepository->findOneBy(
                    array(
                        "idContract" => $id
                    )
                );
                $details["benefics"] = $contract->getBenefits();
                $details["contractType"] = $contract->getContractTypeContractType();
                $details["document"] = $contract->getDocumentDocument();
                $details["employeeContractType"] = $contract->getEmployeeContractTypeEmployeeContractType();
                $details["payMethod"] = $contract->getPayMethodPayMethod();
                $details["payrolls"] = $contract->getPayrolls();
                $details["position"] = $contract->getPositionPosition();
                $details["salary"] = $contract->getSalary();
                $details["state"] = $contract->getState();
                $details["timeCommitment"] = $contract->getTimeCommitmentTimeCommitment();
                $details["workplaces"] = $contract->getWorkplaces();
                break;
            case "pay":
                $payRepository = $em->getRepository("RocketSellerTwoPickBundle:Pay");
                /** @var Pay $pay */
                $pay = $payRepository->findOneBy(
                    array(
                        "idPay" => $id
                    )
                );
                $details["purchaseOrder"] = $pay->getPurchaseOrdersPurchaseOrders();
                $details["payType"] = $pay->getPayTypePayType();
                $details["payMethod"] = $pay->getPayMethodPayMethod();
                break;
            case "liquidation":
                $liquidationRepository = $em->getRepository("RocketSellerTwoPickBundle:Liquidation");
                /** @var Liquidation $liquidation */
                $liquidation = $liquidationRepository->findOneBy(
                    array(
                        "id" => $id
                    )
                );
                $details = $liquidation;
                break;
            case "novelty":
                break;
            default:
                break;
        }

        return $details;
    }
}