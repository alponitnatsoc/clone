<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SellLog
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SellLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_sell_log", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idSellLog;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     */
    private $userUser;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="target_user_id_user_target", referencedColumnName="id")
     * })
     */
    private $targetUser;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="actionType", type="string", length=30)
     */
    private $actionType;




    /**
     * Get idSellLog
     *
     * @return integer
     */
    public function getIdSellLog()
    {
        return $this->idSellLog;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return SellLog
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set actionType
     *
     * @param string $actionType
     *
     * @return SellLog
     */
    public function setActionType($actionType)
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * Get actionType
     *
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return SellLog
     */
    public function setUserUser(\RocketSeller\TwoPickBundle\Entity\User $userUser = null)
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
     * Set targetUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $targetUser
     *
     * @return SellLog
     */
    public function setTargetUser(\RocketSeller\TwoPickBundle\Entity\User $targetUser = null)
    {
        $this->targetUser = $targetUser;

        return $this;
    }

    /**
     * Get targetUser
     *
     * @return \RocketSeller\TwoPickBundle\Entity\User
     */
    public function getTargetUser()
    {
        return $this->targetUser;
    }
}
