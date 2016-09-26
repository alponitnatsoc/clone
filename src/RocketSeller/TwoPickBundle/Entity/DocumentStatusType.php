<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * DocumentStatusType
 *
 * @ORM\Table(name="document_status_type",
 *      uniqueConstraints={@UniqueConstraint(name="code_unique", columns={"document_status_code"})}
 * )
 * @ORM\Entity
 */
class DocumentStatusType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_document_status_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idDocumentStatusType;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;

    /**
     * @ORM\Column(name="document_status_code", type="string", length=6, nullable=TRUE)
     */
    private $documentStatusCode;

    /**
     * Set idDocumentStatusType
     *
     * @param integer $id
     *
     * @return DocumentStatusType
     */
    public function setIdDocumentStatusType($id)
    {
        $this->idDocumentStatusType = $id;

        return $this;
    }

    /**
     * Get idDocumentStatusType
     *
     * @return integer
     */
    public function getIdDocumentStatusType()
    {
        return $this->idDocumentStatusType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return DocumentStatusType
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
     * Set documentStatusCode
     *
     * @param string $documentStatusCode
     *
     * @return DocumentStatusType
     */
    public function setDocumentStatusCode($documentStatusCode)
    {
        $this->documentStatusCode = $documentStatusCode;

        return $this;
    }

    /**
     * Get documentStatusCode
     *
     * @return string
     */
    public function getDocumentStatusCode()
    {
        return $this->documentStatusCode;
    }
}
