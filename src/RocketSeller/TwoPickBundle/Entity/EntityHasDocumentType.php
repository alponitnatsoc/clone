<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityHasDocumentType
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class EntityHasDocumentType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Entity
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Entity", inversedBy="entityHasDocumentType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id_entity", referencedColumnName="id_entity")
     * })
     */
    private $entityEntity;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\DocumentType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\DocumentType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document_type_id_document_type", referencedColumnName="id_document_type")
     * })
     */
    private $documentTypeDocumentType;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set entityEntity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Entity $entityEntity
     *
     * @return EntityHasDocumentType
     */
    public function setEntityEntity(\RocketSeller\TwoPickBundle\Entity\Entity $entityEntity = null)
    {
        $this->entityEntity = $entityEntity;

        return $this;
    }

    /**
     * Get entityEntity
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Entity
     */
    public function getEntityEntity()
    {
        return $this->entityEntity;
    }

    /**
     * Set documentTypeDocumentType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\DocumentType $documentTypeDocumentType
     *
     * @return EntityHasDocumentType
     */
    public function setDocumentTypeDocumentType(\RocketSeller\TwoPickBundle\Entity\DocumentType $documentTypeDocumentType = null)
    {
        $this->documentTypeDocumentType = $documentTypeDocumentType;

        return $this;
    }

    /**
     * Get documentTypeDocumentType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\DocumentType
     */
    public function getDocumentTypeDocumentType()
    {
        return $this->documentTypeDocumentType;
    }
}
