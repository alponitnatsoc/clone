<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NoveltyType
 *
 * @ORM\Table(name="novelty_type")
 * @ORM\Entity
 */
class NoveltyType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_novelty_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idNoveltyType;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="NoveltyTypeHasDocumentType", mappedBy="noveltyTypeNoveltyType", cascade={"persist"})
     */
    private $requiredDocuments;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requiredDocuments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get idNoveltyType
     *
     * @return integer
     */
    public function getIdNoveltyType()
    {
        return $this->idNoveltyType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return NoveltyType
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
     * Add requiredDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType $requiredDocument
     *
     * @return NoveltyType
     */
    public function addRequiredDocument(\RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType $requiredDocument)
    {
        $this->requiredDocuments[] = $requiredDocument;

        return $this;
    }

    /**
     * Remove requiredDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType $requiredDocument
     */
    public function removeRequiredDocument(\RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType $requiredDocument)
    {
        $this->requiredDocuments->removeElement($requiredDocument);
    }

    /**
     * Get requiredDocuments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequiredDocuments()
    {
        return $this->requiredDocuments;
    }
}
