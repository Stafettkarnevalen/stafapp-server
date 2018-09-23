<?php

namespace App\Entity\Security;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\TokenInterface;
use App\Entity\Traits\PersistencyDataTrait;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * OAuth Token base class.
 *
 * @ORM\Table(name="oauth_token_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_fld", type="string")
 * @ORM\DiscriminatorMap({
 *     "token" = "OAuthToken",
 *     "access_token" = "OAuthAccessToken",
 *     "refresh_token" = "OAuthRefreshToken",
 *     "auth_code" = "OAuthAuthCode"
 * })
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Security
 * @author Robert Jürgens <robert.jurgens@idrott.fi>
 * @copyright Finlands Svenska Idrott 2017, All rights reserved.
 */

class OAuthToken implements TokenInterface
{

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @ORM\Column(name="token_fld", type="string")
     * @var string $token
     */
    protected $token;

    /**
     * @ORM\Column(name="expires_at_fld", type="integer", nullable=true)
     * @var int $expiresAt
     */
    protected $expiresAt;

    /**
     * @ORM\Column(name="scope_fld", type="string", nullable=true)
     * @var string $scope
     */
    protected $scope;

    /**
     * @ORM\ManyToOne(targetEntity="OAuthClient", inversedBy="tokens")
     * @ORM\JoinColumn(name="client_fld", referencedColumnName="id_fld", nullable=false)
     * @var OAuthClient
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_fld", referencedColumnName="id_fld", nullable=true)
     * @var User $user
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function getClientId()
    {
        return $this->getClient()->getPublicId();
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiresAt($timestamp)
    {
        $this->expiresAt = $timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        if ($this->expiresAt) {
            return $this->expiresAt - time();
        }

        return PHP_INT_MAX;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExpired()
    {
        if ($this->expiresAt) {
            return time() > $this->expiresAt;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->token;
    }
    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient()
    {
        return $this->client;
    }
}