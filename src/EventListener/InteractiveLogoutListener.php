<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 05/09/2017
 * Time: 23.01
 */

namespace App\EventListener;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Class InteractiveLogoutListener
 * @package App\EventListener
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class InteractiveLogoutListener implements LogoutHandlerInterface
{
    /**
     * Performs operations on a logut event.
     *
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
/*
        $cookie = $request->cookies->get(SecurityController::ADMIN_SESSION_COOKIE);
        if ($cookie) {
            $myfile = fopen("yeslogout.txt", "w");
            fwrite($myfile, 'logout succesfully executed !');

            $oldSession = unserialize(file_get_contents($cookie));

            fwrite($myfile, print_r($oldSession, true));

            $session = $request->getSession();
            $referer = null;
            foreach ($oldSession as $key => $val) {
                if ($key = SecurityController::ADMIN_URL_REFERER) {
                    $referer = $val;
                    continue;
                }
                $session->set($key, $val);
            }
            $request->cookies->remove(SecurityController::ADMIN_SESSION_COOKIE);
            // $userToken = $session->get('_security_main');
            //$session->getFlashBag()
            //    ->set('success', [
            //        'id' => 'admin.welcome_back',
            //        'parameters' => ['%name%' => $userToken->getUser()->getFullname()]
            //]);
            $response->setStatusCode(302);
            $response->headers->set('Location', $referer);
            fclose($myfile);
        } else {

            $myfile = fopen("nologout.txt", "w");
            fwrite($myfile, 'logout unsuccesfully executed !');
            fwrite($myfile, print_r($request->cookies->all(), true));
            fwrite($myfile, print_r($request->server->all(), true));
            fclose($myfile);
        }
        */
    }
}
