<?php

namespace AppBundle\Entity;

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
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idPayMethod;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id_user")
     * })
     */
    private $userUser;

    /**
     * @var \AppBundle\Entity\PayType
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\PayType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pay_type_id_pay_type", referencedColumnName="id_pay_type")
     * })
     */
    private $payTypePayType;

    /**
     * @var \AppBundle\Entity\Bank
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Bank")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bank_id_bank", referencedColumnName="id_bank")
     * })
     */
    private $bankBank;

    /**
     * @var \AppBundle\Entity\AccountType
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\AccountType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="account_type_id_account_type", referencedColumnName="id_account_type")
     * })
     */
    private $accountTypeAccountType;



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
     * @param \AppBundle\Entity\User $userUser
     *
     * @return PayMethod
     */
    public function setUserUser(\AppBundle\Entity\User $userUser)
    {
        $this->userUser = $userUser;

        return $this;
    }

    /**
     * Get userUser
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserUser()
    {
        return $this->userUser;
    }

    /**
     * Set payTypePayType
     *
     * @param \AppBundle\Entity\PayType $payTypePayType
     *
     * @return PayMethod
     */
    public function setPayTypePayType(\AppBundle\Entity\PayType $payTypePayType)
    {
        $this->payTypePayType = $payTypePayType;

        return $this;
    }

    /**
     * Get payTypePayType
     *
     * @return \AppBundle\Entity\PayType
     */
    public function getPayTypePayType()
    {
        return $this->payTypePayType;
    }

    /**
     * Set bankBank
     *
     * @param \AppBundle\Entity\Bank $bankBank
     *
     * @return PayMethod
     */
    public function setBankBank(\AppBundle\Entity\Bank $bankBank)
    {
        $this->bankBank = $bankBank;

        return $this;
    }

    /**
     * Get bankBank
     *
     * @return \AppBundle\Entity\Bank
     */
    public function getBankBank()
    {
        return $this->bankBank;
    }

    /**
     * Set accountTypeAccountType
     *
     * @param \AppBundle\Entity\AccountType $accountTypeAccountType
     *
     * @return PayMethod
     */
    public function setAccountTypeAccountType(\AppBundle\Entity\AccountType $accountTypeAccountType)
    {
        $this->accountTypeAccountType = $accountTypeAccountType;

        return $this;
    }

    /**
     * Get accountTypeAccountType
     *
     * @return \AppBundle\Entity\AccountType
     */
    public function getAccountTypeAccountType()
    {
        return $this->accountTypeAccountType;
    }
}
