<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Department
 *
 * @ORM\Table(name="department")
 * @ORM\Entity
 */
class Department
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_department", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idDepartment;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Country
     *
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_id_country", referencedColumnName="id_country")
     * })
     */
    private $countryCountry;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;

    /**
     *
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\City", mappedBy="departmentDepartment")
     */
    private $citys;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $departmentCode;
    /**
     * @ORM\ManyToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\Entity", mappedBy="departments")
     */
    private $entities;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->citys = new \Doctrine\Common\Collections\ArrayCollection();
        $this->entities = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get idDepartment
     *
     * @return integer
     */
    public function getIdDepartment()
    {
        return $this->idDepartment;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Department
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
     * Set departmentCode
     *
     * @param integer $departmentCode
     *
     * @return Department
     */
    public function setDepartmentCode($departmentCode)
    {
        $this->departmentCode = $departmentCode;

        return $this;
    }

    /**
     * Get departmentCode
     *
     * @return integer
     */
    public function getDepartmentCode()
    {
        return $this->departmentCode;
    }

    /**
     * Set countryCountry
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Country $countryCountry
     *
     * @return Department
     */
    public function setCountryCountry(\RocketSeller\TwoPickBundle\Entity\Country $countryCountry = null)
    {
        $this->countryCountry = $countryCountry;

        return $this;
    }

    /**
     * Get countryCountry
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Country
     */
    public function getCountryCountry()
    {
        return $this->countryCountry;
    }

    /**
     * Add city
     *
     * @param \RocketSeller\TwoPickBundle\Entity\City $city
     *
     * @return Department
     */
    public function addCity(\RocketSeller\TwoPickBundle\Entity\City $city)
    {
        $this->citys[] = $city;

        return $this;
    }

    /**
     * Remove city
     *
     * @param \RocketSeller\TwoPickBundle\Entity\City $city
     */
    public function removeCity(\RocketSeller\TwoPickBundle\Entity\City $city)
    {
        $this->citys->removeElement($city);
    }

    /**
     * Get citys
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCitys()
    {
        return $this->citys;
    }

    /**
     * Add entity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Entity $entity
     *
     * @return Department
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
