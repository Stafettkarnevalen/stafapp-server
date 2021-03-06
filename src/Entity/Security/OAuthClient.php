<?php

namespace App\Entity\Security;

use App\Entity\Traits\NameTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Model\ClientInterface;
use App\Entity\Traits\PersistencyDataTrait;
use FOS\OAuthServerBundle\Util\Random;
use OAuth2\OAuth2;

/**
 * OAuth Client.
 *
 * @ORM\Table(name="oauth_client_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Security
 * @author Robert Jürgens <robert.jurgens@idrott.fi>
 * @copyright Finlands Svenska Idrott 2017, All rights reserved.
 */
class OAuthClient implements ClientInterface
{
    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /** Use a client name */
    use NameTrait;

    /**
     * @ORM\Column(name="random_id_fld", type="string")
     * @var string $randomId
     */
    protected $randomId;

    /**
     * @ORM\Column(name="secret_fld", type="string")
     * @var string $secret
     */
    protected $secret;

    /**
     * @ORM\Column(name="redirect_uris_fld", type="array")
     * @var array $redirectUris
     */
    protected $redirectUris = [];

    /**
     * @ORM\Column(name="allowed_grant_types_fld", type="array")
     * @var array
     */
    protected $allowedGrantTypes = [];

    /**
     * @ORM\OneToMany(targetEntity="OAuthToken", mappedBy="client", cascade={"persist", "merge", "remove"})
     * @ORM\OrderBy({"expiresAt" = "DESC"})
     * @var ArrayCollection $tokens
     */
    protected $tokens;

    /**
     * OAuthClient constructor.
     */
    public function __construct()
    {
        $this->allowedGrantTypes[] = OAuth2::GRANT_TYPE_AUTH_CODE;
        $this->setRandomId(Random::generateToken());
        $this->setSecret(Random::generateToken());
        $this->tokens = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setRandomId($random)
    {
        $this->randomId = $random;
    }

    /**
     * {@inheritdoc}
     */
    public function getRandomId()
    {
        return $this->randomId;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicId()
    {
        return sprintf('%s_%s', $this->getId(), $this->getRandomId());
    }

    /**
     * {@inheritdoc}
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * {@inheritdoc}
     */
    public function checkSecret($secret)
    {
        return null === $this->secret || $secret === $this->secret;
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirectUris(array $redirectUris)
    {
        $this->redirectUris = $redirectUris;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUris()
    {
        return $this->redirectUris;
    }
    /**
     * {@inheritdoc}
     */

    public function setAllowedGrantTypes(array $grantTypes)
    {
        $this->allowedGrantTypes = $grantTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedGrantTypes()
    {
        return $this->allowedGrantTypes;
    }
}
