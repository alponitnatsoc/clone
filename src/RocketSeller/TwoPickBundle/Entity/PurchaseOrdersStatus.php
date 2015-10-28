<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrdersStatus
 *
 * @ORM\Table(name="purchase_orders_status")
 * @ORM\Entity
 */
class PurchaseOrdersStatus
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_purchase_orders_status", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPurchaseOrdersStatus;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=TRUE)
     */
    private $description;

    /**
     * Get idPurchaseOrdersStatus
     *
     * @return integer
     */
    public function getIdPurchaseOrdersStatus()
    {
        return $this->idPurchaseOrdersStatus;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return PurchaseOrdersStatus
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return PurchaseOrdersStatus
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
