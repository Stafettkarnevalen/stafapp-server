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
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Table(name="user_log_event_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Security
 * @author Robert Jürgens <robert@jurgens.fi>
 */
class UserLogEvent implements Serializable
{
    /** Use fields trait */
    use FieldsTrait;

    /**
     * @const TYPE_LOGIN A constant for a log event covering logins
     */
    const TYPE_LOGIN      = 'LOGINS';

    /**
     * @const TYPE_PASSWORD A constant for a log event covering changing of passwords
     */
    const TYPE_PASSWORD   = 'PASSWORD';

    /**
     * @const TYPE_TICKET A constant for a log event covering ticket actions
     */
    const TYPE_TICKET     = 'TICKET';

    /**
     * @const LEVEL_SUCCESS A constant for a log describing a successful event
     */
    const LEVEL_SUCCESS   = 'SUCCESS';

    /**
     * @const LEVEL_INFO A constant for a log describing an event which requires more information
     */
    const LEVEL_INFO      = 'INFO';

    /**
     * @const LEVEL_WARNING A constant for a log describing an event which requires a warning
     */
    const LEVEL_WARNING   = 'WARNING';

    /**
     * @const LEVEL_DANGER A constant for a log describing an event which requires an error message
     */
    const LEVEL_DANGER    = 'DANGER';

    /**
     * @ORM\Column(name="id_fld", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id The id of this LogEvent
     */
    protected $id;

    /**
     * @ORM\Column(name="type_fld", type="string", columnDefinition="ENUM('LOGINS', 'PASSWORD', 'TICKET')", options={"default": "LOGIN"}, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice({"LOGINS", "PASSWORD", "TICKET"})
     * @var string $type The type of this LogEvent
     */
    protected $type;

    /**
     * @ORM\Column(name="level_fld", type="string", columnDefinition="ENUM('INFO', 'WARNING', 'SUCCESS', 'DANGER')", options={"default": "INFO"}, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice({"INFO", "WARNING", "SUCCESS", "DANGER"})
     * @var string $level The level or the severity of this LogEvent
     */
    protected $level;

    /**
     * @ORM\Column(name="timestamp_fld", type="datetime", nullable=false)
     * @Assert\NotBlank()
     * @var \DateTime $timestamp The timestamp of this LogEvent
     */
    protected $timestamp;

    /**
     * @ORM\Column(name="remote_ip_fld", type="string", length=16, nullable=false)
     * @Assert\NotBlank()
     * @var string $remoteIp The remote ip address of the computer responsible for this LogEvent
     */
    protected $remoteIp;

    /**
     * @ORM\Column(name="remote_host_fld", type="string", length=64, nullable=false)
     * @Assert\NotBlank()
     * @var string $remoteHost The remote host name corresponding to the remote ip address
     */
    protected $remoteHost;

    /**
     * @ORM\Column(name="result_fld", type="string", length=255, nullable=false)
     * @var string $result A description of what occured
     */
    protected $result;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="logEvents")
     * @ORM\JoinColumn(name="user_fld", referencedColumnName="id_fld", nullable=false)
     * @var User $user The user responsible for this LogEvent
     */
    protected $user;

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->timestamp,
            $this->remoteHost,
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
            $this->timestamp,
            $this->remoteHost,
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
     * Gets the timestamp.
     *
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Sets the timestamp.
     *
     * @param mixed $timestamp
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Gets remote ip.
     *
     * @return mixed
     */
    public function getRemoteIp()
    {
        return $this->remoteIp;
    }

    /**
     * Sets the remote ip.
     *
     * @param mixed $remoteIp
     * @return $this
     */
    public function setRemoteIp($remoteIp)
    {
        $this->remoteIp = $remoteIp;
        $this->remoteHost = gethostbyaddr($remoteIp);

        return $this;
    }

    /**
     * Gets remote host.
     *
     * @return mixed
     */
    public function getRemoteHost()
    {
        return $this->remoteHost;
    }

    /**
     * Sets the remote host.
     *
     * @param mixed $remoteHost
     * @return $this
     */
    public function setRemoteHost($remoteHost)
    {
        $this->remoteHost = $remoteHost;
        $this->remoteIp = gethostbyname($remoteHost);

        return $this;
    }

    /**
     * Gets the User.
     *
     * @return mixed
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
     * Gets the result.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Sets the result.
     *
     * @param mixed $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Gets the type.
     *
     * @return string
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
     * Gets the log level.
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Sets the log level.
     *
     * @param mixed $level
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    public function __toString()
    {
        return sprintf('[%s] [%s (%s)] [%s]',
            $this->getTimestamp()->format('d.m.Y H:i:s'), $this->getRemoteHost(), $this->getRemoteIp(), $this->getResult());
    }
}