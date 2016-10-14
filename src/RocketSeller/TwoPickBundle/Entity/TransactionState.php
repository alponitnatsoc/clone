<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransactionState
 *
 * @ORM\Table(name="transaction_state")
 * @ORM\Entity
 */
class TransactionState
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_transaction_state", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idTransactionState;
	
		/** @var Document
		 * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document", cascade={"persist"})
		 * @ORM\JoinColumns({
		 *     @ORM\JoinColumn(name="transaction_state_id_document",referencedColumnName="id_document")
		 *
		 * })
		 */
		private $document;
	
		/**
		 * @ORM\Column(type="text", nullable=TRUE)
		 */
		private $log;
	
		/** @var Transaction
		 * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Transaction", inversedBy="transactionState")
		 * @ORM\JoinColumns({
		 *     @ORM\JoinColumn(name="transaction_state_id_transaction",referencedColumnName="id_transaction")
		 * })
		 */
		private $originTransaction;

    /**
     * Get idTransactionState
     *
     * @return integer
     */
    public function getIdTransactionState()
    {
        return $this->idTransactionState;
    }

    /**
     * Set log
     *
     * @param string $log
     *
     * @return TransactionState
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set document
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $document
     *
     * @return TransactionState
     */
    public function setDocument(\RocketSeller\TwoPickBundle\Entity\Document $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set originTransaction
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Transaction $originTransaction
     *
     * @return TransactionState
     */
    public function setOriginTransaction(\RocketSeller\TwoPickBundle\Entity\Transaction $originTransaction = null)
    {
        $this->originTransaction = $originTransaction;

        return $this;
    }

    /**
     * Get originTransaction
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Transaction
     */
    public function getOriginTransaction()
    {
        return $this->originTransaction;
    }
}
