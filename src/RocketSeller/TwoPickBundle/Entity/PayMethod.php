<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PayMethod
 *
 * @ORM\Table(name="pay_method", indexes={@ORM\Index(name="fk_pay_method_user1", columns={"user_id_user"}), @ORM\Index(name="fk_pay_method_pay_type1", columns={"pay_type_id_pay_type"}), @ORM\Index(name="fk_pay_method_account_type1", columns={"account_type_id_account_type"}), @ORM\Index(name="fk_pay_method_bank1", columns={"bank_id_bank"})})
 * @ORM\Entity
 */
class PayMethod
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_pay_method", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPayMethod;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     */
    private $userUser;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PayType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PayType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pay_type_id_pay_type", referencedColumnName="id_pay_type")
     * })
     */
    private $payTypePayType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Bank
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Bank")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bank_id_bank", referencedColumnName="id_bank")
     * })
     */
    private $bankBank;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\AccountType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\AccountType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="account_type_id_account_type", referencedColumnName="id_account_type")
     * })
     */
    private $accountTypeAccountType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Frequency
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Frequency")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="frequency_id_frequency", referencedColumnName="id_frequency")
     * })
     */
    private $frequencyFrequency;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $accountNumber;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cellPhone;





    /**
     * Set idPayMethod
     *
     * @param integer $idPayMethod
     *
     * @return PayMethod
     */
    public function setIdPayMethod($idPayMethod)
    {
        $this->idPayMethod = $idPayMethod;

        return $this;
    }

    /**
     * Get idPayMethod
     *
     * @return integer
     */
    public function getIdPayMethod()
    {
        return $this->idPayMethod;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return PayMethod
     */
    public function setUserUser(\RocketSeller\TwoPickBundle\Entity\User $userUser)
    {
        $this->userUser = $userUser;

        return $this;
    }

    /**
     * Get userUser
     *
     * @return \RocketSeller\TwoPickBundle\Entity\User
     */
    public function getUserUser()
    {
        return $this->userUser;
    }

    /**
     * Set payTypePayType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayType $payTypePayType
     *
     * @return PayMethod
     */
    public function setPayTypePayType(\RocketSeller\TwoPickBundle\Entity\PayType $payTypePayType)
    {
        $this->payTypePayType = $payTypePayType;

        return $this;
    }

    /**
     * Get payTypePayType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PayType
     */
    public function getPayTypePayType()
    {
        return $this->payTypePayType;
    }

    /**
     * Set bankBank
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Bank $bankBank
     *
     * @return PayMethod
     */
    public function setBankBank(\RocketSeller\TwoPickBundle\Entity\Bank $bankBank)
    {
        $this->bankBank = $bankBank;

        return $this;
    }

    /**
     * Get bankBank
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Bank
     */
    public function getBankBank()
    {
        return $this->bankBank;
    }

    /**
     * Set accountTypeAccountType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\AccountType $accountTypeAccountType
     *
     * @return PayMethod
     */
    public function setAccountTypeAccountType(\RocketSeller\TwoPickBundle\Entity\AccountType $accountTypeAccountType)
    {
        $this->accountTypeAccountType = $accountTypeAccountType;

        return $this;
    }

    /**
     * Get accountTypeAccountType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\AccountType
     */
    public function getAccountTypeAccountType()
    {
        return $this->accountTypeAccountType;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payMethodFields = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set accountNumber
     *
     * @param string $accountNumber
     *
     * @return PayMethod
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * Get accountNumber
     *
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Set cellPhone
     *
     * @param integer $cellPhone
     *
     * @return PayMethod
     */
    public function setCellPhone($cellPhone)
    {
        $this->cellPhone = $cellPhone;

        return $this;
    }

    /**
     * Get cellPhone
     *
     * @return integer
     */
    public function getCellPhone()
    {
        return $this->cellPhone;
    }

    


    /**
     * Set frequencyFrequency
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Frequency $frequencyFrequency
     *
     * @return PayMethod
     */
    public function setFrequencyFrequency(\RocketSeller\TwoPickBundle\Entity\Frequency $frequencyFrequency = null)
    {
        $this->frequencyFrequency = $frequencyFrequency;

        return $this;
    }

    /**
     * Get frequencyFrequency
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Frequency
     */
    public function getFrequencyFrequency()
    {
        return $this->frequencyFrequency;
    }
}
