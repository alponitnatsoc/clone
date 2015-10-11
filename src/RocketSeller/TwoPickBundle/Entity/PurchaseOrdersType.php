<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrdersType
 *
 * @ORM\Table(name="purchase_orders_type")
 * @ORM\Entity
 */
class PurchaseOrdersType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_purchase_orders_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPurchaseOrdersType;



    /**
     * Get idPurchaseOrdersType
     *
     * @return integer
     */
    public function getIdPurchaseOrdersType()
    {
        return $this->idPurchaseOrdersType;
    }
}
