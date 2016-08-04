<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CalculatorConstraints
 *
 * @ORM\Table(name="pila_tax")
 * @ORM\Entity
 */
class PilaTax
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_pila_tax", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPilaTax;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $month;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $year;

    /**
     * @ORM\Column(type="float", nullable=TRUE)
     */
    private $tax;



    /**
     * Get idPilaTax
     *
     * @return integer
     */
    public function getIdPilaTax()
    {
        return $this->idPilaTax;
    }

    /**
     * Set month
     *
     * @param integer $month
     *
     * @return PilaTax
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
     * Set year
     *
     * @param integer $year
     *
     * @return PilaTax
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
     * Set tax
     *
     * @param float $tax
     *
     * @return PilaTax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get tax
     *
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }
}
