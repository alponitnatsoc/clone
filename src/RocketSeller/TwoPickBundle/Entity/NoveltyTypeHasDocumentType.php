<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NoveltyTypeHasDocumentType
 *
 * @ORM\Table(name="novelty_type_has_document_type", indexes={@ORM\Index(name="fk_novelty_type_has_document_type1", columns={"id_novelty_type_has_document_type"})})
 * @ORM\Entity
 */
class NoveltyTypeHasDocumentType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_novelty_type_has_document_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idNoveltyTypeHasDocumentType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\NoveltyType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\NoveltyType", inversedBy="requiredDocuments",cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="novelty_type_id_novelty_type", referencedColumnName="id_novelty_type")
     * })
     */
    private $noveltyTypeNoveltyType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\DocumentType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\DocumentType",cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document_type_id_document_type", referencedColumnName="id_document_type")
     * })
     */
    private $documentTypeDocumentType;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $personType;



    /**
     * Get idNoveltyTypeHasDocumentType
     *
     * @return integer
     */
    public function getIdNoveltyTypeHasDocumentType()
    {
        return $this->idNoveltyTypeHasDocumentType;
    }

    /**
     * Set noveltyTypeNoveltyType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyTypeNoveltyType
     *
     * @return NoveltyTypeHasDocumentType
     */
    public function setNoveltyTypeNoveltyType(\RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyTypeNoveltyType = null)
    {
        $this->noveltyTypeNoveltyType = $noveltyTypeNoveltyType;

        return $this;
    }

    /**
     * Get noveltyTypeNoveltyType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\NoveltyType
     */
    public function getNoveltyTypeNoveltyType()
    {
        return $this->noveltyTypeNoveltyType;
    }

    /**
     * Set documentTypeDocumentType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\DocumentType $documentTypeDocumentType
     *
     * @return NoveltyTypeHasDocumentType
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

    /**
     * Set personType
     *
     * @param string $personType
     *
     * @return NoveltyTypeHasDocumentType
     */
    public function setPersonType($personType)
    {
        $this->personType = $personType;

        return $this;
    }

    /**
     * Get personType
     *
     * @return string
     */
    public function getPersonType()
    {
        return $this->personType;
    }
}
