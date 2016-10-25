<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * DocumentType
 *
 * @ORM\Table(name="document_type",
 *      uniqueConstraints={@UniqueConstraint(name="uniqueCode", columns={"doc_code"})},
 * )
 * @ORM\Entity
 * 
 */
class DocumentType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_document_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idDocumentType;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;
    
    /**
     * @ORM\Column(name="doc_code",type="string", length=10, nullable=TRUE)
     */
    private $docCode;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $refPdf;

    /**
     * Set idDocumentType
     *
     * @param integer $idDocumentType
     *
     * @return DocumentType
     */
    public function setIdDocumentType($idDocumentType)
    {
        $this->idDocumentType = $idDocumentType;
        return $this;
    }

    /**
     * Get idDocumentType
     *
     * @return integer
     */
    public function getIdDocumentType()
    {
        return $this->idDocumentType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return DocumentType
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
    public function __toString()
    {
        return (string) $this->name;
    }


    /**
     * Set refPdf
     *
     * @param string $refPdf
     *
     * @return DocumentType
     */
    public function setRefPdf($refPdf)
    {
        $this->refPdf = $refPdf;

        return $this;
    }

    /**
     * Get refPdf
     *
     * @return string
     */
    public function getRefPdf()
    {
        return $this->refPdf;
    }

    /**
     * Set docCode
     *
     * @param string $docCode
     *
     * @return DocumentType
     */
    public function setDocCode($docCode)
    {
        $this->docCode = $docCode;

        return $this;
    }

    /**
     * Get docCode
     *
     * @return string
     */
    public function getDocCode()
    {
        return $this->docCode;
    }
}
