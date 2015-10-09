<?php

namespace AppBundle\Entity;

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
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idDepartment;

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
     * @param \AppBundle\Entity\Country $countryCountry
     *
     * @return Department
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
}
