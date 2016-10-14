<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransactionType
 *
 * @ORM\Table(name="transaction_type")
 * @ORM\Entity
 *
 */
class TransactionType
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_transaction_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTransactionType;

    /**
     * @ORM\Column(name="code", type="string", length=100)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=TRUE)
     */
    private $description;
	

    /**
     * Get idTransactionType
     *
     * @return integer
     */
    public function getIdTransactionType()
    {
        return $this->idTransactionType;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return TransactionType
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return TransactionType
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
     * @return TransactionType
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
