<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 24/04/2018
 * Time: 12.46
 */

namespace App\Entity\Security;


use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OauthUserProvider extends BaseClass {

    protected $session = null;
    protected $logger = null;

    public function __construct(UserManagerInterface $userManager, array $properties, SessionInterface $session, LoggerInterface $logger)
    {
        parent::__construct($userManager, $properties);
        $this->session = $session;
        $this->logger = $logger;
    }

    public function connect(UserInterface $user, UserResponseInterface $response) {
        $property = $this->getProperty($response);

        $username = $response->getUsername();

        // Logger
        $this->logger->debug('FOOBAR connect: ' . $property . '=' . $username);

        // On connect, retrieve the access token and the user id
        $service = $response->getResourceOwner()->getName();

        $setter = 'set' . ucfirst($service);
        $setter_id = $setter . 'Id';
        $setter_token = $setter . 'AccessToken';

        // Disconnect previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }

        // Connect using the current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        if ($user instanceof \FOS\UserBundle\Model\UserInterface)
            $this->userManager->updateUser($user);
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response) {
        $data = $response->getData();

        $username = $response->getUsername();
        $email = $response->getEmail();

        /** @var User $user */
        //$user = $this->userManager->findUserBy(['username' => $email]);
        //if ($user === null)
        $user = $this->userManager->findUserBy([$this->getProperty($response) => $username]);

        $service = $response->getResourceOwner()->getName();

        // Logger
        $this->logger->debug('FOOBAR: ' . $service . '=' . $username);

        $getter = 'get' . ucfirst($service);
        $getter_id = $getter . 'Id';

        $setter = 'set' . ucfirst($service);
        $setter_id = $setter . 'Id';
        $setter_token = $setter . 'AccessToken';

        // If the user is new
        if (null === $user) {
            $this->logger->debug('FOOBAR user was null');
            $user = new SchoolManager();

            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());

            //I have set all requested data with the user's username
            //modify here with relevant data
            if (preg_match("/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i", $email))
                $user->setUsername($email);

            // check for real email
            if (preg_match("/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i", $email))
                $user->setEmail($email);

            $user->setPassword($username);
            $user->setEnabled(true);
            // $user->setConsented(true);
            if ($service === 'twitter') {
                $names = explode(' ', $response->getRealName());
                $user->setLastname(array_pop($names));
                $user->setFirstname(join(' ', $names));
            } else if ($service === 'instagram') {
                $names = explode(' ', $response->getRealName());
                $user->setLastname(array_pop($names));
                $user->setFirstname(join(' ', $names));
            } else {
                $user->setFirstname($response->getFirstName());
                $user->setLastname($response->getLastName());
            }
            $user->setData($service, $data);
            $this->session->set('_oauth_user', $user);
            $this->session->set('_oauth_type', $service);

            throw new AccountNotLinkedException(sprintf("User '%s' not found.", $username));

            // return $user;
        } else if ($user->$getter_id() === null) {
            $this->logger->debug('FOOBAR user was ' . $user->getId());

            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());
            $this->userManager->updateUser($user);

            $user = parent::loadUserByOAuthUserResponse($response);
            $this->session->set('_oauth_user', $user);
            $this->session->set('_oauth_type', $service);
            return $user;
        }
        $this->logger->debug('FOOBAR user can connect');

        // If the user exists, use the HWIOAuth
        $user = parent::loadUserByOAuthUserResponse($response);

        $serviceName = $response->getResourceOwner()->getName();

        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';

        // Update the access token
        $user->$setter($response->getAccessToken());

        return $user;
    }
}
