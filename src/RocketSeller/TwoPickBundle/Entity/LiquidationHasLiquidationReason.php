<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LiquidationHasLiquidationReason
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class LiquidationHasLiquidationReason
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Liquidation
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Liquidation", inversedBy="liquidationReasons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_liquidation", referencedColumnName="id")
     * })
     */
    private $liquidation;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\LiquidationReason
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\LiquidationReason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_liquidation_reason", referencedColumnName="id")
     * })
     */
    private $liquidationReason;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set liquidation
     *
     * @param string $liquidation
     *
     * @return LiquidationHasLiquidationReason
     */
    public function setLiquidation($liquidation)
    {
        $this->liquidation = $liquidation;

        return $this;
    }

    /**
     * Get liquidation
     *
     * @return string
     */
    public function getLiquidation()
    {
        return $this->liquidation;
    }

    /**
     * Set liquidationReason
     *
     * @param string $liquidationReason
     *
     * @return LiquidationHasLiquidationReason
     */
    public function setLiquidationReason($liquidationReason)
    {
        $this->liquidationReason = $liquidationReason;

        return $this;
    }

    /**
     * Get liquidationReason
     *
     * @return string
     */
    public function getLiquidationReason()
    {
        return $this->liquidationReason;
    }
}
