<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NoveltyHasDocument
 *
 * @ORM\Table(name="novelty_has_document", indexes={@ORM\Index(name="fk_novelty_has_document1", columns={"id_novelty_has_document"})})
 * @ORM\Entity
 */
class NoveltyHasDocument
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_novelty_has_document", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idNoveltyHasDocument;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Novelty
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Novelty")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="novelty_id_novelty", referencedColumnName="id_novelty")
     * })
     */
    private $noveltyTypeNoveltyType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Document
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document_id_document", referencedColumnName="id_document")
     * })
     */
    private $documentDocument;




    /**
     * Get idNoveltyHasDocument
     *
     * @return integer
     */
    public function getIdNoveltyHasDocument()
    {
        return $this->idNoveltyHasDocument;
    }

    /**
     * Set noveltyTypeNoveltyType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Novelty $noveltyTypeNoveltyType
     *
     * @return NoveltyHasDocument
     */
    public function setNoveltyTypeNoveltyType(\RocketSeller\TwoPickBundle\Entity\Novelty $noveltyTypeNoveltyType = null)
    {
        $this->noveltyTypeNoveltyType = $noveltyTypeNoveltyType;

        return $this;
    }

    /**
     * Get noveltyTypeNoveltyType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Novelty
     */
    public function getNoveltyTypeNoveltyType()
    {
        return $this->noveltyTypeNoveltyType;
    }

    /**
     * Set documentDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $documentDocument
     *
     * @return NoveltyHasDocument
     */
    public function setDocumentDocument(\RocketSeller\TwoPickBundle\Entity\Document $documentDocument = null)
    {
        $this->documentDocument = $documentDocument;

        return $this;
    }

    /**
     * Get documentDocument
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getDocumentDocument()
    {
        return $this->documentDocument;
    }
}
