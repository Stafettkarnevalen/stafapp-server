<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 21/10/2017
 * Time: 11.53
 */

namespace App\Entity\Documentation;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DocumentationRepository")
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Event
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class FAQ extends Documentation
{

}