<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WeekWorkableDaysRecord
 *
 * @ORM\Table(name="week_workable_days_record")
 * @ORM\Entity
 */
class WeekWorkableDaysRecord
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_week_workable_days_record", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idWeekWorkableDaysRecord;

    /**
     * @ORM\Column(type="smallint",nullable=true)
     */
    private $dayNumber;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $dayName;
	
    /**
     * @var \RocketSeller\TwoPickBundle\Entity\ContractRecord
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\ContractRecord", inversedBy="weekWorkableDaysRecord")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_record_id", referencedColumnName="id_contract_record")
     * })
     */
    private $contractRecordContractRecord;

    /**
     * Get idWeekWorkableDaysRecord
     *
     * @return integer
     */
    public function getIdWeekWorkableDaysRecord()
    {
        return $this->idWeekWorkableDaysRecord;
    }

    /**
     * Set dayNumber
     *
     * @param integer $dayNumber
     *
     * @return WeekWorkableDaysRecord
     */
    public function setDayNumber($dayNumber)
    {
        $this->dayNumber = $dayNumber;

        return $this;
    }

    /**
     * Get dayNumber
     *
     * @return integer
     */
    public function getDayNumber()
    {
        return $this->dayNumber;
    }

    /**
     * Set dayName
     *
     * @param string $dayName
     *
     * @return WeekWorkableDaysRecord
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
     * Set contractRecordContractRecord
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractRecord $contractRecordContractRecord
     *
     * @return WeekWorkableDaysRecord
     */
    public function setContractRecordContractRecord(\RocketSeller\TwoPickBundle\Entity\ContractRecord $contractRecordContractRecord = null)
    {
        $this->contractRecordContractRecord = $contractRecordContractRecord;

        return $this;
    }

    /**
     * Get contractRecordContractRecord
     *
     * @return \RocketSeller\TwoPickBundle\Entity\ContractRecord
     */
    public function getContractRecordContractRecord()
    {
        return $this->contractRecordContractRecord;
    }
}
