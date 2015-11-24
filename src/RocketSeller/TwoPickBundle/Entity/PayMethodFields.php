<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PayMethodFields
 *
 * @ORM\Table(name="pay_method_fields")
 * @ORM\Entity
 */
class PayMethodFields
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_pay_method_fields", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPayMethodFields;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PayType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PayType", inversedBy="payMethodFields")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pay_type_id_pay_type", referencedColumnName="id_pay_type")
     * })
     */
    private $payTypePayType;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $columnName;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $dataType;


    /**
     * Get idPayMethodFields
     *
     * @return integer
     */
    public function getIdPayMethodFields()
    {
        return $this->idPayMethodFields;
    }

    /**
     * Set columnName
     *
     * @param string $columnName
     *
     * @return PayMethodFields
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * Get columnName
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Set payMethodPayMethod
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayMethod $payMethodPayMethod
     *
     * @return PayMethodFields
     */
    public function setPayMethodPayMethod(\RocketSeller\TwoPickBundle\Entity\PayMethod $payMethodPayMethod = null)
    {
        $this->payMethodPayMethod = $payMethodPayMethod;

        return $this;
    }

    /**
     * Get payMethodPayMethod
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PayMethod
     */
    public function getPayMethodPayMethod()
    {
        return $this->payMethodPayMethod;
    }

    /**
     * Set payTypePayType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayType $payTypePayType
     *
     * @return PayMethodFields
     */
    public function setPayTypePayType(\RocketSeller\TwoPickBundle\Entity\PayType $payTypePayType = null)
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
     * Set dataType
     *
     * @param string $dataType
     *
     * @return PayMethodFields
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get dataType
     *
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }
}
