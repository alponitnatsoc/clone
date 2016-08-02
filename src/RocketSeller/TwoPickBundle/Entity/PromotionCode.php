<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PromotionCode
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PromotionCode
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_promotion_code", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPromotionCode;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=50, nullable=true)
     */
    private $code;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="date",nullable=true)
     */
    private $endDate;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PromotionCodeType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PromotionCodeType", inversedBy="promotionCodes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="promotion_code_type_id_promotion_code_type", referencedColumnName="id_promotion_code_type")
     * })
     */
    private $promotionCodeTypePromotionCodeType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="promotionCodes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     */
    private $userUser;



    /**
     * Get idPromotionCode
     *
     * @return integer
     */
    public function getIdPromotionCode()
    {
        return $this->idPromotionCode;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return PromotionCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return PromotionCode
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return PromotionCode
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set promotionCodeTypePromotionCodeType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PromotionCodeType $promotionCodeTypePromotionCodeType
     *
     * @return PromotionCode
     */
    public function setPromotionCodeTypePromotionCodeType(\RocketSeller\TwoPickBundle\Entity\PromotionCodeType $promotionCodeTypePromotionCodeType = null)
    {
        $this->promotionCodeTypePromotionCodeType = $promotionCodeTypePromotionCodeType;

        return $this;
    }

    /**
     * Get promotionCodeTypePromotionCodeType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PromotionCodeType
     */
    public function getPromotionCodeTypePromotionCodeType()
    {
        return $this->promotionCodeTypePromotionCodeType;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return PromotionCode
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
