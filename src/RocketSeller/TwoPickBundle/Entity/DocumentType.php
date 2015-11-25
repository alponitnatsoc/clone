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
     * @var \Application\Sonata\MediaBundle\Entity\Media
     * @ORM\OneToMany(targetEntity="Application\Sonata\MediaBundle\Entity\Media",mappedBy="documentType", cascade={"persist"}, fetch="LAZY")
     */
    protected $media;

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

    /**
     * Set media
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $media
     *
     * @return DocumentType
     */
    public function setMedia(\Application\Sonata\MediaBundle\Entity\Media $media = null)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return \Application\Sonata\MediaBundle\Entity\Media
     */
    public function getMedia()
    {
        return $this->media;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->media = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add medium
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $medium
     *
     * @return DocumentType
     */
    public function addMedia(\Application\Sonata\MediaBundle\Entity\Media $medium)
    {
        $this->media[] = $medium;

        return $this;
    }

    /**
     * Remove medium
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $medium
     */
    public function removeMedia(\Application\Sonata\MediaBundle\Entity\Media $medium)
    {
        $this->media->removeElement($medium);
    }
}
