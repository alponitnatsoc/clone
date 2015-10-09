<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrdersDescription
 *
 * @ORM\Table(name="purchase_orders_description", indexes={@ORM\Index(name="fk_purchase_orders_description_purchase_orders1", columns={"purchase_orders_id_purchase_orders"}), @ORM\Index(name="fk_purchase_orders_description_product1", columns={"product_id_product"}), @ORM\Index(name="fk_purchase_orders_description_tax1", columns={"tax_id_tax"})})
 * @ORM\Entity
 */
class PurchaseOrdersDescription
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_purchase_orders_description", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idPurchaseOrdersDescription;

    /**
     * @var \AppBundle\Entity\Tax
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Tax")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tax_id_tax", referencedColumnName="id_tax")
     * })
     */
    private $taxTax;

    /**
     * @var \AppBundle\Entity\PurchaseOrders
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\PurchaseOrders")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_id_purchase_orders", referencedColumnName="id_purchase_orders")
     * })
     */
    private $purchaseOrdersPurchaseOrders;

    /**
     * @var \AppBundle\Entity\Product
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Product")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id_product", referencedColumnName="id_product")
     * })
     */
    private $productProduct;



    /**
     * Set idPurchaseOrdersDescription
     *
     * @param integer $idPurchaseOrdersDescription
     *
     * @return PurchaseOrdersDescription
     */
    public function setIdPurchaseOrdersDescription($idPurchaseOrdersDescription)
    {
        $this->idPurchaseOrdersDescription = $idPurchaseOrdersDescription;

        return $this;
    }

    /**
     * Get idPurchaseOrdersDescription
     *
     * @return integer
     */
    public function getIdPurchaseOrdersDescription()
    {
        return $this->idPurchaseOrdersDescription;
    }

    /**
     * Set taxTax
     *
     * @param \AppBundle\Entity\Tax $taxTax
     *
     * @return PurchaseOrdersDescription
     */
    public function setTaxTax(\AppBundle\Entity\Tax $taxTax)
    {
        $this->taxTax = $taxTax;

        return $this;
    }

    /**
     * Get taxTax
     *
     * @return \AppBundle\Entity\Tax
     */
    public function getTaxTax()
    {
        return $this->taxTax;
    }

    /**
     * Set purchaseOrdersPurchaseOrders
     *
     * @param \AppBundle\Entity\PurchaseOrders $purchaseOrdersPurchaseOrders
     *
     * @return PurchaseOrdersDescription
     */
    public function setPurchaseOrdersPurchaseOrders(\AppBundle\Entity\PurchaseOrders $purchaseOrdersPurchaseOrders)
    {
        $this->purchaseOrdersPurchaseOrders = $purchaseOrdersPurchaseOrders;

        return $this;
    }

    /**
     * Get purchaseOrdersPurchaseOrders
     *
     * @return \AppBundle\Entity\PurchaseOrders
     */
    public function getPurchaseOrdersPurchaseOrders()
    {
        return $this->purchaseOrdersPurchaseOrders;
    }

    /**
     * Set productProduct
     *
     * @param \AppBundle\Entity\Product $productProduct
     *
     * @return PurchaseOrdersDescription
     */
    public function setProductProduct(\AppBundle\Entity\Product $productProduct)
    {
        $this->productProduct = $productProduct;

        return $this;
    }

    /**
     * Get productProduct
     *
     * @return \AppBundle\Entity\Product
     */
    public function getProductProduct()
    {
        return $this->productProduct;
    }
}
