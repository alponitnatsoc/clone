<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role
 *
 * @ORM\Table(name="role")
 * @ORM\Entity
 */
class Role
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_role", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idRole;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="RoleHasTask", mappedBy="roleRole", cascade={"persist"})
     */
    private $roleHasTask;



    /**
     * Get idRole
     *
     * @return integer
     */
    public function getIdRole()
    {
        return $this->idRole;
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
        $this->roleHasTask = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add roleHasTask
     *
     * @param \RocketSeller\TwoPickBundle\Entity\RoleHasTask $roleHasTask
     *
     * @return Role
     */
    public function addRoleHasTask(\RocketSeller\TwoPickBundle\Entity\RoleHasTask $roleHasTask)
    {
        $this->roleHasTask[] = $roleHasTask;

        return $this;
    }

    /**
     * Remove roleHasTask
     *
     * @param \RocketSeller\TwoPickBundle\Entity\RoleHasTask $roleHasTask
     */
    public function removeRoleHasTask(\RocketSeller\TwoPickBundle\Entity\RoleHasTask $roleHasTask)
    {
        $this->roleHasTask->removeElement($roleHasTask);
    }

    /**
     * Get roleHasTask
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoleHasTask()
    {
        return $this->roleHasTask;
    }
}
