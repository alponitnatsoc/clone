<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Action
 *
 * @ORM\Table(name="action", indexes={@ORM\Index(name="fk_action_procedure1", columns={"real_procedure_id_procedure"}), @ORM\Index(name="fk_action_action_type1", columns={"action_type_id_action_type"}), @ORM\Index(name="fk_action_user1", columns={"user_id_user"})})
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
     * @var string
     *
     * @ORM\Column(name="status", type="string")     
     */
    private $status;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="action")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     */
    private $userUser;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\RealProcedure
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\RealProcedure", inversedBy="action")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="real_procedure_id_procedure", referencedColumnName="id_procedure")
     * })
     */
    private $realProcedureRealProcedure;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\ActionType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\ActionType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="action_type_id_action_type", referencedColumnName="id_action_type")
     * })
     */
    private $actionTypeActionType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\ActionError
     *
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\ActionError",cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="action_error_id_action_error", referencedColumnName="id")
     * })
     */
    private $actionErrorActionError;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person", inversedBy="action")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Entity
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Entity", inversedBy="action")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id_entity", referencedColumnName="id_entity")
     * })
     */
    private $entityEntity;

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
     * Set entityEntity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Entity $entityEntity
     *
     * @return Action
     */
    public function setEntityEntity(\RocketSeller\TwoPickBundle\Entity\Entity $entityEntity = null)
    {
        $this->entityEntity = $entityEntity;

        return $this;
    }

    /**
     * Get entityEntity
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Entity
     */
    public function getEntityEntity()
    {
        return $this->entityEntity;
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
     * Set actionErrorActionError
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ActionError $actionErrorActionError
     *
     * @return Action
     */
    public function setActionErrorActionError(\RocketSeller\TwoPickBundle\Entity\ActionError $actionErrorActionError = null)
    {
        $this->actionErrorActionError = $actionErrorActionError;

        return $this;
    }

    /**
     * Get actionErrorActionError
     *
     * @return \RocketSeller\TwoPickBundle\Entity\ActionError
     */
    public function getActionErrorActionError()
    {
        return $this->actionErrorActionError;
    }
}
