<?php

namespace RocketSeller\TwoPickBundle\Traits;


trait NotificationMethodsTrait
{

    /**
     * @param integer $id - Id de liquidationReason
     * @return $liquidationReason
     */
    protected function notificationByPersonLiquidation($idLiq, $idPerson)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Notification');
        $notification = $repository->findOneBy(array(
            "personPerson" => $idPerson,
            "status" => 1,
            "liquidation" => $idLiq
        ));

        return $notification;
    }
}