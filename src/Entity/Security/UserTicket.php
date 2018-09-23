<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 06/06/2017
 * Time: 22.13
 */
namespace App\Entity\Security;

use App\Entity\Interfaces\Serializable;
use App\Entity\Traits\FieldsTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Traits\LifespanTrait;
use Symfony\Component\Serializer\Annotation as Serialize;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="user_ticket_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Security
 * @author Robert Jürgens <robert@jurgens.fi>
 */
class UserTicket implements UserInterface, Serializable
{
    /** Use fields trait */
    use FieldsTrait;

    /** Use lifespan fields */
    use LifespanTrait;

    /**
     * @const TYPE_EMAIL A constant for a UserTicket sent by email.
     */
    const TYPE_EMAIL = 'EMAIL';

    /**
     * @const TYPE_SMS A constant for a UserTicket sent by SMS.
     */
    const TYPE_SMS   = 'SMS';

    /**
     * @const TYPE_USB A constant for a UserTicket stored on a USB drive.
     */
    const TYPE_USB   = 'USB';

    /**
     * @const FOR_LOGIN A constant for a UserTicket issued for logins
     */
    const FOR_LOGIN           = 'DOLOGIN';

    /**
     * @const FOR_LOGIN A constant for a UserTicket issued for changing password
     */
    const FOR_CHANGE_PASSWORD = 'CHPASSW';

    /**
     * @const FOR_LOGIN A constant for a UserTicket issued for changing email
     */
    const FOR_CHANGE_EMAIL    = 'CHEMAIL';

    /**
     * @const FOR_LOGIN A constant for a UserTicket issued for changing phone number
     */
    const FOR_CHANGE_PHONE    = 'CHPHONE';

    /**
     * @ORM\Column(name="id_fld", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id The Entity Id of this UserTicket
     */
    protected $id;

    /**
     * @ORM\Column(name="ticket_fld", type="string", length=64, nullable=false)
     * @Assert\NotBlank()
     * @var string $ticket The value of this UserTicket
     */
    protected $ticket;

    /**
     * @ORM\Column(name="tries_left_fld", type="integer", nullable=false, options={"default" : 3})
     * @var integer $triesLeft The number of tries left for this UserTicket
     */
    protected $triesLeft;

    /**
     * @ORM\Column(name="type_fld", type="string", columnDefinition="ENUM('EMAIL', 'SMS', 'USB')", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice({"EMAIL", "SMS", "USB"})
     * @var string $type The type of this UserTicket
     */
    protected $type;

