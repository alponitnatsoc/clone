<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Procedure
 *
 * @ORM\Table(name="procedure", indexes={@ORM\Index(name="fk_procedure_procedure_type1", columns={"procedure_type_id_procedure_type"}), @ORM\Index(name="fk_procedure_user1", columns={"user_id_user"}), @ORM\Index(name="fk_procedure_employer1", columns={"employer_id_employer"})})
 * @ORM\Entity
 */
class Procedure
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_procedure", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idProcedure;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id_user")
     * })
     */
    private $userUser;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\ProcedureType
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\ProcedureType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="procedure_type_id_procedure_type", referencedColumnName="id_procedure_type")
     * })
     */
    private $procedureTypeProcedureType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Employer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_id_employer", referencedColumnName="id_employer")
     * })
     */
    private $employerEmployer;



    /**
     * Set idProcedure
     *
     * @param integer $idProcedure
     *
     * @return Procedure
     */
    public function setIdProcedure($idProcedure)
    {
        $this->idProcedure = $idProcedure;

        return $this;
    }

    /**
     * Get idProcedure
     *
     * @return integer
     */
    public function getIdProcedure()
    {
        return $this->idProcedure;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return Procedure
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
     * Set procedureTypeProcedureType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ProcedureType $procedureTypeProcedureType
     *
     * @return Procedure
     */
    public function setProcedureTypeProcedureType(\RocketSeller\TwoPickBundle\Entity\ProcedureType $procedureTypeProcedureType)
    {
        $this->procedureTypeProcedureType = $procedureTypeProcedureType;

        return $this;
    }

    /**
     * Get procedureTypeProcedureType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\ProcedureType
     */
    public function getProcedureTypeProcedureType()
    {
        return $this->procedureTypeProcedureType;
    }

    /**
     * Set employerEmployer
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Employer $employerEmployer
     *
     * @return Procedure
     */
    public function setEmployerEmployer(\RocketSeller\TwoPickBundle\Entity\Employer $employerEmployer)
    {
        $this->employerEmployer = $employerEmployer;

        return $this;
    }

    /**
     * Get employerEmployer
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Employer
     */
    public function getEmployerEmployer()
    {
        return $this->employerEmployer;
    }
}
