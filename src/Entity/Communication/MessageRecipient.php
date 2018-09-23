<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 05/06/2017
 * Time: 12.19
 */

namespace App\Entity\Communication;

use App\Entity\Interfaces\Serializable;
use App\Entity\Security\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Traits\CloneableTrait;

/**
 * @ORM\Table(name="message_recipient_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Communication
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class MessageRecipient implements Serializable
{
    /** Use cloneable trait */
    use CloneableTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @const BOX_INBOX The inbox
     */
    const BOX_INBOX    = "INBOX";

    /**
     * @const BOX_ARCHIVE Box for archived messages
     */
    const BOX_ARCHIVE  = "ARCHIVE";

    /**
     * @const BOX_TRASH The trash bin
     */
    const BOX_TRASH    = "TRASH";

    /**
     * @const BOX_SENT Box for sent messages
     */
    const BOX_SENT     = "SENT";

    /**
     * @ORM\Column(name="box_fld", type="string", columnDefinition="ENUM('INBOX', 'ARCHIVE', 'TRASH', 'SENT')", options={"default" : "INBOX"}, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice({"INBOX", "ARCHIVE", "TRASH", "SENT"})
     * @var string $box The box that the message is stored in
     */
    protected $box = self::BOX_INBOX;

    /**
     * @ORM\Column(name="hidden_fld", type="boolean", options={"default" : false}, nullable=true)
     * @var boolean $isHidden If the message is hidden
     */
    protected $isHidden;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="messages")
     * @ORM\JoinColumn(name="user_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var User $user The recipient user
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="recipients", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="message_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Message $message The message
     */
    protected $message;

    /**
     * @ORM\Column(name="read_fld", type="datetime", nullable=true)
     * @var \DateTime $read If the message was read or not
     */
    protected $read;

    /**
     * Gets the box.
     *
     * @return string
     */
    public function getBox()
    {
        return $this->box;
    }

    /**
     * Sets the box.
     *
     * @param string $box
     * @return $this
     */
    public function setBox($box)
    {
        $this->box = $box;

        return $this;
    }

    /**
     * Gets is hidden.
     *
     * @return boolean
     */
    public function getisHidden()
    {
        return $this->isHidden;
    }

    /**
     * Sets  is hidden.
     *
     * @param boolean $isHidden
     * @return $this
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    /**
     * Gets the recipient
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the recipient.
     *
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the message.
     *
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the message.
     *
     * @param Message $message
     * @return $this
     */
    public function setMessage(Message $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Gets the read timestamp.
     *
     * @return \DateTime|null
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Sets the read timestamp.
     *
     * @param \DateTime|null $read
     * @return $this
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * Gets magic properties.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        return $this->getMessage()->$getter();
    }

    /**
     * Sets magic properties.
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        $this->getMessage()->$setter($value);

        return $this;
    }

    /**
     * Gets the children, the message thread.
     *
     * @return ArrayCollection
     */
    public function children()
    {
        $children = new ArrayCollection([]);
        $u = $this->getUser();

        /** @var Message $msg */
        foreach ($this->getMessage()->getChildren() as $msg) {
            $rcpts = $msg->getRecipients();
            if ($msg->getCreatedBy() === $u) {
                foreach ($rcpts as $rcpt)
                    $children->add($rcpt);
            } else {
                $critera = Criteria::create()
                    ->where(Criteria::expr()->eq('user', $u))
                ;
                foreach ($rcpts->matching($critera) as $re)
                    // foreach ($rcpts as $re)
                    $children->add($re);
            }
        }

        return $children;
    }

    /**
     * Gets the parent.
     *
     * @return MessageRecipient|null
     */
    public function getParent()
    {
        /** @var Message $msg */
        $parent = $this->getMessage()->getParent();
        if (!$parent)
            return null;
        $p = null;
        $u = $this->getUser();
        $from = $parent->getCreatedBy();
        if ($from === $u) {
            $rcpts = $parent->getRecipients();
            $critera = Criteria::create()
                ->where(Criteria::expr()->eq('user', $this->getMessage()->getCreatedBy()));
            $found = $rcpts->matching($critera);
            if ($found->count())
                $p = $found->current();
        }
        return $p;
    }

    public function __toString()
    {
        return $this->getMessage()->getTitle();
    }
}