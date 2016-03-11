<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bank
 *
 * @ORM\Table(name="bank")
 * @ORM\Entity
 */
class Bank
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_bank", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idBank;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $novopaymentCode;

    /**
     * Get idBank
     *
     * @return integer
     */
    public function getIdBank()
    {
        return $this->idBank;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Bank
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
     * Set novopaymentCode
     *
     * @param string $name
     *
     * @return Bank
     */
    public function setNovopaymentCode($novopaymentCode)
    {
        $this->novopaymentCode = $novopaymentCode;

        return $this;
    }

    /**
     * Get novopaymentCode
     *
     * @return string
     */
    public function getNovopaymentCode()
    {
        return $this->novopaymentCode;
    }
}
