<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProcedureType
 *
 * @ORM\Table(name="procedure_type")
 * @ORM\Entity
 */
class ProcedureType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_procedure_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idProcedureType;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @var string $code
     *
     * @ORM\Column(type="string", length=4)
     */
    private $code;

    /**
     * Get idProcedureType
     *
     * @return integer
     */
    public function getIdProcedureType()
    {
        return $this->idProcedureType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ProcedureType
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
     * Set code
     *
     * @param string $code
     *
     * @return ProcedureType
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
