<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 05/06/2017
 * Time: 11.30
 */

namespace App\Entity\Communication;

use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Interfaces\Serializable;
use App\Entity\Security\User;
use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Model\SignedCommentInterface;

use App\Entity\Traits\FieldsTrait;
use FOS\CommentBundle\Entity\Comment as BaseComment;
use FOS\CommentBundle\Model\ThreadInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="comment_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Communication
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class Comment extends BaseComment implements Serializable, SignedCommentInterface, CreatedByUserInterface
{
    /** use fields trait */
    use FieldsTrait;

    /**
     * @ORM\Column(name="id_fld", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id The id of the entity
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="comments")
     * @ORM\JoinColumn(name="thread_fld", referencedColumnName="id_fld", nullable=false)
     * @var Thread $thread The thread that the comment is in
     */
    protected $thread;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", inversedBy="comments")
     * @ORM\JoinColumn(name="author_fld", referencedColumnName="id_fld", nullable=false)
     * @var User $author The author of the comment
     */
    protected $author;

    /**
     * Gets the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id.
     *
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets the author.
     *
     * @param UserInterface $author
     * @return $this
     */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Gets the author.
     *
     * @return User|UserInterface
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Gets the name of the author.
     *
     * @return null|string
     */
    public function getAuthorName()
    {
        if (null === $this->getAuthor()) {
            return null;
        }
        return $this->getAuthor()->getUsername();
    }

    /**
     * Message constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Gets the thread.
     *
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Sets the thread.
     *
     * @param ThreadInterface $thread
     * @return $this
     */
    public function setThread(ThreadInterface $thread)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Gets the User who created this object.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->getAuthor();
    }

    /**
     * Sets the User who created this object.
     *
     * @param User $user
     * @return $this
     */
    public function setCreatedBy(User $user)
    {
        return $this->setAuthor($user);
    }
}