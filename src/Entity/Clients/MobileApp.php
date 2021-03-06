<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 06/06/2017
 * Time: 22.13
 */
namespace App\Entity\Clients;

use App\Entity\Interfaces\Serializable;
use App\Entity\Security\User;
use App\Entity\Traits\DataTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serialize;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="mobile_app_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Clients
 * @author Robert Jürgens <robert@jurgens.fi>
 */
class MobileApp implements Serializable
{
    /** Use data trait */
    use DataTrait;

    /**
     * @ORM\Column(name="id_fld", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id The Entity Id of this UserTicket
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\User", mappedBy="mobileApp")
     * @var User $user the user account thet this app is connected to
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="MobileAppTicket", mappedBy="mobileApp", cascade={"persist", "merge", "remove"})
     * @ORM\OrderBy({"from" = "DESC"})
     * @var ArrayCollection Tickets that belongs to this mobileApp
     */
    protected $tickets;


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
}