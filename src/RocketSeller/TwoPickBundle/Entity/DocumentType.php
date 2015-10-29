<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentType
 *
 * @ORM\Table(name="document_type")
 * @ORM\Entity
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
}
