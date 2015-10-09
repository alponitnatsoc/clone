<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 *
 * @ORM\Table(name="city", indexes={@ORM\Index(name="fk_city_department1", columns={"department_id_department"})})
 * @ORM\Entity
 */
class City
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_city", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCity;

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
     * Set idCity
     *
     * @param integer $idCity
     *
     * @return City
     */
    public function setIdCity($idCity)
    {
        $this->idCity = $idCity;

        return $this;
    }

    /**
     * Get idCity
     *
     * @return integer
     */
    public function getIdCity()
    {
        return $this->idCity;
    }

    /**
     * Set departmentDepartment
     *
     * @param \AppBundle\Entity\Department $departmentDepartment
     *
     * @return City
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
}
