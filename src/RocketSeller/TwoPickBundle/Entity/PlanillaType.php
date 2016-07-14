<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlanillaType
 *
 * @ORM\Table(name="planilla_type")
 * @ORM\Entity
 */
class PlanillaType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_planilla_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPlanillaType;

    /**
     * @var string $code
     *
     * @ORM\Column(type="string", length=10)
     */
    private $code;

    /**
     * @var string $description
     *
     * @ORM\Column(type="string", length=200)
     */
    private $description;


    /**
     * Get idPlanillaType
     *
     * @return integer
     */
    public function getIdPlanillaType()
    {
        return $this->idPlanillaType;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return PlanillaType
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

    /**
     * Set description
     *
     * @param string $description
     *
     * @return PlanillaType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
