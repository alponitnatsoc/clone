<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityType
 *
 * @ORM\Table(name="entity_type")
 * @ORM\Entity
 */
class EntityType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_entity_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEntityType;
    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Entity", mappedBy="entityTypeEntityType")
     */
    private $entities;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $payroll_code;


    /**
     * Get idEntityType
     *
     * @return integer
     */
    public function getIdEntityType()
    {
        return $this->idEntityType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EntityType
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
     * Set payroll code
     *
     * @param string $payroll_code
     *
     * @return PayType
     */
    public function setPayrollCode($payroll_code)
    {
        $this->payroll_code = $payroll_code;

        return $this;
    }

    /**
     * Get payroll_code
     *
     * @return string
     */
    public function getPayrollCode()
    {
        return $this->payroll_code;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->entities = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add entity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Entity $entity
     *
     * @return EntityType
     */
    public function addEntity(\RocketSeller\TwoPickBundle\Entity\Entity $entity)
    {
        $this->entities[] = $entity;

        return $this;
    }

    /**
     * Remove entity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Entity $entity
     */
    public function removeEntity(\RocketSeller\TwoPickBundle\Entity\Entity $entity)
    {
        $this->entities->removeElement($entity);
    }

    /**
     * Get entities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntities()
    {
        return $this->entities;
    }
}
