<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 */
class Campaign
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idCampaign;


    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $enabled = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=TRUE)
     */
    private $stock;

    /**
     * @var integer
     *
     * @ORM\Column(type="string", length=50, nullable=TRUE)
     */
    private $description;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $dateStart;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $dateEnd;

    /**
     * @ORM\OneToMany(targetEntity="UserHasCampaign", mappedBy="campaignCampaign", cascade={"persist"})
     */
    private $userHasCampaigns;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userHasCampaigns = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get idCampaign
     *
     * @return integer
     */
    public function getIdCampaign()
    {
        return $this->idCampaign;
    }

    /**
     * Set enabled
     *
     * @param integer $enabled
     *
     * @return Campaign
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return integer
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set stock
     *
     * @param integer $stock
     *
     * @return Campaign
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock
     *
     * @return integer
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set dateStart
     *
     * @param \DateTime $dateStart
     *
     * @return Campaign
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Get dateStart
     *
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     *
     * @return Campaign
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Add userHasCampaign
     *
     * @param \RocketSeller\TwoPickBundle\Entity\UserHasCampaign $userHasCampaign
     *
     * @return Campaign
     */
    public function addUserHasCampaign(\RocketSeller\TwoPickBundle\Entity\UserHasCampaign $userHasCampaign)
    {
        $this->userHasCampaigns[] = $userHasCampaign;

        return $this;
    }

    /**
     * Remove userHasCampaign
     *
     * @param \RocketSeller\TwoPickBundle\Entity\UserHasCampaign $userHasCampaign
     */
    public function removeUserHasCampaign(\RocketSeller\TwoPickBundle\Entity\UserHasCampaign $userHasCampaign)
    {
        $this->userHasCampaigns->removeElement($userHasCampaign);
    }

    /**
     * Get userHasCampaigns
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserHasCampaigns()
    {
        return $this->userHasCampaigns;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Campaign
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
}
