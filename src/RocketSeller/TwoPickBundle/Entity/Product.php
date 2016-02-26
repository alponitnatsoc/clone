<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity
 */
class Product
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_product", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idProduct;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $simpleName;

    /**
     * @ORM\Column(type="float", nullable=TRUE)
     */
    private $price;

    /**
     * @ORM\Column(type="text", nullable=TRUE)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $validity;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Tax
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Tax")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tax_id_tax", referencedColumnName="id_tax")
     * })
     */
    private $taxTax;

    /**
     * Get idProduct
     *
     * @return integer
     */
    public function getIdProduct()
    {
        return $this->idProduct;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Product
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
     * Set validity
     *
     * @param string $validity
     *
     * @return Product
     */
    public function setValidity($validity)
    {
        $this->validity = $validity;

        return $this;
    }

    /**
     * Get validity
     *
     * @return string
     */
    public function getValidity()
    {
        return $this->validity;
    }

    /**
     * Set simpleName
     *
     * @param string $simpleName
     *
     * @return Product
     */
    public function setSimpleName($simpleName)
    {
        $this->simpleName = $simpleName;

        return $this;
    }

    /**
     * Get simpleName
     *
     * @return string
     */
    public function getSimpleName()
    {
        return $this->simpleName;
    }

    /**
     * Set taxTax
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Tax $taxTax
     *
     * @return Product
     */
    public function setTaxTax(\RocketSeller\TwoPickBundle\Entity\Tax $taxTax = null)
    {
        $this->taxTax = $taxTax;

        return $this;
    }

    /**
     * Get taxTax
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Tax
     */
    public function getTaxTax()
    {
        return $this->taxTax;
    }

}
