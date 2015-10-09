<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmployerHasEntity
 *
 * @ORM\Table(name="employer_has_entity", indexes={@ORM\Index(name="fk_employer_has_entity_employer1", columns={"employer_id_employer"}), @ORM\Index(name="fk_employer_has_entity_entity1", columns={"entity_id_entity"})})
 * @ORM\Entity
 */
class EmployerHasEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_employer_has_entity", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idEmployerHasEntity;

    /**
     * @var \AppBundle\Entity\Entity
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Entity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id_entity", referencedColumnName="id_entity")
     * })
     */
    private $entityEntity;

    /**
     * @var \AppBundle\Entity\Employer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Employer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_id_employer", referencedColumnName="id_employer")
     * })
     */
    private $employerEmployer;



    /**
     * Set idEmployerHasEntity
     *
     * @param integer $idEmployerHasEntity
     *
     * @return EmployerHasEntity
     */
    public function setIdEmployerHasEntity($idEmployerHasEntity)
    {
        $this->idEmployerHasEntity = $idEmployerHasEntity;

        return $this;
    }

    /**
     * Get idEmployerHasEntity
     *
     * @return integer
     */
    public function getIdEmployerHasEntity()
    {
        return $this->idEmployerHasEntity;
    }

    /**
     * Set entityEntity
     *
     * @param \AppBundle\Entity\Entity $entityEntity
     *
     * @return EmployerHasEntity
     */
    public function setEntityEntity(\AppBundle\Entity\Entity $entityEntity)
    {
        $this->entityEntity = $entityEntity;

        return $this;
    }

    /**
     * Get entityEntity
     *
     * @return \AppBundle\Entity\Entity
     */
    public function getEntityEntity()
    {
        return $this->entityEntity;
    }

    /**
     * Set employerEmployer
     *
     * @param \AppBundle\Entity\Employer $employerEmployer
     *
     * @return EmployerHasEntity
     */
    public function setEmployerEmployer(\AppBundle\Entity\Employer $employerEmployer)
    {
        $this->employerEmployer = $employerEmployer;

        return $this;
    }

    /**
     * Get employerEmployer
     *
     * @return \AppBundle\Entity\Employer
     */
    public function getEmployerEmployer()
    {
        return $this->employerEmployer;
    }
}
