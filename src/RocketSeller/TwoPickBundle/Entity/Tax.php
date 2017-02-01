<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tax
 *
 * @ORM\Table(name="tax")
 * @ORM\Entity
 */
class Tax
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_tax", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTax;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=TRUE)
     */
    private $value;

    /**
     * @ORM\Column(type="text", nullable=TRUE)
     */
    private $description;
	
	/**
	 * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\TaxValueHistorical", mappedBy="taxTax")
	 */
	private $taxValueHistoricals;
	
    /**
     * Get idTax
     *
     * @return integer
     */
    public function getIdTax()
    {
        return $this->idTax;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Tax
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
     * Set value
     *
     * @param float $value
     *
     * @return Tax
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Tax
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
     * Constructor
     */
    public function __construct()
    {
        $this->taxValueHistoricals = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add taxValueHistorical
     *
     * @param \RocketSeller\TwoPickBundle\Entity\TaxValueHistorical $taxValueHistorical
     *
     * @return Tax
     */
    public function addTaxValueHistorical(\RocketSeller\TwoPickBundle\Entity\TaxValueHistorical $taxValueHistorical)
    {
        $this->taxValueHistoricals[] = $taxValueHistorical;

        return $this;
    }

    /**
     * Remove taxValueHistorical
     *
     * @param \RocketSeller\TwoPickBundle\Entity\TaxValueHistorical $taxValueHistorical
     */
    public function removeTaxValueHistorical(\RocketSeller\TwoPickBundle\Entity\TaxValueHistorical $taxValueHistorical)
    {
        $this->taxValueHistoricals->removeElement($taxValueHistorical);
    }

    /**
     * Get taxValueHistoricals
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTaxValueHistoricals()
    {
        return $this->taxValueHistoricals;
    }
}
