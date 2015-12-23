<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Task
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Task
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_task", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idTask;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="RoleHasTask", mappedBy="taskTask", cascade={"persist"})
     */
    private $taskHasRole;


    /**
     * Get id
     *
     * @return integer
     */
    public function getIdTask()
    {
        return $this->idTask;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Task
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
        $this->taskHasRole = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add taskHasRole
     *
     * @param \RocketSeller\TwoPickBundle\Entity\RoleHasTask $taskHasRole
     *
     * @return Task
     */
    public function addTaskHasRole(\RocketSeller\TwoPickBundle\Entity\RoleHasTask $taskHasRole)
    {
        $this->taskHasRole[] = $taskHasRole;

        return $this;
    }

    /**
     * Remove taskHasRole
     *
     * @param \RocketSeller\TwoPickBundle\Entity\RoleHasTask $taskHasRole
     */
    public function removeTaskHasRole(\RocketSeller\TwoPickBundle\Entity\RoleHasTask $taskHasRole)
    {
        $this->taskHasRole->removeElement($taskHasRole);
    }

    /**
     * Get taskHasRole
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTaskHasRole()
    {
        return $this->taskHasRole;
    }
}
