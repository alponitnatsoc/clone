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
     * @var \RocketSeller\TwoPickBundle\Entity\PayMethod
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PayMethod", inversedBy="payMethodFields")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pay_method_id_pay_method", referencedColumnName="id_pay_method")
     * })
     */
    private $payMethodPayMethod;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $columnName;



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
}