    /**
     * @ORM\Column(name="for_fld", type="string", length=8, columnDefinition="ENUM('DOLOGIN', 'CHPASSW', 'CHEMAIL', 'CHPHONE')",
     *     nullable=false)
     * @Assert\NotBlank()
     * @var string $for The purpose for this UserTicket
     */
    protected $for;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="tickets", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="user_fld", referencedColumnName="id_fld", nullable=false)
     * @var User $user The owner of this UserTicket
     */
    protected $user;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Schools\School", mappedBy="principalTicket")
     */
    protected $school;

    /**
     * @Assert\Length(min=4)
     * @Assert\Length(max=32)
     * @var string $plaintextTicket The value of this UserTicket in plain text (never stored in the database)
     */
    protected $plaintextTicket;

    /**
     * Creates a UserTicket of a specified type for a specified User.
     *
     * @param User $user
     * @param string $type
     * @param PasswordEncoderInterface $encoder
     * @param string $for
     * @return UserTicket
     * @throws \Exception
     */
    public static function createFor(User $user, $type, $encoder, $for = UserTicket::FOR_LOGIN)
    {
        $ticket = new UserTicket();
        $expires = new \DateTime('now');
        $expires->add(new \DateInterval("PT10M"));
        $code = rand(10000, 999999);
        $code = str_pad($code, 6, '0', STR_PAD_LEFT);
        $password = $encoder->encodePassword($user, $code);

        return $ticket->setTicket($password)->setPlaintextTicket($code)->setIsActive(true)
            ->setFrom(new \DateTime('now'))->setUntil($expires)->setTriesLeft(3)
            ->setUser($user)->setType($type)->setFor($for);
    }

    /**
     * Refresh the ticket with a new password and valid period.
     *
     * @param $encoder PasswordEncoderInterface
     * @return $this
     * @throws \Exception
     */
    public function refresh($encoder)
    {
        $expires = new \DateTime('now');
        $expires->add(new \DateInterval("PT10M"));
        $code = rand(10000, 999999);
        $code = str_pad($code, 6, '0', STR_PAD_LEFT);
        $password = $encoder->encodePassword($this->getUser(), $code);

        return $this->setTicket($password)->setPlaintextTicket($code)->setIsActive(true)
            ->setFrom(new \DateTime('now'))->setUntil($expires)->setTriesLeft(3);
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->ticket,
            $this->triesLeft,
            $this->type,
            $this->user,
        ]);
    }

    /**
     * @see \Serializable::unserialize()
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->ticket,
            $this->triesLeft,
            $this->type,
            $this->user,
            ) = unserialize($serialized);
    }

    /**
     * Gets the id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the ticket.
     *
     * @return mixed
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Sets the ticket.
     *
     * @param mixed $ticket
     * @return $this
     */
    public function setTicket($ticket)
    {
        if (!empty($ticket))
            $this->ticket = $ticket;

        return $this;
    }

    /**
     * Registers a failed login attempt.
     *
     * @param mixed $remoteHost The remote host where the action was performed from
     * @return $this
     */
    public function failedTry($remoteHost = null)
    {
        if ($this->triesLeft > 0)
            $this->triesLeft--;
        if ($this->triesLeft == 0)
            $this->isActive = false;

        $this->getUser()->logEvent(UserLogEvent::TYPE_TICKET, UserLogEvent::LEVEL_DANGER,
            $remoteHost, 'ticket.wrong.credentials');

        return $this;
    }

    /**
     * Gets number of tries left.
     *
     * @return mixed
     */
    public function getTriesLeft()
    {
        return $this->triesLeft;
    }

    /**
     * Sets number of treis left.
     *
     * @param mixed $triesLeft
     * @return $this
     */
    public function setTriesLeft($triesLeft)
    {
        $this->triesLeft = $triesLeft;

        return $this;
    }

    /**
     * Returns true if this ticket still has tries left.
     *
     * @return bool
     */
    public function hasTriesLeft()
    {
        return ($this->triesLeft > 0);
    }

    /**
     * Gets the type.
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param mixed $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the User.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the User.
     *
     * @param mixed $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the plaintext ticket.
     *
     * @return mixed
     */
    public function getPlaintextTicket()
    {
        return $this->plaintextTicket;
    }

    /**
     * Sets the plaintext ticket.
     *
     * @param mixed $plaintextTicket
     * @return $this
     */
    public function setPlaintextTicket($plaintextTicket)
    {
        $this->plaintextTicket = $plaintextTicket;

        return $this;
    }

    public function getUsername()
    {
        if ($this->user)
            return $this->getUser()->getUsername();
        return null;
    }

    public function getPassword()
    {
        return $this->getTicket();
    }

    public function eraseCredentials()
    {

    }

    public function getSalt()
    {
        return null;
    }

    public function getRoles()
    {
        if ($this->user)
            return $this->getUser()->getRoles();
        return null;
    }

    /**
     * Gets the purpose of this ticket.
     *
     * @return string
     */
    public function getFor()
    {
        return $this->for;
    }

    /**
     * Sets the purpose of this thicket.
     *
     * @param string $for
     * @return $this
     */
    public function setFor($for)
    {
        $this->for = $for;

        return $this;
    }

    /**
     * Gets the school.
     *
     * @return mixed
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * Sets the school.
     *
     * @param mixed $school
     * @return $this
     */
    public function setSchool($school)
    {
        $this->school = $school;

        return $this;
    }

    /**
     * Gets the plain text password for the user.
     *
     * @return mixed
     */
    public function getPlainPassword()
    {
        if ($this->user)
            return $this->getUser()->getPlainPassword();
        return null;
    }

    /**
     * Sets the plain text password for the user.
     *
     * @param string $plainPassword
     * @return $this
     */
    public function setPlainPassword($plainPassword)
    {
        if ($this->user) {
            $this->getUser()->setPlainPassword($plainPassword);
        }

        return $this;
    }

}