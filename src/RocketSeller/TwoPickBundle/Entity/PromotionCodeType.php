<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PromotionCodeType
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PromotionCodeType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_promotion_code_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPromotionCodeType;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=50)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="short_name", type="string", length=50)
     */
    private $shortName;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration;

    /**
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\PromotionCode", mappedBy="promotionCodeTypePromotionCodeType", cascade={"persist"})
     */
    private $promotionCodes;

    /**
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\PromotionCodeTypeHasProduct", mappedBy="promotionCodeTypePromotionCodeType", cascade={"persist"})
     */
    private $products;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->promotionCodes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get idPromotionCodeType
     *
     * @return integer
     */
    public function getIdPromotionCodeType()
    {
        return $this->idPromotionCodeType;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return PromotionCodeType
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
     * Set shortName
     *
     * @param string $shortName
     *
     * @return PromotionCodeType
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Get shortName
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     *
     * @return PromotionCodeType
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Add promotionCode
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PromotionCode $promotionCode
     *
     * @return PromotionCodeType
     */
    public function addPromotionCode(\RocketSeller\TwoPickBundle\Entity\PromotionCode $promotionCode)
    {
        $this->promotionCodes[] = $promotionCode;

        return $this;
    }

    /**
     * Remove promotionCode
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PromotionCode $promotionCode
     */
    public function removePromotionCode(\RocketSeller\TwoPickBundle\Entity\PromotionCode $promotionCode)
    {
        $this->promotionCodes->removeElement($promotionCode);
    }

    /**
     * Get promotionCodes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPromotionCodes()
    {
        return $this->promotionCodes;
    }

    /**
     * Add product
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PromotionCodeTypeHasProduct $product
     *
     * @return PromotionCodeType
     */
    public function addProduct(\RocketSeller\TwoPickBundle\Entity\PromotionCodeTypeHasProduct $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PromotionCodeTypeHasProduct $product
     */
    public function removeProduct(\RocketSeller\TwoPickBundle\Entity\PromotionCodeTypeHasProduct $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }
}
