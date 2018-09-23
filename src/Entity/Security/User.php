<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 18.08
 */
namespace App\Entity\Security;

use App\Entity\Clients\MobileApp;
use App\Entity\Communication\Message;
use App\Entity\Communication\MessageRecipient;
use App\Entity\Interfaces\Serializable;
use App\Entity\Traits\CloneableTrait;
use App\Entity\Traits\ContainsMessageTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\Traits\ActiveTrait;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Traits\PersonTrait;
use App\Entity\Traits\HiddenFieldsTrait;
use Doctrine\Common\Collections\Criteria;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Serializer\Annotation as Serialize;
use JMS\Serializer\Annotation as Jms;

/**
 * User account for the app.
 *
 * @ORM\Table(name="user_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_fld", type="string")
 * @ORM\DiscriminatorMap({
 *     "user" = "User",
 *     "school_manager" = "SchoolManager",
 *     "school_admin" = "SchoolAdministrator",
 *     "system_user" = "SystemUser",
 * })
 * @UniqueEntity(fields="username", message="email.reserved")
 * @UniqueEntity(fields="phone", message="phone.reserved")
 * @ORM\HasLifecycleCallbacks
 * @Jms\ExclusionPolicy("NONE")
 * @package App\Entity\Security
 * @author Robert Jürgens <robert@jurgens.fi>
 */
class User implements AdvancedUserInterface, Serializable, UserInterface
{
    /** 
     * Use is active flag
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     */
    use ActiveTrait;

    /** Use clone trait */
    use CloneableTrait;

    /** 
     * Use hidden fields trait
     * @Serialize\Groups({"Authenticated", "Admin"})
     * @Jms\Groups({"Authenticated", "Admin"})
     */
    use HiddenFieldsTrait;

    /** 
     * Use persistency data such as id and timestamps
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     */
    use PersistencyDataTrait;

    /** 
     * Use person trait
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     */
    use PersonTrait;

    /** 
     * Can contain a message
     * @Jms\Exclude()
     */
    use ContainsMessageTrait;

    /**
     * @ORM\Column(name="username_fld", type="string", length=60, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var string The user's username, also the primary e-mail address
     */
    protected $username;

    /**
     * @ORM\Column(name="email_fld", type="string", length=60, nullable=true)
     * @Assert\Email()
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var string|null The user's secondary e-mail address, used when resetting forgotten passwords
     */
    protected $email;

    /**
     * @ORM\Column(name="emailhash_fld", type="string", length=64, nullable=true)
     * @Serialize\Groups({"Login"})
     * @Jms\Groups({"Login"})
     * @var string|null A security measure for validating the username (primary e-mail address) when registering
     *                  new users
     */
    protected $emailhash;

    /**
     * @ORM\Column(name="phone_fld", type="string", length=60, nullable=true, unique=true)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var string|null The user's cell phone number
     */
    protected $phone;

    /**
     * @ORM\Column(name="phonehash_fld", type="string", length=64, nullable=true)
     * @Serialize\Groups({"Login"})
     * @Jms\Groups({"Login"})
     * @var string|null A security measure for validating the user's cell phone number when registering new users
     */
    protected $phonehash;

    /**
     * @ORM\Column(name="password_fld", type="string", length=64, nullable=true)
     * @Serialize\Groups({"Login", "Authenticated", "Admin"})
     * @Jms\Groups({"Login", "Authenticated", "Admin"})
     * @var string The user's password (encrypted)
     */
    protected $password;

    /**
     * @ORM\Column(name="blocked_fld", type="boolean", nullable=true)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var bool|null A field to block a user
     */
    protected $isBlocked = false;

    /**
     * @ORM\Column(name="consented_fld", type="boolean", nullable=false)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var bool|null A field to signify consent was given to register by a user
     */
    protected $consented = false;

