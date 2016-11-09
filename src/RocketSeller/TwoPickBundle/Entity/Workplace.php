<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

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
     * @Exclude
     */
    private $employerEmployer;

    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Contract", mappedBy="workplaceWorkplace", cascade={"persist"})
     */
    private $contracts;

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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActiveContracts()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('state',1));
        return $this->contracts->matching($criteria);
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contracts = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Workplace
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
     * Add contract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contract
     *
     * @return Workplace
     */
    public function addContract(\RocketSeller\TwoPickBundle\Entity\Contract $contract)
    {
        $contract->setWorkplaceWorkplace($this);
        $this->contracts[] = $contract;

        return $this;
    }

    /**
     * Remove contract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contract
     */
    public function removeContract(\RocketSeller\TwoPickBundle\Entity\Contract $contract)
    {
        $this->contracts->removeElement($contract);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContracts()
    {
        return $this->contracts;
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
}
