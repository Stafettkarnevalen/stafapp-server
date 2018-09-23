<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 21.19
 */

namespace App\EventListener;

use App\Entity\Schools\School;
use App\Entity\Security\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use App\Entity\Interfaces\CreatedByUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CreatedByUserListener
 * @package AppBundle\EventListener
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class CreatedByUserListener
{
    /** @var TokenStorageInterface $tokenStorage The tokenStorage */
    private $tokenStorage;

    /** @var  ContainerInterface $container The container*/
    private $container;

    /**
     * CreatedByUserListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage, ContainerInterface $container)
    {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
    }

    /**
     * Perform operations on entities before they are stored.
     *
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $token_storage = $this->container->get('security.token_storage');

        $entity = $event->getEntity();

        // Store the user who created the entity in the corresponding field
        if ($entity instanceof CreatedByUserInterface &&
            !$entity->getCreatedBy() &&
            $this->tokenStorage->getToken()->getUser() instanceof User
        ) {
            $entity->setCreatedBy($this->tokenStorage->getToken()->getUser());
        }
    }

    /**
     * Perform operations on entities before they are removed.
     *
     * @param LifecycleEventArgs $event
     * @throws OptimisticLockException
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $em = $event->getEntityManager();

        // Set school name to null so all its names can be removed by cascade
        if ($entity instanceof School) {
/*            $entity->setName(null);
            foreach ($entity->getNames() as $name) {
                $em->remove($name);
            }
*/
        }
    }

    /**
     * Perform operations on entities after they are removed.
     *
     * @param LifecycleEventArgs $event
     * @throws OptimisticLockException
     */
    public function postRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $em = $event->getEntityManager();

        // Set school name to null so all its names can be removed by cascade
        if ($entity instanceof School && ($ticket = $entity->getPrincipalTicket())) {
//            $em->remove($ticket);
//            $em->flush();
        }
    }

}