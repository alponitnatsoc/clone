<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\SmallIntType;

/**
 * Referred
 *
 * @ORM\Table(name="referred")
 * @ORM\Entity
 */
class Referred
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
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="referrals")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $userId;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="referred_user_id", referencedColumnName="id")
     * })
     */
    private $referredUserId;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Invitation
     *
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Invitation", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="invitation_id", referencedColumnName="id")
     * })
     */
    private $invitationId;

    /**
     * Estado para verificar si el descuento por el referido ya fue aplicado al usuario que refiere.
     * 0 - No se ha redimido
     * 1 - Descuento redimido
     *
     * @var SmallIntType
     *
     * @ORM\Column(type="smallint")
     */
    private $status = 0;


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
     * Set userId
     *
     * @param integer $userId
     *
     * @return Referred
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set referredUserId
     *
     * @param integer $referredUserId
     *
     * @return Referred
     */
    public function setReferredUserId($referredUserId)
    {
        $this->referredUserId = $referredUserId;

        return $this;
    }

    /**
     * Get referredUserId
     *
     * @return integer
     */
    public function getReferredUserId()
    {
        return $this->referredUserId;
    }

    /**
     * Set invitationId
     *
     * @param integer $invitationId
     *
     * @return Referred
     */
    public function setInvitationId($invitationId)
    {
        $this->invitationId = $invitationId;

        return $this;
    }

    /**
     * Get invitationId
     *
     * @return integer
     */
    public function getInvitationId()
    {
        return $this->invitationId;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Referred
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
}
