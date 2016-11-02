<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * ActionError
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ActionError
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=500)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=100)
     */
    private $status;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Action
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Action", inversedBy="actionErrorActionError")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="action_id_action", referencedColumnName="id_action")
     * })
     */
    private $action;

    /**
     * @ORM\Column(name="solved_at",type="datetime", nullable=true)
     */
    private $solvedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ActionError
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return ActionError
     */
    public function setStatus($status)
    {
        $this->status = $status;
        if($status == 'solved'){
            $this->solvedAt = new DateTime();
        }
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
     * Set action
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Action $action
     *
     * @return ActionError
     */
    public function setAction(\RocketSeller\TwoPickBundle\Entity\Action $action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set solvedAt
     *
     * @param \DateTime $solvedAt
     *
     * @return ActionError
     */
    public function setSolvedAt($solvedAt)
    {
        $this->solvedAt = $solvedAt;

        return $this;
    }

    /**
     * Get solvedAt
     *
     * @return \DateTime
     */
    public function getSolvedAt()
    {
        return $this->solvedAt;
    }
}
