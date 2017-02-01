<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tax
 *
 * @ORM\Table(name="tax_value_historical")
 * @ORM\Entity
 */
class TaxValueHistorical
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_tax_value_historical", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTaxValueHistorical;

    /**
     * @ORM\Column(type="smallint", length=4, nullable=TRUE)
     */
    private $year;
	
	/**
	 * @ORM\Column(type="smallint", length=2, nullable=TRUE)
	 */
	private $month;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=TRUE)
     */
    private $value;
	
	/**
	 * @var \RocketSeller\TwoPickBundle\Entity\Tax
	 * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Tax", inversedBy="taxValueHistoricals")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_tax", referencedColumnName="id_tax")
	 * })
	 *
	 */
	private $taxTax;


    /**
     * Get idTaxValueHistorical
     *
     * @return integer
     */
    public function getIdTaxValueHistorical()
    {
        return $this->idTaxValueHistorical;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return TaxValueHistorical
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set month
     *
     * @param integer $month
     *
     * @return TaxValueHistorical
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return integer
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return TaxValueHistorical
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set taxTax
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Tax $taxTax
     *
     * @return TaxValueHistorical
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
