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
     * Set idDepartment
     *
     * @param integer $idDepartment
     *
     * @return Department
     */
    public function setIdDepartment($idDepartment)
    {
        $this->idDepartment = $idDepartment;

        return $this;
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
     * Set countryCountry
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Country $countryCountry
     *
     * @return Department
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
     * Constructor
     */
    public function __construct()
    {
        $this->citys = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function __toString()
    {
        return (string) $this->name;
    }
}
