<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrdersStatus
 *
 * @ORM\Table(name="purchase_orders_status", indexes={@ORM\Index(name="fk_id_novo_pay", columns={"id_novo_pay"})})
 * @ORM\Entity
 *
 * Estados de las ordenes de compra:
 *      1 - Pagada ID: 1
 *      2 - Pendiente / Por pagar ID: 2
 *      3 - Cancelada / Rechazada ID: 3
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
     * @ORM\Column(name="id_novo_pay", type="string", length=100)
     */
    private $idNovoPay;

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

    /**
     * Set idNovoPay
     *
     * @param string $idNovoPay
     *
     * @return PurchaseOrdersStatus
     */
    public function setIdNovoPay($idNovoPay)
    {
        $this->idNovoPay = $idNovoPay;

        return $this;
    }

    /**
     * Get idNovoPay
     *
     * @return string
     */
    public function getIdNovoPay()
    {
        return $this->idNovoPay;
    }

}
