<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Workplace
 *
 * @ORM\Table(name="workplace")
 * @ORM\Entity
 */
class Workplace
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_workplace", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idWorkplace;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Employer
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employer",  inversedBy="workplaces")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_id_employer", referencedColumnName="id_employer")
     * })
     */
    private $employerEmployer;



    /**
     * @ORM\OneToMany(targetEntity="ContractHasWorkplace", mappedBy="workplaceWorkplace", cascade={"persist"})
     */
    private $workplaceHasContracts;

    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    private $mainAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Department")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_department", referencedColumnName="id_department")
     * })
     */
    private $department;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="id_city", referencedColumnName="id_city")
     */
    private $city;



    /**
     * Set idWorkplace
     *
     * @param integer $idWorkplace
     *
     * @return Workplace
     */
    public function setIdWorkplace($idWorkplace)
    {
        $this->idWorkplace = $idWorkplace;

        return $this;
    }

    /**
     * Get idWorkplace
     *
     * @return integer
     */
    public function getIdWorkplace()
    {
        return $this->idWorkplace;
    }

    /**
     * Set employeeEmployee
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Employee $employeeEmployee
     *
     * @return Workplace
     */
    public function setEmployeeEmployee(\RocketSeller\TwoPickBundle\Entity\Employee $employeeEmployee)
    {
        $this->employeeEmployee = $employeeEmployee;

        return $this;
    }

    /**
     * Get employeeEmployee
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Employee
     */
    public function getEmployeeEmployee()
    {
        return $this->employeeEmployee;
    }

    /**
     * Set contractContract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contractContract
     *
     * @return Workplace
     */
    public function setContractContract(\RocketSeller\TwoPickBundle\Entity\Contract $contractContract)
    {
        $this->contractContract = $contractContract;

        return $this;
    }

    /**
     * Get contractContract
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Contract
     */
    public function getContractContract()
    {
        return $this->contractContract;
    }

    /**
     * Set employerEmployer
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Employer $employerEmployer
     *
     * @return Workplace
     */
    public function setEmployerEmployer(\RocketSeller\TwoPickBundle\Entity\Employer $employerEmployer = null)
    {
        $this->employerEmployer = $employerEmployer;

        return $this;
    }

    /**
     * Get employerEmployer
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Employer
     */
    public function getEmployerEmployer()
    {
        return $this->employerEmployer;
    }

    /**
     * Set mainAddress
     *
     * @param string $mainAddress
     *
     * @return Workplace
     */
    public function setMainAddress($mainAddress)
    {
        $this->mainAddress = $mainAddress;

        return $this;
    }

    /**
     * Get mainAddress
     *
     * @return string
     */
    public function getMainAddress()
    {
        return $this->mainAddress;
    }

    /**
     * Set department
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Department $department
     *
     * @return Workplace
     */
    public function setDepartment(\RocketSeller\TwoPickBundle\Entity\Department $department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set city
     *
     * @param \RocketSeller\TwoPickBundle\Entity\City $city
     *
     * @return Workplace
     */
    public function setCity(\RocketSeller\TwoPickBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \RocketSeller\TwoPickBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->workplaceHasContracts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add workplaceHasContract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace $workplaceHasContract
     *
     * @return Workplace
     */
    public function addWorkplaceHasContract(\RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace $workplaceHasContract)
    {
        $this->workplaceHasContracts[] = $workplaceHasContract;

        return $this;
    }

    /**
     * Remove workplaceHasContract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace $workplaceHasContract
     */
    public function removeWorkplaceHasContract(\RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace $workplaceHasContract)
    {
        $this->workplaceHasContracts->removeElement($workplaceHasContract);
    }

    /**
     * Get workplaceHasContracts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorkplaceHasContracts()
    {
        return $this->workplaceHasContracts;
    }
}
