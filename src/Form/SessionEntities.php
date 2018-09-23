<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 15/12/2016
 * Time: 9.34
 */

namespace App\Form;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Session\Session;

class SessionEntities
{
    /** @var Session */
    private $session;

    /** @var Registry  */
    private $registry;

    /**
     * SessionEntities constructor.
     * @param Session $session
     * @param Registry $registry
     */
    public function __construct(Session $session, Registry $registry)
    {
        $this->session = $session;
        $this->registry = $registry;
    }

    /**
     * gets a Session value
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ($name && $this->session->get($name)) {
            $em = $this->registry->getManager();
            return $em->merge($this->session->get($name));
        }
        return null;
    }

    /**
     * Sets a Session value
     *
     * @param $name
     * @param $value
     *
     * @return SessionEntities
     */
    public function __set($name, $value)
    {
        $this->session->set($name, $value);

        return $this;
    }
}