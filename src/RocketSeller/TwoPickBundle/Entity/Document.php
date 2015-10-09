<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Document
 *
 * @ORM\Table(name="document", indexes={@ORM\Index(name="fk_Document_document_type1", columns={"document_type_id_document_type"}), @ORM\Index(name="fk_document_contract1", columns={"contract_id_contract"}), @ORM\Index(name="fk_document_person1", columns={"person_id_person"})})
 * @ORM\Entity
 */
class Document
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_document", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idDocument;

    /**
     * @var \AppBundle\Entity\Person
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;

    /**
     * @var \AppBundle\Entity\Contract
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Contract")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_id_contract", referencedColumnName="id_contract")
     * })
     */
    private $contractContract;

    /**
     * @var \AppBundle\Entity\DocumentType
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\DocumentType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document_type_id_document_type", referencedColumnName="id_document_type")
     * })
     */
    private $documentTypeDocumentType;



    /**
     * Set idDocument
     *
     * @param integer $idDocument
     *
     * @return Document
     */
    public function setIdDocument($idDocument)
    {
        $this->idDocument = $idDocument;

        return $this;
    }

    /**
     * Get idDocument
     *
     * @return integer
     */
    public function getIdDocument()
    {
        return $this->idDocument;
    }

    /**
     * Set personPerson
     *
     * @param \AppBundle\Entity\Person $personPerson
     *
     * @return Document
     */
    public function setPersonPerson(\AppBundle\Entity\Person $personPerson)
    {
        $this->personPerson = $personPerson;

        return $this;
    }

    /**
     * Get personPerson
     *
     * @return \AppBundle\Entity\Person
     */
    public function getPersonPerson()
    {
        return $this->personPerson;
    }

    /**
     * Set contractContract
     *
     * @param \AppBundle\Entity\Contract $contractContract
     *
     * @return Document
     */
    public function setContractContract(\AppBundle\Entity\Contract $contractContract)
    {
        $this->contractContract = $contractContract;

        return $this;
    }

    /**
     * Get contractContract
     *
     * @return \AppBundle\Entity\Contract
     */
    public function getContractContract()
    {
        return $this->contractContract;
    }

    /**
     * Set documentTypeDocumentType
     *
     * @param \AppBundle\Entity\DocumentType $documentTypeDocumentType
     *
     * @return Document
     */
    public function setDocumentTypeDocumentType(\AppBundle\Entity\DocumentType $documentTypeDocumentType)
    {
        $this->documentTypeDocumentType = $documentTypeDocumentType;

        return $this;
    }

    /**
     * Get documentTypeDocumentType
     *
     * @return \AppBundle\Entity\DocumentType
     */
    public function getDocumentTypeDocumentType()
    {
        return $this->documentTypeDocumentType;
    }
}
