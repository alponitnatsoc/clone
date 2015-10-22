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
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idEntity;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\EntityType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EntityType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_type_id_entity_type", referencedColumnName="id_entity_type")
     * })
     */
    private $entityTypeEntityType;

    /**
     * @ORM\OneToMany(targetEntity="EntityFields", mappedBy="entityEntity", cascade={"persist"})
     */
    private $entityFields;

    /**
     * @ORM\OneToMany(targetEntity="Office", mappedBy="entityEntity", cascade={"persist"})
     */
    private $office;
    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;



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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->entityFields = new \Doctrine\Common\Collections\ArrayCollection();
        $this->office = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add entityField
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityFields $entityField
     *
     * @return Entity
     */
    public function addEntityField(\RocketSeller\TwoPickBundle\Entity\EntityFields $entityField)
    {
        $this->entityFields[] = $entityField;

        return $this;
    }

    /**
     * Remove entityField
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityFields $entityField
     */
    public function removeEntityField(\RocketSeller\TwoPickBundle\Entity\EntityFields $entityField)
    {
        $this->entityFields->removeElement($entityField);
    }

    /**
     * Get entityFields
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntityFields()
    {
        return $this->entityFields;
    }

    /**
     * Add office
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Office $office
     *
     * @return Entity
     */
    public function addOffice(\RocketSeller\TwoPickBundle\Entity\Office $office)
    {
        $this->office[] = $office;

        return $this;
    }

    /**
     * Remove office
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Office $office
     */
    public function removeOffice(\RocketSeller\TwoPickBundle\Entity\Office $office)
    {
        $this->office->removeElement($office);
    }

    /**
     * Get office
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOffice()
    {
        return $this->office;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Entity
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
