<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PayType
 *
 * @ORM\Table(name="pay_type")
 * @ORM\Entity
 */
class PayType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_pay_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPayType;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="PayMethodFields", mappedBy="payTypePayType", cascade={"persist"})
     */
    private $payMethodFields;

    /**
     * Get idPayType
     *
     * @return integer
     */
    public function getIdPayType()
    {
        return $this->idPayType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return PayType
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
     * Constructor
     */
    public function __construct()
    {
        $this->payMethodFields = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add payMethodField
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayMethodFields $payMethodField
     *
     * @return PayType
     */
    public function addPayMethodField(\RocketSeller\TwoPickBundle\Entity\PayMethodFields $payMethodField)
    {
        $this->payMethodFields[] = $payMethodField;

        return $this;
    }

    /**
     * Remove payMethodField
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayMethodFields $payMethodField
     */
    public function removePayMethodField(\RocketSeller\TwoPickBundle\Entity\PayMethodFields $payMethodField)
    {
        $this->payMethodFields->removeElement($payMethodField);
    }

    /**
     * Get payMethodFields
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayMethodFields()
    {
        return $this->payMethodFields;
    }
}
