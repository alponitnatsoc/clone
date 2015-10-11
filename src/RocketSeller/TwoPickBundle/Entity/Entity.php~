<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity
 *
 * @ORM\Table(name="entity", indexes={@ORM\Index(name="fk_entity_entity_type1", columns={"entity_type_id_entity_type"})})
 * @ORM\Entity
 */
class Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_entity", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idEntity;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\EntityType
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EntityType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_type_id_entity_type", referencedColumnName="id_entity_type")
     * })
     */
    private $entityTypeEntityType;



    /**
     * Set idEntity
     *
     * @param integer $idEntity
     *
     * @return Entity
     */
    public function setIdEntity($idEntity)
    {
        $this->idEntity = $idEntity;

        return $this;
    }

    /**
     * Get idEntity
     *
     * @return integer
     */
    public function getIdEntity()
    {
        return $this->idEntity;
    }

    /**
     * Set entityTypeEntityType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityType $entityTypeEntityType
     *
     * @return Entity
     */
    public function setEntityTypeEntityType(\RocketSeller\TwoPickBundle\Entity\EntityType $entityTypeEntityType)
    {
        $this->entityTypeEntityType = $entityTypeEntityType;

        return $this;
    }

    /**
     * Get entityTypeEntityType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EntityType
     */
    public function getEntityTypeEntityType()
    {
        return $this->entityTypeEntityType;
    }
}
