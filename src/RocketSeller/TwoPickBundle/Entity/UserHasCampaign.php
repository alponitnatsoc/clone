<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\Common\Collections\Criteria;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;


/**
 * UserHasCampaign
 *
 * @ORM\Table(name="user_has_campaign")}
 * )
 * @ORM\Entity
 */
class UserHasCampaign
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_user_has_campaign", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idUserHasCampaign;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Campaign
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Campaign", inversedBy="userHasCampaigns", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="campaign_id_campaign", referencedColumnName="id_campaign")
     * })
     * @Exclude
     */
    private $campaignCampaign;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="userHasCampaigns", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     */
    private $userUser;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     * 0 - inactive
     * 1 - active
     */
    private $state = 0;

    /**
     * @ORM\Column(type="integer", nullable=TRUE)
     */
    private $uses = 0;

    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $dateStarted;


    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $lastUsed;



    /**
     * Get idUserHasCampaign
     *
     * @return integer
     */
    public function getIdUserHasCampaign()
    {
        return $this->idUserHasCampaign;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return UserHasCampaign
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set uses
     *
     * @param integer $uses
     *
     * @return UserHasCampaign
     */
    public function setUses($uses)
    {
        $this->uses = $uses;

        return $this;
    }

    /**
     * Get uses
     *
     * @return integer
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * Set dateStarted
     *
     * @param \DateTime $dateStarted
     *
     * @return UserHasCampaign
     */
    public function setDateStarted($dateStarted)
    {
        $this->dateStarted = $dateStarted;

        return $this;
    }

    /**
     * Get dateStarted
     *
     * @return \DateTime
     */
    public function getDateStarted()
    {
        return $this->dateStarted;
    }

    /**
     * Set lastUsed
     *
     * @param \DateTime $lastUsed
     *
     * @return UserHasCampaign
     */
    public function setLastUsed($lastUsed)
    {
        $this->lastUsed = $lastUsed;

        return $this;
    }

    /**
     * Get lastUsed
     *
     * @return \DateTime
     */
    public function getLastUsed()
    {
        return $this->lastUsed;
    }

    /**
     * Set campaignCampaign
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Campaign $campaignCampaign
     *
     * @return UserHasCampaign
     */
    public function setCampaignCampaign(\RocketSeller\TwoPickBundle\Entity\Campaign $campaignCampaign = null)
    {
        $this->campaignCampaign = $campaignCampaign;

        return $this;
    }

    /**
     * Get campaignCampaign
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Campaign
     */
    public function getCampaignCampaign()
    {
        return $this->campaignCampaign;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return UserHasCampaign
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
}
