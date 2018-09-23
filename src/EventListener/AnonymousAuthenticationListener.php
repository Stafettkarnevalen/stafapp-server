<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 23/10/2017
 * Time: 10.25
 */

namespace App\EventListener;


use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class AnonymousAuthenticationListener
 * @package AppBundle\EventListener
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class AnonymousAuthenticationListener
{
    public function handle(GetResponseEvent $event)
    {
        // TBD
    }
}