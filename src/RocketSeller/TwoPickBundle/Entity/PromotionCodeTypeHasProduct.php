<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PromotionCodeTypeHasProduct
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PromotionCodeTypeHasProduct
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_promotion_code_type_has_product", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPromotionCodeTypeHasProduct;

    /**
     * @var integer
     *
     * @ORM\Column(name="percent", type="integer")
     */
    private $percent;


    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PromotionCodeType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PromotionCodeType", inversedBy="products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="promotion_code_type_id_promotion_code_type", referencedColumnName="id_promotion_code_type")
     * })
     */
    private $promotionCodeTypePromotionCodeType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Product
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Product")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id_product", referencedColumnName="id_product")
     * })
     */
    private $productProduct;



    /**
     * Get idPromotionCodeTypeHasProduct
     *
     * @return integer
     */
    public function getIdPromotionCodeTypeHasProduct()
    {
        return $this->idPromotionCodeTypeHasProduct;
    }

    /**
     * Set percent
     *
     * @param integer $percent
     *
     * @return PromotionCodeTypeHasProduct
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * Get percent
     *
     * @return integer
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * Set promotionCodeTypePromotionCodeType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PromotionCodeType $promotionCodeTypePromotionCodeType
     *
     * @return PromotionCodeTypeHasProduct
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
     * Set productProduct
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Product $productProduct
     *
     * @return PromotionCodeTypeHasProduct
     */
    public function setProductProduct(\RocketSeller\TwoPickBundle\Entity\Product $productProduct = null)
    {
        $this->productProduct = $productProduct;

        return $this;
    }

    /**
     * Get productProduct
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Product
     */
    public function getProductProduct()
    {
        return $this->productProduct;
    }
}