    /**
     * @ORM\Column(name="password_valid_for_fld", type="integer", nullable=false)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var integer For how many logins is the password valid for (-1 is forever, 0 signals that the password needs
     *              to be changed)
     */
    protected $passwordValidFor;

    /**
     * @ORM\Column(name="locale_fld", type="string", length=2, nullable=true)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var string|null The user's locale (language)
     */
    protected $locale = 'sv';

    /**
     * @Assert\Length(min=4)
     * @Assert\Length(max=32)
     * @Jms\Exclude()
     * @var string|null The user's password in plain text (only stored temporarily until the entity is persisted,
     *                  not stored in the database)
     */
    protected $plainPassword;

    /**
     * @ORM\Column(name="logincount_fld", type="integer", nullable=true, options={"default" : 0})
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var integer|null The number of times the user has logged on to the system
     */
    protected $logins = 1;

    /**
     * @ORM\Column(name="lastlogin_fld", type="datetime", nullable=true)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var \DateTime|null The timestamp of the user's last login
     */
    protected $lastLogin;

    /**
     * @ORM\Column(name="facebook_id_fld", type="string", length=255, nullable=true)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var string $facebookId The Facebook user's id
     */
    protected $facebookId;

    /**
     * @ORM\Column(name="facebook_access_token_fld", type="string", length=255, nullable=true)
     * @Serialize\Groups({"Login", "Authenticated", "Admin"})
     * @Jms\Groups({"Login", "Authenticated", "Admin"})
     * @var string $facebookAccessToken The Facebook user's access token
     */
    protected $facebookAccessToken;

    /**
     * @ORM\Column(name="googleplus_id_fld", type="string", length=255, nullable=true)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var string $googleplusId The Google user's id
     */
    protected $googleplusId;

    /**
     * @ORM\Column(name="googleplus_access_token_fld", type="string", length=255, nullable=true)
     * @Serialize\Groups({"Login", "Authenticated", "Admin"})
     * @Jms\Groups({"Login", "Authenticated", "Admin"})
     * @var string $googleplusAccessToken The Google user's access token
     */
    protected $googleplusAccessToken;

    /**
     * @ORM\Column(name="twitter_id_fld", type="string", length=255, nullable=true)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var string $twitterId The Twitter user's id
     */
    protected $twitterId;

    /**
     * @ORM\Column(name="twitter_access_token_fld", type="string", length=255, nullable=true)
     * @Serialize\Groups({"Login", "Authenticated", "Admin"})
     * @Jms\Groups({"Login", "Authenticated", "Admin"})
     * @var string $twitterAccessToken The Twitter user's access token
     */
    protected $twitterAccessToken;

    /**
     * @ORM\Column(name="instagram_id_fld", type="string", length=255, nullable=true)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     */
    protected $instagramId;

    /**
     * @ORM\Column(name="instagram_access_token_fld", type="string", length=255, nullable=true)
     * @Serialize\Groups({"Login", "Authenticated", "Admin"})
     * @Jms\Groups({"Login", "Authenticated", "Admin"})
     */
    protected $instagramAccessToken;

    /**
     * @ORM\Column(name="roles_fld", type="array", nullable=false)
     * @Serialize\Groups({"Default"})
     * @Jms\Groups({"Default"})
     * @var array $groups The groups of the user.
     */
    protected $roles;

    /**
     * @var RoleHierarchyInterface $roleHierarchy the role hierarchy (groups are recursive)
     * @Jms\Exclude()
     */
    public static $roleHierarchy;

    /**
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users", cascade={"persist" ,"merge"})
     * @ORM\JoinTable(name="user_has_group_table",
     *     joinColumns={@ORM\JoinColumn(name="user_fld", referencedColumnName="id_fld")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_fld", referencedColumnName="id_fld")}
     *     )
     * @ORM\OrderBy({"name" = "ASC"})
     * @Serialize\Groups({"Default"})
     * @Serialize\MaxDepth(2)
     * @Jms\Groups({"Default"})
     * @Jms\MaxDepth(2)
     * @var ArrayCollection The user's groups
     */
    protected $groups;

