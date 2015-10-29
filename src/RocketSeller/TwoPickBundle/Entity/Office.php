<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Office
 *
 * @ORM\Table(name="office", indexes={@ORM\Index(name="fk_office_city1", columns={"city_id_city"}), @ORM\Index(name="fk_office_department1", columns={"department_id_department"}), @ORM\Index(name="fk_office_country1", columns={"country_id_country"}), @ORM\Index(name="fk_office_entity1", columns={"entity_id_entity"})})
 * @ORM\Entity
 */
class Office
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_office", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idOffice;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Entity
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Entity", inversedBy="office")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id_entity", referencedColumnName="id_entity")
     * })
     */
    private $entityEntity;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Department
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Department")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="department_id_department", referencedColumnName="id_department")
     * })
     */
    private $departmentDepartment;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Country
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_id_country", referencedColumnName="id_country")
     * })
     */
    private $countryCountry;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\City
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_id_city", referencedColumnName="id_city")
     * })
     */
    private $cityCity;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    private $address;



    /**
     * Set idOffice
     *
     * @param integer $idOffice
     *
     * @return Office
     */
    public function setIdOffice($idOffice)
    {
        $this->idOffice = $idOffice;

        return $this;
    }

    /**
     * Get idOffice
     *
     * @return integer
     */
    public function getIdOffice()
    {
        return $this->idOffice;
    }

    /**
     * Set entityEntity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Entity $entityEntity
     *
     * @return Office
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

    /**
     * Set departmentDepartment
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Department $departmentDepartment
     *
     * @return Office
     */
    public function setDepartmentDepartment(\RocketSeller\TwoPickBundle\Entity\Department $departmentDepartment)
    {
        $this->departmentDepartment = $departmentDepartment;

        return $this;
    }

    /**
     * Get departmentDepartment
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Department
     */
    public function getDepartmentDepartment()
    {
        return $this->departmentDepartment;
    }

    /**
     * Set countryCountry
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Country $countryCountry
     *
     * @return Office
     */
    public function setCountryCountry(\RocketSeller\TwoPickBundle\Entity\Country $countryCountry)
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
     * Set cityCity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\City $cityCity
     *
     * @return Office
     */
    public function setCityCity(\RocketSeller\TwoPickBundle\Entity\City $cityCity)
    {
        $this->cityCity = $cityCity;

        return $this;
    }

    /**
     * Get cityCity
     *
     * @return \RocketSeller\TwoPickBundle\Entity\City
     */
    public function getCityCity()
    {
        return $this->cityCity;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Office
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
     * Set address
     *
     * @param string $address
     *
     * @return Office
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
}
