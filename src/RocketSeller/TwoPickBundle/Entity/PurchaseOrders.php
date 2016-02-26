<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrders
 *
 * @ORM\Table(name="purchase_orders", indexes={})
 * @ORM\Entity(repositoryClass="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersRepository")
 */
class PurchaseOrders
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_purchase_orders", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPurchaseOrders;
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable = TRUE)
     */
    private $payMethodId;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_status_id_purchase_orders_status", referencedColumnName="id_purchase_orders_status")
     * })
     */
    private $purchaseOrdersStatus;

    /**
     * @ORM\OneToMany(targetEntity="PurchaseOrdersDescription", mappedBy="purchaseOrders", cascade={"persist"})
     */
    private $purchaseOrderDescriptions;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="purchaseOrders")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     */
    private $idUser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    private $date_created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    private $date_modified;

    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    private $invoice_number;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;

    /**
     * @ORM\Column(type="float", length=100, nullable=TRUE)
     */
    private $value;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->purchaseOrderDescriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->date_created = new \DateTime();
        $this->date_modified = new \DateTime();
    }

    /**
     * Get idPurchaseOrders
     *
     * @return integer
     */
    public function getIdPurchaseOrders()
    {
        return $this->idPurchaseOrders;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return PurchaseOrders
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set dateModified
     *
     * @param \DateTime $dateModified
     *
     * @return PurchaseOrders
     */
    public function setDateModified($dateModified)
    {
        $this->date_modified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->date_modified;
    }

    /**
     * Set invoiceNumber
     *
     * @param string $invoiceNumber
     *
     * @return PurchaseOrders
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoice_number = $invoiceNumber;

        return $this;
    }

    /**
     * Get invoiceNumber
     *
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return PurchaseOrders
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
     * Set value
     *
     * @param float $value
     *
     * @return PurchaseOrders
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set purchaseOrdersStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatus
     *
     * @return PurchaseOrders
     */
    public function setPurchaseOrdersStatus(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatus = null)
    {
        $this->purchaseOrdersStatus = $purchaseOrdersStatus;

        return $this;
    }

    /**
     * Get purchaseOrdersStatus
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     */
    public function getPurchaseOrdersStatus()
    {
        return $this->purchaseOrdersStatus;
    }

    /**
     * Add purchaseOrderDescription
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrderDescription
     *
     * @return PurchaseOrders
     */
    public function addPurchaseOrderDescription(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrderDescription)
    {
        $this->purchaseOrderDescriptions[] = $purchaseOrderDescription;

        return $this;
    }

    /**
     * Remove purchaseOrderDescription
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrderDescription
     */
    public function removePurchaseOrderDescription(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrderDescription)
    {
        $this->purchaseOrderDescriptions->removeElement($purchaseOrderDescription);
    }

    /**
     * Get purchaseOrderDescriptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPurchaseOrderDescriptions()
    {
        return $this->purchaseOrderDescriptions;
    }

    /**
     * Set idUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $idUser
     *
     * @return PurchaseOrders
     */
    public function setIdUser(\RocketSeller\TwoPickBundle\Entity\User $idUser = null)
    {
        $this->idUser = $idUser;

        return $this;
    }

    /**
     * Get idUser
     *
     * @return \RocketSeller\TwoPickBundle\Entity\User
     */
    public function getIdUser()
    {
        return $this->idUser;
    }


    /**
     * Set payMethodId
     *
     * @param integer $payMethodId
     *
     * @return PurchaseOrders
     */
    public function setPayMethodId($payMethodId)
    {
        $this->payMethodId = $payMethodId;

        return $this;
    }

    /**
     * Get payMethodId
     *
     * @return integer
     */
    public function getPayMethodId()
    {
        return $this->payMethodId;
    }
}
