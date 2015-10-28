<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrders
 *
 * @ORM\Table(name="purchase_orders", indexes={@ORM\Index(name="fk_purchase_orders_purchase_orders_type1", columns={"purchase_orders_type_id_purchase_orders_type"}), @ORM\Index(name="fk_purchase_orders_payroll1", columns={"payroll_id_payroll"})})
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
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_type_id_purchase_orders_type", referencedColumnName="id_purchase_orders_type")
     * })
     */
    private $purchaseOrdersTypePurchaseOrdersType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Payroll
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll", inversedBy="purchaseOrders")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payroll_id_payroll", referencedColumnName="id_payroll")
     * })
     */
    private $payrollPayroll;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_status_id_purchase_orders_status", referencedColumnName="id_purchase_orders_status")
     * })
     */
    private $purchaseOrdersStatusPurchaseOrdersStatus;

    /**
     * @ORM\OneToMany(targetEntity="PurchaseOrdersDescription", mappedBy="purchaseOrdersPurchaseOrders", cascade={"persist"})
     */
    private $purchaseOrderDescriptions;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User")
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
     * @ORM\Column(name="dateModified", type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    private $date_modified;

    /**
     * Set idPurchaseOrders
     *
     * @param integer $idPurchaseOrders
     *
     * @return PurchaseOrders
     */
    public function setIdPurchaseOrders($idPurchaseOrders)
    {
        $this->idPurchaseOrders = $idPurchaseOrders;

        return $this;
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
     * Set purchaseOrdersTypePurchaseOrdersType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType $purchaseOrdersTypePurchaseOrdersType
     *
     * @return PurchaseOrders
     */
    public function setPurchaseOrdersTypePurchaseOrdersType(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType $purchaseOrdersTypePurchaseOrdersType)
    {
        $this->purchaseOrdersTypePurchaseOrdersType = $purchaseOrdersTypePurchaseOrdersType;

        return $this;
    }

    /**
     * Get purchaseOrdersTypePurchaseOrdersType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType
     */
    public function getPurchaseOrdersTypePurchaseOrdersType()
    {
        return $this->purchaseOrdersTypePurchaseOrdersType;
    }

    /**
     * Set payrollPayroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll
     *
     * @return PurchaseOrders
     */
    public function setPayrollPayroll(\RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll)
    {
        $this->payrollPayroll = $payrollPayroll;

        return $this;
    }

    /**
     * Get payrollPayroll
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Payroll
     */
    public function getPayrollPayroll()
    {
        return $this->payrollPayroll;
    }

    /**
     * Set purchaseOrdersTypePurchaseOrdersStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersTypePurchaseOrdersStatus
     *
     * @return PurchaseOrders
     */
    public function setPurchaseOrdersTypePurchaseOrdersStatus(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersTypePurchaseOrdersStatus = null)
    {
        $this->purchaseOrdersTypePurchaseOrdersStatus = $purchaseOrdersTypePurchaseOrdersStatus;

        return $this;
    }

    /**
     * Get purchaseOrdersTypePurchaseOrdersStatus
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     */
    public function getPurchaseOrdersTypePurchaseOrdersStatus()
    {
        return $this->purchaseOrdersTypePurchaseOrdersStatus;
    }

    /**
     * Set purchaseOrdersStatusPurchaseOrdersStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatusPurchaseOrdersStatus
     *
     * @return PurchaseOrders
     */
    public function setPurchaseOrdersStatusPurchaseOrdersStatus(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatusPurchaseOrdersStatus = null)
    {
        $this->purchaseOrdersStatusPurchaseOrdersStatus = $purchaseOrdersStatusPurchaseOrdersStatus;

        return $this;
    }

    /**
     * Get purchaseOrdersStatusPurchaseOrdersStatus
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     */
    public function getPurchaseOrdersStatusPurchaseOrdersStatus()
    {
        return $this->purchaseOrdersStatusPurchaseOrdersStatus;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->purchaseOrderDescriptions = new \Doctrine\Common\Collections\ArrayCollection();
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
}
