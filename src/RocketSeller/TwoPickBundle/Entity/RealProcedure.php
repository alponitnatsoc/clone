<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RealProcedure
 *
 * @ORM\Table(name="real_procedure", indexes={@ORM\Index(name="fk_procedure_procedure_type1", columns={"procedure_type_id_procedure_type"}), @ORM\Index(name="fk_procedure_user1", columns={"user_id_user"}), @ORM\Index(name="fk_procedure_employer1", columns={"employer_id_employer"})})
 * @ORM\Entity
 */
class RealProcedure
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_procedure", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idProcedure;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="realProcedure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     */
    private $userUser;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\ProcedureType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\ProcedureType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="procedure_type_id_procedure_type", referencedColumnName="id_procedure_type")
     * })
     */
    private $procedureTypeProcedureType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Employer
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employer", inversedBy="realProcedure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_id_employer", referencedColumnName="id_employer")
     * })
     */
    private $employerEmployer;

    /**
     * @ORM\OneToMany(targetEntity="Action", mappedBy="realProcedureRealProcedure", cascade={"persist"})
     */
    private $action;



    /**
     * Set idProcedure
     *
     * @param integer $idProcedure
     *
     * @return RealProcedure
     */
    public function setIdProcedure($idProcedure)
    {
        $this->idProcedure = $idProcedure;

        return $this;
    }

    /**
     * Get idProcedure
     *
     * @return integer
     */
    public function getIdProcedure()
    {
        return $this->idProcedure;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return RealProcedure
     */
    public function setUserUser(\RocketSeller\TwoPickBundle\Entity\User $userUser)
    {
        $this->userUser = $userUser;

        return $this;
    }

    /**
     * Get userUser
     *
     * @return \RocketSeller\TwoPickBundle\Entity\User
     */
    public function getUserUser()
    {
        return $this->userUser;
    }

    /**
     * Set procedureTypeProcedureType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ProcedureType $procedureTypeProcedureType
     *
     * @return RealProcedure
     */
    public function setProcedureTypeProcedureType(\RocketSeller\TwoPickBundle\Entity\ProcedureType $procedureTypeProcedureType)
    {
        $this->procedureTypeProcedureType = $procedureTypeProcedureType;

        return $this;
    }

    /**
     * Get procedureTypeProcedureType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\ProcedureType
     */
    public function getProcedureTypeProcedureType()
    {
        return $this->procedureTypeProcedureType;
    }

    /**
     * Set employerEmployer
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Employer $employerEmployer
     *
     * @return RealProcedure
     */
    public function setEmployerEmployer(\RocketSeller\TwoPickBundle\Entity\Employer $employerEmployer)
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
     * Constructor
     */
    public function __construct()
    {
        $this->action = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add action
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Action $action
     *
     * @return RealProcedure
     */
    public function addAction(\RocketSeller\TwoPickBundle\Entity\Action $action)
    {
        $this->action[] = $action;

        return $this;
    }

    /**
     * Remove action
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Action $action
     */
    public function removeAction(\RocketSeller\TwoPickBundle\Entity\Action $action)
    {
        $this->action->removeElement($action);
    }

    /**
     * Get action
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return RealProcedure
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getProcedureTypeProcedureType()->getName();
    }


}
