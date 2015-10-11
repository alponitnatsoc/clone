<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserHasRole
 *
 * @ORM\Table(name="user_has_role", indexes={@ORM\Index(name="fk_user_has_role_user1", columns={"user_id_user"}), @ORM\Index(name="fk_user_has_role_role1", columns={"role_id_role"})})
 * @ORM\Entity
 */
class UserHasRole
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_user_has_role", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUserHasRole;

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
     * @var \RocketSeller\TwoPickBundle\Entity\Role
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id_role", referencedColumnName="id_role")
     * })
     */
    private $roleRole;



    /**
     * Set idUserHasRole
     *
     * @param integer $idUserHasRole
     *
     * @return UserHasRole
     */
    public function setIdUserHasRole($idUserHasRole)
    {
        $this->idUserHasRole = $idUserHasRole;

        return $this;
    }

    /**
     * Get idUserHasRole
     *
     * @return integer
     */
    public function getIdUserHasRole()
    {
        return $this->idUserHasRole;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return UserHasRole
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
     * Set roleRole
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Role $roleRole
     *
     * @return UserHasRole
     */
    public function setRoleRole(\RocketSeller\TwoPickBundle\Entity\Role $roleRole)
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
}