    /**
     * @ORM\OneToMany(targetEntity="UserLogEvent", mappedBy="user", cascade={"persist", "merge", "remove"})
     * @ORM\OrderBy({"timestamp" = "DESC"})
     * @Jms\Exclude()
     * @var ArrayCollection Logs that belongs to this user
     */
    protected $logEvents;

    /**
     * @ORM\OneToMany(targetEntity="UserTicket", mappedBy="user", cascade={"persist", "merge", "remove"})
     * @Jms\Exclude()
     * @ORM\OrderBy({"from" = "DESC"})
     * @var ArrayCollection Tickets that belongs to this user
     */
    protected $tickets;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Communication\MessageRecipient",
     *     mappedBy="user", cascade={"persist", "merge", "remove"})
     * @ORM\OrderBy({"createdAt" = "DESC"})
     * @Jms\Exclude()
     * @var ArrayCollection Messages that this user has received
     */
    protected $messages;

    /**
     * @ORM\OneToOne(targetEntity="UserProfile", inversedBy="user", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="profile_fld", referencedColumnName="id_fld", nullable=true)
     * @Jms\Exclude()
     * @var UserProfile $profile The user's profile
     */
    protected $profile;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Clients\MobileApp", inversedBy="user", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="mobile_app_fld", referencedColumnName="id_fld", nullable=true)
     * @Jms\Exclude()
     * @var MobileApp $mobileApp The mobile app if the user account is connected to one
     */
    protected $mobileApp;

    /**
     * User constructor.
     */
    public function __construct()
    {
        // Users are not active by default, requires email and phone verification
        $this->isActive = false;
        $this->groups = new ArrayCollection();
        $this->roles = [static::ROLE_DEFAULT];
        $this->logEvents = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        // make password valif for all time (default)
        $this->passwordValidFor = -1;
        $this->profile = new UserProfile();
        $this->profile->setUser($this);
    }

    /**
     * Gets a string representation of this entity.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFullname() .
            (($this->getHiddenFields() && in_array('username', $this->getHiddenFields())) ? null : ' <' . $this->getUsername() . '>');
    }

    /**
     * Retrieves a profile data value from the user's profile.
     *
     * @param string $path
     * @param mixed|null $default
     * @return mixed
     */
    public function getData($path, $default = null)
    {
        return $this->profile->getDataForPath($path, $default);
    }

    /**
     * Stores a profile data value to the user's profile.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value)
    {
        $data = $this->profile->getData();
        $data = array_merge_recursive($data, [$key => $value]);
        $this->getProfile()->setData($data);

        return $this;
    }

        /**
     * Gets the profile.
     *
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Sets the profile.
     *
     * @param mixed $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Gets the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Gets the salt.
     *
     * @return null
     */
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /**
     * Gets the encrypted password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Gets the groups this user belongs to.
     *
     * @param boolean $array Return value should be an array instead of an ArrayCollection
     * @return ArrayCollection|array
     */
    public function getGroups($array = true)
    {
        return $array ?
            $this->groups->toArray() :
            $this->groups;
    }

    /**
     * Erases all credentials (Not used but required by the interface)
     *
     * @return void
     */
    public function eraseCredentials()
    {

    }

    /**
     * All accounts are nonexpiring, always returns true.
     *
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Returns true if this user is not blocked by the owner or some administrator.
     *
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return !$this->isBlocked;
    }

    /**
     * Checks if the user has a role.
     *
     * @param $role string The role to check for
     * @return bool
     */
    public function isGranted($role)
    {
        return $this->hasRole($role);
    }

    /**
     * All credentials are nonexpiring, always returns true.
     *
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks if this useraccount is active.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * @see \Serializable::serialize()
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->email,
            $this->firstname,
            $this->lastname,
            $this->phone,
            $this->googleplusId,
            $this->googleplusAccessToken,
            $this->facebookId,
            $this->facebookAccessToken,
            $this->twitterId,
            $this->twitterAccessToken,
            $this->instagramId,
            $this->instagramAccessToken,
            $this->consented,
            $this->password,
            $this->isActive,
            // see section on salt below
            // $this->salt,
        ]);
    }

    /**
     * @see \Serializable::unserialize()
     *
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->email,
            $this->firstname,
            $this->lastname,
            $this->phone,
            $this->googleplusId,
            $this->googleplusAccessToken,
            $this->facebookId,
            $this->facebookAccessToken,
            $this->twitterId,
            $this->twitterAccessToken,
            $this->consented,
            $this->password,
            $this->isActive
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    /**
     * Set the username.
     *
     * @param string $username The user's username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Sets the password.
     *
     * @param string $password The user's password (encrypted)
     * @return $this
     */
    public function setPassword($password)
    {
        if (!empty($password))
            $this->password = $password;

        return $this;
    }

    /**
     * Sets the email.
     *
     * @param string $email The user's secondary email address
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets the email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns true if the user is blocked, false if the user is not blocked and null if this is undefined.
     *
     * @return mixed
     */
    public function getIsBlocked()
    {
        return $this->isBlocked;
    }

    /**
     * Sets the isBlocked flag.
     *
     * @param mixed $isBlocked Defines if the user is blocked or not
     * @return $this
     */
    public function setIsBlocked($isBlocked)
    {
        $this->isBlocked = $isBlocked;

        return $this;
    }

    /**
     * Gets the phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets the phone.
     *
     * @param string $phone The phone number of the user
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Gets the logins.
     *
     * @return integer
     */
    public function getLogins()
    {
        return $this->logins;
    }

    /**
     * Sets the logins.
     *
     * @param integer $logins The number of logins of this user
     * @return $this
     */
    public function setLogins($logins)
    {
        $this->logins = $logins;

        return $this;
    }

    /**
     * Increment logins by one. Creates a UserLogEvent of the login event.
     *
     * @param mixed $remoteHost The remote host where the login was performed from
     * @return $this
     */
    public function incrementLogins($remoteHost = null)
    {
        $this->logins++;
        // Create a log from the successful login
        $event = new UserLogEvent();
        $event->setType(UserLogEvent::TYPE_LOGIN)->setLevel(UserLogEvent::LEVEL_SUCCESS)
            ->setRemoteHost($remoteHost)->setUser($this)
            ->setTimestamp(new \DateTime('now'))->setResult('login.successful');
        if (!$this->logEvents)
            $this->logEvents = new ArrayCollection();
        $this->logEvents->add($event);
        return $this;
    }

    /**
     * Gets the lastLogin.
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Sets the lastLogin.
     *
     * @param \DateTime $lastLogin The timestamp of the last login
     *
     * @return $this
     */
    public function setLastLogin(\DateTime $lastLogin = null)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Gets the plainPassword.
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Sets the plainPassword.
     *
     * @param string $password The password of the user in plain text
     *
     * @return $this
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * Gets the emailhash.
     *
     * @return string
     */
    public function getEmailhash()
    {
        return $this->emailhash;
    }

    /**
     * Gets the phonehash.
     *
     * @return string
     */
    public function getPhonehash()
    {
        return $this->phonehash;
    }

    /**
     * Sets the emailhash.
     *
     * @param mixed $emailhash The code used to verify the user's primary e-mail address
     *
     * @return $this
     */
    public function setEmailhash($emailhash)
    {
        $this->emailhash = $emailhash;

        return $this;
    }

    /**
     * Sets the phonehash.
     *
     * @param mixed $phonehash The code used to verify the user's cell phone number
     *
     * @return $this
     */
    public function setPhonehash($phonehash)
    {
        $this->phonehash = $phonehash;

        return $this;
    }

    /**
     * Gets the locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the locale.
     *
     * @param string $locale The locale of the user
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }


    /**
     * Sets the groups.
     *
     * @param ArrayCollection|array $groups
     * @return $this
     */
    public function setGroups($groups)
    {
        $this->groups = is_array($groups) ?
            new ArrayCollection($groups) :
            $groups;
        return $this;
    }

    /**
     * Gets triggered only on insert
     *
     * @ORM\PrePersist
     * @return void
     */
    public function onUserPrePersist()
    {
        // If user was registered, create email and phone hashes
        if (!$this->isActive) {
            $this->phonehash = self::createHash(12);
            $this->emailhash = self::createHash(12);
        }
        $this->lastLogin = new \DateTime("now");
        $this->createdAt = new \DateTime("now");
    }

    /**
     * Adds a group.
     *
     * @param Group $group The group to be added
     * @param bool $cascade If true, add this user to the group as well
     *
     * @return $this
     */
    public function addGroup(Group $group, $cascade = true)
    {
        if ($this->groups->contains($group)) {
            return $this;
        }
        $this->groups->add($group);
        if ($cascade) $group->addUser($this, false);
        return $this;
    }

    /**
     * Removes a group.
     *
     * @param Group $group The group to be removed
     * @param bool $cascade If true, remove this user from the group as well
     *
     * @return $this
     */
    public function removeGroup(Group $group, $cascade = true)
    {
        if (!$this->groups->contains($group)) {
            return $this;
        }
        $this->groups->removeElement($group);
        if ($cascade) $group->removeUser($this, false);
        return $this;
    }

    /**
     * Checks if user has a group
     *
     * @param Group|integer|string $group The group to check for
     *
     * @return bool
     */
    public function hasGroup($group)
    {
        if (is_integer($group)) {
            foreach ($this->groups as $r)
                if ($r->getId() == $group)
                    return true;
            return false;
        } else if (is_string($group)) {
            foreach ($this->groups as $r) {
                if ($r->getEmail() == $group)
                    return true;
            }
            return false;
        } else if (!$group instanceof Group) {
            return false;
        }
        return $this->groups->contains($group);
    }

    /**
     * Gets the number of times the user can still log in with this password.
     *
     * @return integer
     */
    public function getPasswordValidFor()
    {
        return $this->passwordValidFor;
    }

    /**
     * Sets the number of times the user can still log in with this password.
     *
     * @param integer $passwordValidFor The number of times the password is still valid for
     * @return $this
     */
    public function setPasswordValidFor($passwordValidFor)
    {
        $this->passwordValidFor = $passwordValidFor;

        return $this;
    }

    /**
     * Gets the login events.
     *
     * @param string $type The type to filter for
     * @param array $sortOrder The sorting order to use
     * @return ArrayCollection
     */
    public function getLogEvents($type = 'LOGIN', $sortOrder = [])
    {
        $criteria = Criteria::create();

        if ($type)
            $criteria->where(Criteria::expr()->eq('type', $type));

        $order = [];
        foreach ($sortOrder as $key => $sort) {
            $order[$key] = ($sort == 'ASC' ? Criteria::ASC : Criteria::DESC);
        }
        $criteria->orderBy($order);

        return $this->logEvents->matching($criteria);
    }

    /**
     * Sets the login events.
     *
     * @param mixed $logEvents The log events to set
     * @return $this
     */
    public function setLogEvents($logEvents)
    {
        $this->logEvents = $logEvents;

        return $this;
    }

    /**
     * Logs an event.
     *
     * @param string $type
     * @param string $level
     * @param mixed $remoteHost
     * @param string $result
     * @return UserLogEvent
     */
    public function logEvent($type, $level, $remoteHost, $result)
    {
        $event = new UserLogEvent();
        $event->setType($type)->setLevel($level)
            ->setRemoteHost($remoteHost)->setUser($this)
            ->setTimestamp(new \DateTime('now'))->setResult($result);
        if (!$this->logEvents)
            $this->logEvents = new ArrayCollection();
        $this->logEvents->add($event);
        return $event;
    }

    /**
     * Gets the tickets.
     *
     * @return ArrayCollection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Sets the tickets.
     *
     * @param ArrayCollection $tickets
     * @return $this
     */
    public function setTickets(ArrayCollection $tickets)
    {
        $this->tickets = $tickets;

        return $this;
    }

    /**
     * Gets the messages.
     *
     * @param string $box The box name to filter for (special case: if box == map then return a map of all boxes
     *                    and their respective messages.)
     * @param string $sortKey The sorting key to use
     * @param string $order The sorting order to use
     * @return ArrayCollection
     */
    public function getMessages($box = null, $sortKey = null, $order = null)
    {

        $criteria = Criteria::create();
        if ($sortKey && $order) {
            if ($order == 'ASC')
                $order = Criteria::ASC;
            else
                $order = Criteria::DESC;
            $criteria->orderBy([$sortKey => $order]);
        }
        if (!$box)
            return $this->messages->matching($criteria);
        if ($box == 'map') {
            $map = new ArrayCollection();
            foreach ([MessageRecipient::BOX_INBOX, MessageRecipient::BOX_ARCHIVE, MessageRecipient::BOX_TRASH] as $key)
                $map->set($key, new ArrayCollection());
            /** @var MessageRecipient $msg */
            foreach ($this->messages->matching($criteria) as $msg) {
                if (in_array(Message::TYPE_INTERNAL, $msg->getMessage()->getType()))
                    $map->get($msg->getBox())->add($msg);
            }
            return $map;
        }

        $filtered = new ArrayCollection();
        foreach($this->messages as $msg) {
            if ($msg->getBox() == $box && in_array(Message::TYPE_INTERNAL, $msg->getMessage()->getType()))
                $filtered->add($msg);
        }
        return $filtered;
    }

    /**
     * Sets the messages.
     *
     * @param ArrayCollection $messages The messages to set
     * @return $this
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Gets all unread messages.
     *
     * @param string $box The box name to filter for (special case: if box == map then return a map of all boxes
     *                    and their respective messages.)
     * @return ArrayCollection
     */
    public function getUnreadMessages($box = null)
    {
        $unread = new ArrayCollection();
        if ($box == 'map') {
            foreach ([MessageRecipient::BOX_INBOX, MessageRecipient::BOX_ARCHIVE, MessageRecipient::BOX_TRASH] as $key)
                $unread->set($key, new ArrayCollection());
            $map = $this->getMessages($box);
            foreach ($map as $key => $box) {
                /** @var MessageRecipient $msg */
                foreach ($box as $msg) {
                    if (!$msg->getRead() && in_array(Message::TYPE_INTERNAL, $msg->getMessage()->getType()))
                        $unread->get($key)->add($msg);
                }
            }
        }
        $all = $this->getMessages($box);
        foreach ($all as $msg) {
            if (!$msg->getRead() && in_array(Message::TYPE_INTERNAL, $msg->getMessage()->getType()))
                $unread->add($msg);
        }
        return $unread;
    }

    /**
     * Gets all entities and fields that have to be badged.
     *
     * @return ArrayCollection
     */
    public function getBadges() {
        return new ArrayCollection(array_merge($this->getUnreadMessages()->toArray(),
            ($this->getPasswordValidFor() == 0 ? ['passwd'] : [])));
    }

    /**
     * Creates a random hash string.
     *
     * @param int $length
     * @return string
     */
    public static function createHash($length = 12) {
        return substr(md5(uniqid(rand(), true)), 0, $length);
    }

    /**
     * Gets a random string suitable for a password.
     *
     * @param int $len
     * @return string
     * @throws \Exception
     */
    public function getRandomString($len = 12) {
        return base64_encode(random_bytes($len / 4 * 3));
    }

    /**
     * Gets a User Security Entity fot this User.
     *
     * @return UserSecurityIdentity
     */
    public function getUserSecurityIdentity()
    {
        return UserSecurityIdentity::fromAccount($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserRoles(array $roles)
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addUserRole($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addUserRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeUserRole($role)
    {
        if (($key = array_search(strtoupper($role), $this->roles, true)) !== false) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUserRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = $this->getUserRoles();
        /** @var Group $group */
        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles(array $roles)
    {
        return $this->setUserRoles($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role)
    {
        return $this->addUserRole($role);
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role)
    {
        return $this->removeUserRole($role);
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role)
    {
        $roleObj = new Role($role);
        $reachableRoles = array_map(function($role) { return new Role($role); }, $this->getRoles());
        return in_array($roleObj, self::$roleHierarchy->getReachableRoles($reachableRoles));
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        return $this->setIsActive($enabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsernameCanonical()
    {
        return $this->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function setUsernameCanonical($usernameCanonical)
    {
        return $this->setUsername($usernameCanonical);
    }

    /**
     * {@inheritdoc}
     */
    public function setSalt($salt)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailCanonical()
    {
        return $this->getEmail();
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailCanonical($emailCanonical)
    {
        return $this->setEmail($emailCanonical);
    }

    /**
     * {@inheritdoc}
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('ROLE_SUPER_ADMIN');
    }


    /**
     * {@inheritdoc}
     */
    public function setSuperAdmin($boolean)
    {
        if ($boolean)
            return $this->addUserRole('ROLE_SUPER_ADMIN');
        else
            return $this->removeUserRole('ROLE_SUPER_ADMIN');
    }

    /**
     * {@inheritdoc}
     */
    public function isAdmin()
    {
        return $this->hasRole('ROLE_ADMIN');
    }


    /**
     * {@inheritdoc}
     */
    public function setAdmin($boolean)
    {
        if ($boolean)
            return $this->addUserRole('ROLE_ADMIN');
        else
            return $this->removeUserRole('ROLE_ADMIN');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationToken()
    {
        return $this->getEmailhash();
    }

    /**
     * {@inheritdoc}
     */
    public function setConfirmationToken($confirmationToken)
    {
        return $this->setEmailhash($confirmationToken);
    }

    /**
     * {@inheritdoc}
     */
    public function setPasswordRequestedAt(\DateTime $date = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordRequestNonExpired($ttl)
    {
        return ($this->getPasswordValidFor() == -1);
    }

    /**
     * Gets the consented field.
     *
     * @return bool|null
     */
    public function getConsented()
    {
        return $this->consented;
    }

    /**
     * Sets the consented field.
     *
     * @param bool|null $consented
     * @return $this
     */
    public function setConsented(bool $consented)
    {
        $this->consented = $consented;

        return $this;
    }

    /**
     * @return bool
     */
    public function anonymizationRequiresPassword()
    {
        return (
            $this->getPassword() !== $this->getGoogleplusId() &&
            $this->getPassword() !== $this->getFacebookId() &&
            $this->getPassword() !== $this->getTwitterId() &&
            $this->getPassword() !== $this->getInstagramId()
        );
    }

    /**
     * Anonymize the user to comply with GDPR.
     *
     * @return $this
     */
    public function anonymize()
    {
        $this
            ->setUsername("anonymized-{$this->getId()}.noreply@idrott.fi")
            ->setPhone("anonymized-{$this->getId()}")
            ->setFirstname('Anon')
            ->setLastname('User')
            ->setConsented(false)
            ->setIsActive(false)
            ->setIsBlocked(true)
            ->setEmail("anonymized-{$this->getId()}.noreply@idrott.fi")
            ->setGoogleplusId(null)
            ->setGoogleplusAccessToken(null)
            ->setFacebookId(null)
            ->setFacebookAccessToken(null)
            ->setTwitterId(null)
            ->setTwitterAccessToken(null)
            ->setInstagramId(null)
            ->setInstagramAccessToken(null)
            ->setLocale(null)
            ->setPasswordValidFor(0)
            ->setRoles([])
            ->setPassword('anonymized');

        return $this;
    }

    /**
     * Gets the facebookId.
     *
     * @return mixed
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Sets the facebookId.
     *
     * @param mixed $facebookId
     * @return $this
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * Gets the facebookAccessToken.
     *
     * @return mixed
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }

    /**
     * Sets the facebookAccessToken.
     *
     * @param mixed $facebookAccessToken
     * @return $this
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
    }

    /**
     * Gets the googleplusId.
     *
     * @return mixed
     */
    public function getGoogleplusId()
    {
        return $this->googleplusId;
    }

    /**
     * Sets the googleplusId.
     *
     * @param mixed $googleplusId
     * @return $this
     */
    public function setGoogleplusId($googleplusId)
    {
        $this->googleplusId = $googleplusId;

        return $this;
    }

    /**
     * Gets the googleplusAccessToken.
     *
     * @return mixed
     */
    public function getGoogleplusAccessToken()
    {
        return $this->googleplusAccessToken;
    }

    /**
     * Sets the googleplusAccessToken.
     *
     * @param mixed $googleplusAccessToken
     * @return $this
     */
    public function setGoogleplusAccessToken($googleplusAccessToken)
    {
        $this->googleplusAccessToken = $googleplusAccessToken;

        return $this;
    }

    /**
     * Gets the twitterId.
     *
     * @return mixed
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * Sets the twitterId.
     *
     * @param mixed $twitterId
     * @return $this
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;

        return $this;
    }

    /**
     * Gets the twitterAccessToken.
     *
     * @return mixed
     */
    public function getTwitterAccessToken()
    {
        return $this->twitterAccessToken;
    }

    /**
     * Sets the twitterAccessToken.
     *
     * @param mixed $twitterAccessToken
     * @return $this
     */
    public function setTwitterAccessToken($twitterAccessToken)
    {
        $this->twitterAccessToken = $twitterAccessToken;

        return $this;
    }

    /**
     * Gets the instagramId.
     *
     * @return mixed
     */
    public function getInstagramId()
    {
        return $this->instagramId;
    }

    /**
     * Sets the instagramId.
     *
     * @param mixed $instagramId
     * @return $this
     */
    public function setInstagramId($instagramId)
    {
        $this->instagramId = $instagramId;

        return $this;
    }

    /**
     * Gets the instagramAccessToken.
     *
     * @return mixed
     */
    public function getInstagramAccessToken()
    {
        return $this->instagramAccessToken;
    }

    /**
     * Sets the instagramAccessToken.
     *
     * @param mixed $instagramAccessToken
     * @return $this
     */
    public function setInstagramAccessToken($instagramAccessToken)
    {
        $this->instagramAccessToken = $instagramAccessToken;

        return $this;
    }

    /**
     * Gets an email address to use with google groups.
     *
     * @return string
     */
    public function getGoogleEmail()
    {
        return $this->getUsername();
    }

    /**
     * Gets the mobileApp.
     *
     * @return mixed
     */
    public function getMobileApp()
    {
        return $this->mobileApp;
    }

    /**
     * Sets the mobileApp.
     *
     * @param mixed $mobileApp
     * @return $this
     */
    public function setMobileApp($mobileApp)
    {
        $this->mobileApp = $mobileApp;

        return $this;
    }

    /**
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize2()
    {
        return [
            self::class => [
                'name' => $this->getFullname(),
                'password' => $this->password,
                'isActive' => $this->isActive,
                'id' => $this->id,
                'createdAt' => $this->createdAt,
            ],
        ];
    }
}