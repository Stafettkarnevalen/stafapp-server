<?php

namespace App\Entity\Security;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Model\AccessTokenInterface;

/**
 * OAuth AccessToken class.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Security
 * @author Robert Jürgens <robert.jurgens@idrott.fi>
 * @copyright Finlands Svenska Idrott 2017, All rights reserved.
 */

class OAuthAccessToken extends OAuthToken implements AccessTokenInterface
{

}