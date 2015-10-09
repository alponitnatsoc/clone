<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Action
 *
 * @ORM\Table(name="action", indexes={@ORM\Index(name="fk_action_procedure1", columns={"procedure_id_procedure"}), @ORM\Index(name="fk_action_action_type1", columns={"action_type_id_action_type"}), @ORM\Index(name="fk_action_user1", columns={"user_id_user"})})
 * @ORM\Entity
 */
class Action
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_action", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idAction;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id_user")
     * })
     */
    private $userUser;

    /**
     * @var \AppBundle\Entity\Procedure
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Procedure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="procedure_id_procedure", referencedColumnName="id_procedure")
     * })
     */
    private $procedureProcedure;

    /**
     * @var \AppBundle\Entity\ActionType
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ActionType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="action_type_id_action_type", referencedColumnName="id_action_type")
     * })
     */
    private $actionTypeActionType;



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
     * @param \AppBundle\Entity\User $userUser
     *
     * @return Action
     */
    public function setUserUser(\AppBundle\Entity\User $userUser)
    {
        $this->userUser = $userUser;

        return $this;
    }

    /**
     * Get userUser
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserUser()
    {
        return $this->userUser;
    }

    /**
     * Set procedureProcedure
     *
     * @param \AppBundle\Entity\Procedure $procedureProcedure
     *
     * @return Action
     */
    public function setProcedureProcedure(\AppBundle\Entity\Procedure $procedureProcedure)
    {
        $this->procedureProcedure = $procedureProcedure;

        return $this;
    }

    /**
     * Get procedureProcedure
     *
     * @return \AppBundle\Entity\Procedure
     */
    public function getProcedureProcedure()
    {
        return $this->procedureProcedure;
    }

    /**
     * Set actionTypeActionType
     *
     * @param \AppBundle\Entity\ActionType $actionTypeActionType
     *
     * @return Action
     */
    public function setActionTypeActionType(\AppBundle\Entity\ActionType $actionTypeActionType)
    {
        $this->actionTypeActionType = $actionTypeActionType;

        return $this;
    }

    /**
     * Get actionTypeActionType
     *
     * @return \AppBundle\Entity\ActionType
     */
    public function getActionTypeActionType()
    {
        return $this->actionTypeActionType;
    }
}
