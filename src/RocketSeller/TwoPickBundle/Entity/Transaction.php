<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransactionState
 *
 * @ORM\Table(name="transaction")
 * @ORM\Entity
 */
class Transaction
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_transaction", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idTransaction;
	
		/**
		 * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
		 * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus")
		 * @ORM\JoinColumns({
		 *   @ORM\JoinColumn(name="purchase_orders_status_id_purchase_orders_status", referencedColumnName="id_purchase_orders_status")
		 * })
		 */
		private $purchaseOrdersStatus;
	
		/**
		 * @ORM\Column(type="string", length=20, nullable=TRUE)
		 */
		private $radicatedNumber;
	
		/** @var TransactionState
		 * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\TransactionState", mappedBy="originTransaction")
		 * @ORM\JoinColumns({
		 *     @ORM\JoinColumn(name="transaction_id_transaction_state",referencedColumnName="id_transaction_state")
		 * })
		 */
		private $transactionState;
	
		/** @var TransactionType
		 * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\TransactionType")
		 * @ORM\JoinColumns({
		 *     @ORM\JoinColumn(name="transaction_id_transaction_type",referencedColumnName="id_transaction_type")
		 * })
		 */
		private $transactionType;
	
		/**
		 * @ORM\ManyToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\Employer", mappedBy="transactions")
		 */
		private $employers;

    /**
     * Get idTransaction
     *
     * @return integer
     */
    public function getIdTransaction()
    {
        return $this->idTransaction;
    }

    /**
     * Set radicatedNumber
     *
     * @param string $radicatedNumber
     *
     * @return Transaction
     */
    public function setRadicatedNumber($radicatedNumber)
    {
        $this->radicatedNumber = $radicatedNumber;

        return $this;
    }

    /**
     * Get radicatedNumber
     *
     * @return string
     */
    public function getRadicatedNumber()
    {
        return $this->radicatedNumber;
    }

    /**
     * Set purchaseOrdersStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatus
     *
     * @return Transaction
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
     * Set transactionState
     *
     * @param \RocketSeller\TwoPickBundle\Entity\TransactionState $transactionState
     *
     * @return Transaction
     */
    public function setTransactionState(\RocketSeller\TwoPickBundle\Entity\TransactionState $transactionState = null)
    {
        $this->transactionState = $transactionState;

        return $this;
    }

    /**
     * Get transactionState
     *
     * @return \RocketSeller\TwoPickBundle\Entity\TransactionState
     */
    public function getTransactionState()
    {
        return $this->transactionState;
    }

    /**
     * Set transactionType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\TransactionType $transactionType
     *
     * @return Transaction
     */
    public function setTransactionType(\RocketSeller\TwoPickBundle\Entity\TransactionType $transactionType = null)
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * Get transactionType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\TransactionType
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->employers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add employer
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Employer $employer
     *
     * @return Transaction
     */
    public function addEmployer(\RocketSeller\TwoPickBundle\Entity\Employer $employer)
    {
        $this->employers[] = $employer;

        return $this;
    }

    /**
     * Remove employer
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Employer $employer
     */
    public function removeEmployer(\RocketSeller\TwoPickBundle\Entity\Employer $employer)
    {
        $this->employers->removeElement($employer);
    }

    /**
     * Get employers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployers()
    {
        return $this->employers;
    }
}
