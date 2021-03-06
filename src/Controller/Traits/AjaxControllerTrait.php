<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 26/09/2017
 * Time: 18.46
 */

namespace App\Controller\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

/**
 * Trait AjaxControllerTrait
 * @package App\Controller
 */
trait AjaxControllerTrait
{
    public abstract function redirect($url, $status = Response::HTTP_OK);

    function redirectWithAjaxSupport(Request $request, $url, $status = Response::HTTP_TEMPORARY_REDIRECT)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['redirect' => $url], $status);
        } else {
            return $this->redirect($url, $status);
        }
    }

    public function reloadWithAjaxSupport(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array('reload' => true));
        } else {
            return $this->redirect($request->getUri());
        }
    }

    public abstract function generateUrl(Route $route, array $params = []);

    public function redirectToRouteWithAjaxSupport(Request $request, $route, $parameters = [], $status = 302)
    {
        if ($request->isXmlHttpRequest()) {
            $url = $this->generateUrl($route, $parameters);
            return new JsonResponse(array('redirect' => $url), $status);
        } else {
            return $this->redirect($route, $status);
        }
    }

}