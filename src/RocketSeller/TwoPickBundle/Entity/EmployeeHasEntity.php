<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmployeeHasEntity
 *
 * @ORM\Table(name="employee_has_entity", indexes={@ORM\Index(name="fk_employee_has_entity_employee1", columns={"employee_id_employee"}), @ORM\Index(name="fk_employee_has_entity_entity1", columns={"entity_id_entity"})})
 * @ORM\Entity
 */
class EmployeeHasEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_employee_has_entity", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idEmployeeHasEntity;

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
     * @var \AppBundle\Entity\Employee
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Employee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employee_id_employee", referencedColumnName="id_employee")
     * })
     */
    private $employeeEmployee;



    /**
     * Set idEmployeeHasEntity
     *
     * @param integer $idEmployeeHasEntity
     *
     * @return EmployeeHasEntity
     */
    public function setIdEmployeeHasEntity($idEmployeeHasEntity)
    {
        $this->idEmployeeHasEntity = $idEmployeeHasEntity;

        return $this;
    }

    /**
     * Get idEmployeeHasEntity
     *
     * @return integer
     */
    public function getIdEmployeeHasEntity()
    {
        return $this->idEmployeeHasEntity;
    }

    /**
     * Set entityEntity
     *
     * @param \AppBundle\Entity\Entity $entityEntity
     *
     * @return EmployeeHasEntity
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
     * Set employeeEmployee
     *
     * @param \AppBundle\Entity\Employee $employeeEmployee
     *
     * @return EmployeeHasEntity
     */
    public function setEmployeeEmployee(\AppBundle\Entity\Employee $employeeEmployee)
    {
        $this->employeeEmployee = $employeeEmployee;

        return $this;
    }

    /**
     * Get employeeEmployee
     *
     * @return \AppBundle\Entity\Employee
     */
    public function getEmployeeEmployee()
    {
        return $this->employeeEmployee;
    }
}
