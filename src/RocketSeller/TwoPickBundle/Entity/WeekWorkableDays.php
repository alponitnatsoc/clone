<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WeekWorkableDays
 *
 * @ORM\Table(name="week_workable_days")
 * @ORM\Entity
 */
class WeekWorkableDays
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_week_workable_days", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idWeekWorkableDays;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $dayName;
    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Contract
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Contract", inversedBy="weekWorkableDays")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_id_contract", referencedColumnName="id_contract")
     * })
     */
    private $contractContract;





    /**
     * Get idWeekWorkableDays
     *
     * @return integer
     */
    public function getIdWeekWorkableDays()
    {
        return $this->idWeekWorkableDays;
    }

    /**
     * Set dayName
     *
     * @param string $dayName
     *
     * @return WeekWorkableDays
     */
    public function setDayName($dayName)
    {
        $this->dayName = $dayName;

        return $this;
    }

    /**
     * Get dayName
     *
     * @return string
     */
    public function getDayName()
    {
        return $this->dayName;
    }

    /**
     * Set contractContract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contractContract
     *
     * @return WeekWorkableDays
     */
    public function setContractContract(\RocketSeller\TwoPickBundle\Entity\Contract $contractContract = null)
    {
        $this->contractContract = $contractContract;

        return $this;
    }

    /**
     * Get contractContract
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Contract
     */
    public function getContractContract()
    {
        return $this->contractContract;
    }
}
