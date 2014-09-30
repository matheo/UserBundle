<?php

namespace Matheo\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\User as FOSUser;
use Symfony\Component\Security\Core\Role\RoleInterface;


/**
 * Group
 *
 * @ORM\Entity
 * @ORM\Table(name="group")
 */
class Group implements GroupInterface
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    protected $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Rol", inversedBy="groups", cascade={"persist"})
     * @ORM\JoinTable(name="group_rol",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="rol_id", referencedColumnName="id")}
     * )
     */
    protected $roles;



    /**
     * @param string               $name
     * @param ArrayCollection|null $roles
     */
    public function __construct($name, $roles = null)
    {
        $this->name  = $name;
        $this->roles = new ArrayCollection();

        if ($roles) {
            foreach ($roles as $role) {
                $this->addRole($role);
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ?: '';
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $role
     *
     * @return $this
     */
    public function addRole($role)
    {
        !($role instanceof Rol) && $role = new Rol($role);

        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * @param string $rol
     *
     * @return $this
     */
    public function removeRole($role)
    {
        $role = $this->roles->filter(
            function(Rol $r) use ($role) {
                if ($role instanceof Rol) {
                    return $r->getRole() === $role->getRole();
                } else {
                    return $r->getRole() === strtoupper($role);
                }
            }
        )->first();

        if ($role) {
            $this->roles->removeElement($role);
        }

        return $this;
    }

    /**
     * @param string|RoleInterface $role
     *
     * @return boolean
     */
    public function hasRole($role)
    {
        foreach ($this->roles as $role) {
            if ($role == ($role instanceof RoleInterface ? $role->getRole() : (string)$role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles(array $roles)
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * @return array|ArrayCollection
     */
    public function getRoles()
    {
        if (!$this->roles->count()) {
            return [FOSUser::ROLE_DEFAULT];
        }

        $roles = [];

        foreach ($this->roles->toArray() as $role) {
            $roles[] = $role instanceof RoleInterface ? $role->getRole() : (string) $role;
        }

        return array_filter($roles);
    }

    /**
     * @param ArrayCollection $roles
     *
     * @return $this
     */
    public function setRolesCollection(ArrayCollection $roles)
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRolesCollection()
    {
        return $this->roles;
    }
}
