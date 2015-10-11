<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityFields
 *
 * @ORM\Table(name="entity_fields", indexes={@ORM\Index(name="fk_entity_fields_entity1", columns={"entity_id_entity"})})
 * @ORM\Entity
 */
class EntityFields
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_entity_fields", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idEntityFields;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Entity
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Entity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id_entity", referencedColumnName="id_entity")
     * })
     */
    private $entityEntity;



    /**
     * Set idEntityFields
     *
     * @param integer $idEntityFields
     *
     * @return EntityFields
     */
    public function setIdEntityFields($idEntityFields)
    {
        $this->idEntityFields = $idEntityFields;

        return $this;
    }

    /**
     * Get idEntityFields
     *
     * @return integer
     */
    public function getIdEntityFields()
    {
        return $this->idEntityFields;
    }

    /**
     * Set entityEntity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Entity $entityEntity
     *
     * @return EntityFields
     */
    public function setEntityEntity(\RocketSeller\TwoPickBundle\Entity\Entity $entityEntity)
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
}
