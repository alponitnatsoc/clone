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
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=TRUE)
     */
    private $description;


    /**
     * Get idPurchaseOrdersType
     *
     * @return integer
     */
    public function getIdPurchaseOrdersType()
    {
        return $this->idPurchaseOrdersType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return PurchaseOrdersType
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
     * @return PurchaseOrdersType
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
