<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TaxHasCountry
 *
 * @ORM\Table(name="tax_has_country", indexes={@ORM\Index(name="fk_tax_has_country_tax1", columns={"tax_id_tax"}), @ORM\Index(name="fk_tax_has_country_country1", columns={"country_id_country"})})
 * @ORM\Entity
 */
class TaxHasCountry
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_tax_has_country", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idTaxHasCountry;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Tax
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Tax")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tax_id_tax", referencedColumnName="id_tax")
     * })
     */
    private $taxTax;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Country
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_id_country", referencedColumnName="id_country")
     * })
     */
    private $countryCountry;

    /**
     * Set idTaxHasCountry
     *
     * @param integer $idTaxHasCountry
     *
     * @return TaxHasCountry
     */
    public function setIdTaxHasCountry($idTaxHasCountry)
    {
        $this->idTaxHasCountry = $idTaxHasCountry;

        return $this;
    }

    /**
     * Get idTaxHasCountry
     *
     * @return integer
     */
    public function getIdTaxHasCountry()
    {
        return $this->idTaxHasCountry;
    }

    /**
     * Set taxTax
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Tax $taxTax
     *
     * @return TaxHasCountry
     */
    public function setTaxTax(\RocketSeller\TwoPickBundle\Entity\Tax $taxTax)
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

    /**
     * Set countryCountry
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Country $countryCountry
     *
     * @return TaxHasCountry
     */
    public function setCountryCountry(\RocketSeller\TwoPickBundle\Entity\Country $countryCountry)
    {
        $this->countryCountry = $countryCountry;

        return $this;
    }

    /**
     * Get countryCountry
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Country
     */
    public function getCountryCountry()
    {
        return $this->countryCountry;
    }
}
