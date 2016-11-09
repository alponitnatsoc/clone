<?php

namespace RocketSeller\TwoPickBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Action
 *
 * @ORM\Table(name="action",
 *     indexes={@ORM\Index(name="fk_action_procedure1", columns={"real_procedure_id_procedure"}),
 *              @ORM\Index(name="fk_action_action_type1", columns={"action_type_id_action_type"}),
 *              @ORM\Index(name="fk_action_user1", columns={"user_id_user"}),
 *              @ORM\Index(name="action_type_index", columns={"action_type_id_action_type"}),
 *              @ORM\Index(name="action_status_index", columns={"status_type_id_action"}),
 *              @ORM\Index(name="updated_at_index", columns={"updated_at"})
 *      })
 * @ORM\Entity
 */
class Action
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_action", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idAction;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="actions", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     */
    private $userUser;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\RealProcedure
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\RealProcedure", inversedBy="action", cascade={"persist"} )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="real_procedure_id_procedure", referencedColumnName="id_procedure")
     * })
     */
    private $realProcedureRealProcedure;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person", inversedBy="action")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\ActionType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\ActionType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="action_type_id_action_type", referencedColumnName="id_action_type")
     * })
     */
    private $actionTypeActionType;

    /** @var string
     * @ORM\Column(name="action_type_name",type="string",length=50)
     */
    private $actionTypeName='';

    /**
     * @var StatusTypes
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\StatusTypes", inversedBy="actions" )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="status_type_id_action",referencedColumnName="id_status_type",nullable=true)
     * })
     */
    private $actionStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string",nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EmployerHasEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="employer_has_entity",referencedColumnName="id_employer_has_entity", nullable=true)
     */
    private $employerEntity;

    /**
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="employee_has_entity",referencedColumnName="id_employee_has_entity",nullable=true)
     */
    private $employeeEntity;

    /**
     * @ORM\Column(name="created_at",type="datetime", nullable=true)
     */
    private $createdAt=null;

    /**
     * @ORM\Column(name="updated_at",type="datetime", nullable=true)
     */
    private $updatedAt = null;

    /**
     * @ORM\Column(name="finished_at",type="datetime", nullable=true)
     */
    private $finishedAt=null;

    /**
     * @ORM\Column(name="error_at",type="datetime", nullable=true)
     */
    private $errorAt=null;

    /**
     * @ORM\Column(name="corrected_at",type="datetime", nullable=true)
     */
    private $correctedAt=null;

    /**
     * @ORM\Column(name="first_error_at",type="datetime", nullable=true)
     */
    private $firstErrorAt=null;

    /**
     * @ORM\Column(name="calculated_at",type="datetime", nullable=true)
     */
    private $calculatedAt=null;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\ActionError
     *
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\ActionError", mappedBy="action", cascade={"persist"})
     */
    private $actionErrorActionError;

    /**
     * @var integer
     * 0 - normal
     * 1 - medium
     * 2 - High
     * @ORM\Column(type="integer")
     */
    private $priority = 0;
    
    /**
     * Set idAction
     *
     * @param integer $idAction
     *
     * @return Action
     */
    public function setIdAction($idAction)
    {
        $this->idAction = $idAction;
        return $this;
    }

    /**
     * Get idAction
     *
     * @return integer
     */
    public function getIdAction()
    {
        return $this->idAction;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return Action
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
     * Set actionTypeActionType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ActionType $actionTypeActionType
     *
     * @return Action
     */
    public function setActionTypeActionType(\RocketSeller\TwoPickBundle\Entity\ActionType $actionTypeActionType)
    {
        $this->actionTypeName = $actionTypeActionType->getName();
        $this->actionTypeActionType = $actionTypeActionType;
        return $this;
    }

    /**
     * Get actionTypeActionType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\ActionType
     */
    public function getActionTypeActionType()
    {
        return $this->actionTypeActionType;
    }

    /**
     * Set personPerson
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Person $personPerson
     *
     * @return Action
     */
    public function setPersonPerson(\RocketSeller\TwoPickBundle\Entity\Person $personPerson = null)
    {
        $this->personPerson = $personPerson;
        return $this;
    }

    /**
     * Get personPerson
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Person
     */
    public function getPersonPerson()
    {
        return $this->personPerson;
    }

    /**
     * Set realProcedureRealProcedure
     *
     * @param \RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedureRealProcedure
     *
     * @return Action
     */
    public function setRealProcedureRealProcedure(\RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedureRealProcedure = null)
    {
        $this->realProcedureRealProcedure = $realProcedureRealProcedure;
        return $this;
    }

    /**
     * Get realProcedureRealProcedure
     *
     * @return \RocketSeller\TwoPickBundle\Entity\RealProcedure
     */
    public function getRealProcedureRealProcedure()
    {
        return $this->realProcedureRealProcedure;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Action
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get actionStatusCode
     *
     * @return string
     */
    public function getActionStatusCode()
    {
        return $this->actionStatus->getCode();
    }


    /**
     * @return string
     */
    public function getActionTypeCode()
    {
        return $this->actionTypeActionType->getCode();
    }

    /**
     * get Action employer
     * @return Employer
     */
    public function getEmployer()
    {
        return $this->userUser->getPersonPerson()->getEmployer();
    }

    /**
     * get Action user person
     *
     * @return Person
     */
    public function getUserPerson()
    {
        return $this->userUser->getPersonPerson();
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt= new DateTime();
        $this->actionErrorActionError = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add actionErrorActionError
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ActionError $actionErrorActionError
     *
     * @return Action
     */
    public function addActionErrorActionError(\RocketSeller\TwoPickBundle\Entity\ActionError $actionErrorActionError)
    {
        $actionErrorActionError->setAction($this);
        $this->actionErrorActionError[] = $actionErrorActionError;
        return $this;
    }

    /**
     * Remove actionErrorActionError
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ActionError $actionErrorActionError
     */
    public function removeActionErrorActionError(\RocketSeller\TwoPickBundle\Entity\ActionError $actionErrorActionError)
    {
        $this->actionErrorActionError->removeElement($actionErrorActionError);
    }

    /**
     * Set updatedAt
     * @return Action
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new DateTime();
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set actionStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\StatusTypes $actionStatus
     *
     * @return Action
     */
    public function setActionStatus(\RocketSeller\TwoPickBundle\Entity\StatusTypes $actionStatus = null)
    {
        $today = new DateTime();
        $this->status = $actionStatus->getName();
        $this->getRealProcedureRealProcedure()->setActionChangedAt($today);
        if($actionStatus->getCode()=='FIN')
            $this->finishedAt = $today;
        if($actionStatus->getCode()=='ERRO'){
            $this->finishedAt = null;
            $this->errorAt = $today;
            if($this->firstErrorAt==null)
                $this->firstErrorAt = $today;
            if($this->getRealProcedureRealProcedure()->getErrorAt()==null){
                $this->getRealProcedureRealProcedure()->setErrorAt($today);
                $this->getRealProcedureRealProcedure()->setProcedureStatus($actionStatus);
            }
        }
        if($actionStatus->getCode()=='CORT'){
            $this->finishedAt = null;
            $this->correctedAt = $today;
        }
        if($actionStatus->getCode()=='NEW'){
            $this->finishedAt = null;
        }
        $this->actionStatus = $actionStatus;
        return $this;
    }

    /**
     * Get actionStatus
     *
     * @return \RocketSeller\TwoPickBundle\Entity\StatusTypes
     */
    public function getActionStatus()
    {
        return $this->actionStatus;
    }


    /**
     * Set employerEntity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEntity $employerEntity
     *
     * @return Action
     */
    public function setEmployerEntity(\RocketSeller\TwoPickBundle\Entity\EmployerHasEntity $employerEntity = null)
    {
        $this->employerEntity = $employerEntity;

        return $this;
    }

    /**
     * Get employerEntity
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EmployerHasEntity
     */
    public function getEmployerEntity()
    {
        return $this->employerEntity;
    }

    /**
     * Set employeeEntity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $employeeEntity
     *
     * @return Action
     */
    public function setEmployeeEntity(\RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $employeeEntity = null)
    {
        $this->employeeEntity = $employeeEntity;

        return $this;
    }

    /**
     * Get employeeEntity
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity
     */
    public function getEmployeeEntity()
    {
        return $this->employeeEntity;
    }

    /**
     * Set actionTypeName
     *
     * @param string $actionTypeName
     *
     * @return Action
     */
    public function setActionTypeName($actionTypeName)
    {
        $this->actionTypeName = $actionTypeName;
        return $this;
    }

    /**
     * Get actionTypeName
     *
     * @return string
     */
    public function getActionTypeName()
    {
        return $this->actionTypeName;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Action
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
     * Set finishedAt
     *
     * @param \DateTime $finishedAt
     *
     * @return Action
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finishedAt = $finishedAt;
        return $this;
    }

    /**
     * Get finishedAt
     *
     * @return \DateTime
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * Get actionErrorActionError
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActionErrorActionError()
    {
        return $this->actionErrorActionError;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getActionTypeName();
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return Action
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set errorAt
     *
     * @param \DateTime $errorAt
     *
     * @return Action
     */
    public function setErrorAt($errorAt)
    {
        $this->errorAt = $errorAt;
        if($this->firstErrorAt == null){
            $this->firstErrorAt = $errorAt;
        }
        return $this;
    }

    /**
     * Get errorAt
     *
     * @return \DateTime
     */
    public function getErrorAt()
    {
        return $this->errorAt;
    }

    /**
     * Set correctedAt
     *
     * @param \DateTime $correctedAt
     *
     * @return Action
     */
    public function setCorrectedAt($correctedAt)
    {
        $this->correctedAt = $correctedAt;

        return $this;
    }

    /**
     * Get correctedAt
     *
     * @return \DateTime
     */
    public function getCorrectedAt()
    {
        return $this->correctedAt;
    }

    /**
     * Get firstErrorAt
     *
     * @return \DateTime
     */
    public function getFirstErrorAt()
    {
        return $this->firstErrorAt;
    }

    /**
     * Set calculatedAt
     * @return Action
     */
    public function setCalculatedAt()
    {
        $this->setUpdatedAt();
        $this->calculatedAt = $this->getUpdatedAt();
        return $this;
    }

    /**
     * Get calculatedAt
     *
     * @return \DateTime
     */
    public function getCalculatedAt()
    {
        return $this->calculatedAt;
    }
}
