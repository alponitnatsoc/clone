<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoleHasTask
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class RoleHasTask
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_role_has_task", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Role
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Role", inversedBy="roleHasTask")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id_role", referencedColumnName="id_role")
     * })
     */
    private $roleRole;


    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Task
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Task", inversedBy="roleHasTask")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="task_id_task", referencedColumnName="id_task")
     * })
     */
    private $taskTask;




    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set roleRole
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Role $roleRole
     *
     * @return RoleHasTask
     */
    public function setRoleRole(\RocketSeller\TwoPickBundle\Entity\Role $roleRole = null)
    {
        $this->roleRole = $roleRole;

        return $this;
    }

    /**
     * Get roleRole
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Role
     */
    public function getRoleRole()
    {
        return $this->roleRole;
    }

    /**
     * Set taskTask
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Task $taskTask
     *
     * @return RoleHasTask
     */
    public function setTaskTask(\RocketSeller\TwoPickBundle\Entity\Task $taskTask = null)
    {
        $this->taskTask = $taskTask;

        return $this;
    }

    /**
     * Get taskTask
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Task
     */
    public function getTaskTask()
    {
        return $this->taskTask;
    }
}
