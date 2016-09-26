<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractDocumentStatusType
 *
 * @ORM\Table(name="contract_document_status_type")
 * @ORM\Entity
 */
class ContractDocumentStatusType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_contract_document_status_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idContractDocumentStatusType;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=4, nullable=TRUE)
     */
    private $contractDocumentStatusCode;

    /**
     * Set idContractDocumentStatusType
     *
     * @param integer $id
     *
     * @return ContractDocumentStatusType
     */
    public function setIdContractDocumentStatusType($id)
    {
        $this->idContractDocumentStatusType = $id;

        return $this;
    }

    /**
     * Get idContractDocumentStatusType
     *
     * @return integer
     */
    public function getIdContractDocumentStatusType()
    {
        return $this->idContractDocumentStatusType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ContractDocumentStatusType
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
     * Set contractDocumentStatusCode
     *
     * @param string $contractDocumentStatusCode
     *
     * @return ContractDocumentStatusType
     */
    public function setContractDocumentStatusCode($contractDocumentStatusCode)
    {
        $this->contractDocumentStatusCode = $contractDocumentStatusCode;

        return $this;
    }

    /**
     * Get contractDocumentStatusCode
     *
     * @return string
     */
    public function getContractDocumentStatusCode()
    {
        return $this->contractDocumentStatusCode;
    }
}
