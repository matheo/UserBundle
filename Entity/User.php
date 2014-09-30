<?php

namespace Matheo\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User as CoreUser;
use Symfony\Component\Security\Core\Role\RoleInterface;


/**
 * User
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\HasLifecycleCallbacks
 */
class User extends BaseUser implements CoreUser\EquatableInterface
{
    use TimestampableEntity;

    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(name="group_user",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var string
     *
     * @ORM\Column(name="realname", type="string", length=255, nullable=true)
     */
    protected $realName;

    /**
     * @var string
     *
     * @ORM\Column(name="profilepicture", type="string", length=255, nullable=true)
     */
    protected $profilePicture;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=100, nullable=true)
     */
    protected $address;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_uid", type="string", length=255, nullable=true)
     */
    protected $facebookUid;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_token", type="string", length=255, nullable=true)
     */
    protected $facebookToken;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_data", type="array", nullable=true)
     */
    protected $facebookData;

    /**
     * @var string
     *
     * @ORM\Column(name="gplus_uid", type="string", length=255, nullable=true)
     */
    protected $gplusUid;

    /**
     * @var string
     *
     * @ORM\Column(name="gplus_token", type="string", length=255, nullable=true)
     */
    protected $gplusToken;

    /**
     * @var string
     *
     * @ORM\Column(name="gplus_data", type="array", nullable=true)
     */
    protected $gplusData;



    public function __construct()
    {
        parent::__construct();

        // trait initialization
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getRealName() ?: $this->getUsername();
    }

    /**
     * @param CoreUser\UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(CoreUser\UserInterface $user)
    {
        /** @var User $user */
        if ($user instanceof CoreUser\UserInterface) {
            // check that the groups (roles) are the same, in any order
            $isEqual = count($this->getGroups()) == count($user->getGroups());

            if ($isEqual) {
                /** @var Group $group */
                foreach ($this->getGroups() as $group) {
                    $isEqual = $isEqual && $user->hasGroup($group->getName());
                }
            }

            return $isEqual;
        }

        return false;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $realName
     *
     * @return $this
     */
    public function setRealName($realName)
    {
        $this->realName = $realName;

        return $this;
    }

    /**
     * @return string
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * @param string $picture
     *
     * @return $this
     */
    public function setProfilePicture($picture)
    {
        $this->profilePicture = $picture;

        return $this;
    }

    /**
     * @return string
     */
    public function getProfilePicture()
    {
        return $this->profilePicture;
    }

    /**
     * @param string $address
     *
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $phone
     *
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }



    /* SOCIAL */

    /**
     * @param string $facebookUid
     *
     * @return $this
     */
    public function setFacebookUid($facebookUid)
    {
        $this->facebookUid = $facebookUid;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookUid()
    {
        return $this->facebookUid;
    }

    /**
     * @param string $facebookData
     *
     * @return $this
     */
    public function setFacebookData($facebookData)
    {
        $this->facebookData = $facebookData;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookData()
    {
        return $this->facebookData;
    }

    /**
     * @param string $facebookToken
     *
     * @return $this
     */
    public function setFacebookToken($facebookToken)
    {
        $this->facebookToken = $facebookToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookToken()
    {
        return $this->facebookToken;
    }

    /**
     * @param string $googleId
     *
     * @return $this
     */
    public function setGoogleUid($googleId)
    {
        $this->gplusUid = $googleId;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleUid()
    {
        return $this->gplusUid;
    }

    /**
     * @param string $googleToken
     *
     * @return $this
     */
    public function setGoogleToken($googleToken)
    {
        $this->gplusToken = $googleToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleToken()
    {
        return $this->gplusToken;
    }

    /**
     * @param string $googleData
     *
     * @return $this
     */
    public function setGoogleData($googleData)
    {
        $this->gplusData = $googleData;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleData()
    {
        return $this->gplusData;
    }



    /* RELATIONS */

    /**
     * @param ArrayCollection $groups
     */
    public function setGroups($groups)
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }
    }

    /**
     * Gets the groups granted to the user.
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        if (!count($this->roles)) {
            return [parent::ROLE_DEFAULT];
        }

        return $this->roles;
    }

    /**
     * @return $this
     */
    public function loadRoles()
    {
        $this->roles = [];

        /** @var Group $group */
        foreach ($this->getGroups() as $group) {
            foreach ($group->getRoles() as $role) {
                $this->roles[] = ($role instanceof RoleInterface ? $role->getRole() : (string)$role);
            }
        }

        $this->roles[] = parent::ROLE_DEFAULT;

        return $this;
    }

    /**
     * @param string $rol
     *
     * @return boolean
     */
    public function hasRole($rol)
    {
        return in_array(strtoupper($rol), $this->getRoles(), true);
    }
}
