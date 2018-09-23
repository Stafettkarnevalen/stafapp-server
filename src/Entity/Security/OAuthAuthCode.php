<?php

namespace App\Entity\Security;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;

/**
 * OAuth AccessToken class.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Security
 * @author Robert Jürgens <robert.jurgens@idrott.fi>
 * @copyright Finlands Svenska Idrott 2017, All rights reserved.
 */

class OAuthAuthCode extends OAuthToken implements AuthCodeInterface
{

    /**
     * @ORM\Column(name="redirect_uri_fld", type="string")
     * @var string $redirectUri
     */
    protected $redirectUri;

    /**
     * Gets the redirectUri.
     *
     * @return mixed
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Sets the redirectUri.
     *
     * @param mixed $redirectUri
     * @return $this
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }
}