<?php
namespace RocketSeller\TwoPickBundle\Traits;

use RocketSeller\TwoPickBundle\Entity\Pay;

trait GetTransactionDetailTrait
{
    protected function transactionDetail($type, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $details = array();

        switch ($type) {
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
            default:
                break;
        }

        return $details;
    }
}