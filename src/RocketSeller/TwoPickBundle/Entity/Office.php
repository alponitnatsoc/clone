<?php

namespace AppBundle\Entity;

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
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idOffice;

    /**
     * @var \AppBundle\Entity\Entity
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Entity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id_entity", referencedColumnName="id_entity")
     * })
     */
    private $entityEntity;

    /**
     * @var \AppBundle\Entity\Department
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Department")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="department_id_department", referencedColumnName="id_department")
     * })
     */
    private $departmentDepartment;

    /**
     * @var \AppBundle\Entity\Country
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_id_country", referencedColumnName="id_country")
     * })
     */
    private $countryCountry;

    /**
     * @var \AppBundle\Entity\City
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_id_city", referencedColumnName="id_city")
     * })
     */
    private $cityCity;



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
     * @param \AppBundle\Entity\Entity $entityEntity
     *
     * @return Office
     */
    public function setEntityEntity(\AppBundle\Entity\Entity $entityEntity)
    {
        $this->entityEntity = $entityEntity;

        return $this;
    }

    /**
     * Get entityEntity
     *
     * @return \AppBundle\Entity\Entity
     */
    public function getEntityEntity()
    {
        return $this->entityEntity;
    }

    /**
     * Set departmentDepartment
     *
     * @param \AppBundle\Entity\Department $departmentDepartment
     *
     * @return Office
     */
    public function setDepartmentDepartment(\AppBundle\Entity\Department $departmentDepartment)
    {
        $this->departmentDepartment = $departmentDepartment;

        return $this;
    }

    /**
     * Get departmentDepartment
     *
     * @return \AppBundle\Entity\Department
     */
    public function getDepartmentDepartment()
    {
        return $this->departmentDepartment;
    }

    /**
     * Set countryCountry
     *
     * @param \AppBundle\Entity\Country $countryCountry
     *
     * @return Office
     */
    public function setCountryCountry(\AppBundle\Entity\Country $countryCountry)
    {
        $this->countryCountry = $countryCountry;

        return $this;
    }

    /**
     * Get countryCountry
     *
     * @return \AppBundle\Entity\Country
     */
    public function getCountryCountry()
    {
        return $this->countryCountry;
    }

    /**
     * Set cityCity
     *
     * @param \AppBundle\Entity\City $cityCity
     *
     * @return Office
     */
    public function setCityCity(\AppBundle\Entity\City $cityCity)
    {
        $this->cityCity = $cityCity;

        return $this;
    }

    /**
     * Get cityCity
     *
     * @return \AppBundle\Entity\City
     */
    public function getCityCity()
    {
        return $this->cityCity;
    }
}
