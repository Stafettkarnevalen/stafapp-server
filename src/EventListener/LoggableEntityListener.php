<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 29/10/2017
 * Time: 1.43
 */

namespace App\EventListener;

use App\Entity\Interfaces\LoggableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\LoggableListener;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class LoggableEntityListener
 * @package AppBundle\EventListener
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class LoggableEntityListener extends LoggableListener
{

    /** @var TokenStorageInterface $tokenStorage The token storage */
    private $tokenStorage;

    /**
     * LoggableEntityListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        parent::__construct();
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Performs operations on entities after they are loaded.
     *
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $em = $event->getEntityManager();
        // store logs in the entity
        if ($entity instanceof LoggableEntity) {
            $logs = $em->getRepository(LogEntry::class)->findBy([
                'objectClass' => get_class($entity),
                'objectId' => $entity->getId(),
            ], [
                'loggedAt' => 'ASC'
            ]);
            $entity->setLogs(new ArrayCollection($logs));
        }
    }

    /**
     * Performs operations on entities before they are flushed.
     *
     * @param EventArgs $args
     */
    public function onFlush(EventArgs $args)
    {
        // store the username in the log
        $token = $this->tokenStorage->getToken();
        if ($token)
            $this->setUsername($token->getUsername());
        else
            $this->setUsername('unknown');
        parent::onFlush($args);
    }
}