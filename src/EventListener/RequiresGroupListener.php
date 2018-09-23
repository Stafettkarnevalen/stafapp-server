<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 05/11/2017
 * Time: 18.27
 */

namespace App\EventListener;

use App\Entity\Interfaces\RequiresGroupInterface;
use App\Entity\Security\Group;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Psr\Log\LoggerInterface;

/**
 * Class RequiresGroupListener
 * @package App\EventListener
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class RequiresGroupListener
{

    /** @var LoggerInterface $logger */
    protected $logger;

    /**
     * RequiresGroupListener constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Performs operations on entities after they are stored for the first time.
     *
     * @param LifecycleEventArgs $event
     * @throws OptimisticLockException
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
        $entity = $event->getEntity();

        $this->logger->info('preMerge');

        // store a one-to-one role for the entity
        if ($entity instanceof RequiresGroupInterface && $entity->getGroup() === null) {
            $group = new Group();
            $group->setName($entity->getGroupName())
                ->setEmail($entity->getGroupEmail())
                ->setLoginRoute($entity->getGroupLoginRoute())
                ->setLogoutRoute($entity->getGroupLogoutRoute())
                ->setIsSystem(true)
                ->setIsGoogleSynced($entity->getGroupIsGoogleSynced());
            $em->persist($group);
            $entity->setGroup($group);
            $em->merge($entity);
            $em->flush();
        } else if ($entity instanceof RequiresGroupInterface) {
            $group = $entity->getGroup();
            $group->setName($entity->getGroupName());
            $group->setEmail($entity->getGroupEmail());
            $em->merge($group);
            $em->flush();
        }
    }

    /**
     * Performs operations on entities after they are stored.
     *
     * @param LifecycleEventArgs $event
     * @throws OptimisticLockException
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
        $entity = $event->getEntity();

        $this->logger->info('postMerge');

        // store a one-to-one role for the entity
        if ($entity instanceof RequiresGroupInterface && $entity->getGroup() === null) {
            $group = new Group();
            $group->setName($entity->getGroupName())
                ->setEmail($entity->getGroupEmail())
                ->setLoginRoute($entity->getGroupLoginRoute())
                ->setLogoutRoute($entity->getGroupLogoutRoute())
                ->setIsSystem(true)
                ->setIsGoogleSynced($entity->getGroupIsGoogleSynced());
            $em->persist($group);
            $entity->setGroup($group);
            $em->merge($entity);
            $em->flush();
        } else if ($entity instanceof RequiresGroupInterface) {
            $group = $entity->getGroup();
            $group->setName($entity->getGroupName());
            $group->setEmail($entity->getGroupEmail());
            $em->merge($group);
            $em->flush();
        }
    }
}