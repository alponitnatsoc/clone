<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Department
 *
 * @ORM\Table(name="department", indexes={@ORM\Index(name="fk_department_country1", columns={"country_id_country"})})
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
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Country")
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
}
