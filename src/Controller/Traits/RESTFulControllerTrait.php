<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 10/06/2018
 * Time: 16.25
 */

namespace App\Controller\Traits;


use App\Entity\Api\Message;
use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait RESTFulControllerTrait
{
    /**
     * Checks if the request requires a RESTful response
     *
     * @param Request $request
     * @return bool
     */
    public function isRestfulRequest(Request $request)
    {
        $mediaType = $request->attributes->get('media_type');
        return ($mediaType == 'application/json');
    }

    /**
     * Creates a serializing context.
     *
     * @param array $groups
     * @return Context
     */
    public function getContext(array $groups)
    {
        $ctx = new Context();
        return $ctx->enableMaxDepth()->setGroups($groups);
    }

    /**
     * Creates a view.
     *
     * Convenience method to allow for a fluent interface.
     *
     * @param mixed $data
     * @param int   $statusCode
     * @param array $headers
     *
     * @return View
     */
    abstract protected function view($data = null, $statusCode = null, array $headers = []);

    /**
     * Converts view into a response object.
     *
     * Not necessary to use, if you are using the "ViewResponseListener", which
     * does this conversion automatically in kernel event "onKernelView".
     *
     * @param View $view
     *
     * @return Response
     */
    abstract protected function handleView(View $view);

    /**
     * Returns an error response for api calls when the target entity was not found.
     *
     * @param $param
     * @param $value
     * @param $status
     * @return Response
     */
    public function notFoundError($param, $value, $status = Response::HTTP_NOT_FOUND)
    {
        return $this->error('entity_not_found:', "{$param}={$value}", $status);
    }

    /**
     * Returns an error response for api calls when the target entity was not found.
     *
     * @param $param
     * @param $value
     * @param $status
     * @return Response
     */
    public function badValueError($param, $value, $status = Response::HTTP_BAD_REQUEST)
    {
        return $this->error('invalid_value:', "{$param}={$value}", $status);
    }

    /**
     * Returns an error response for api calls when the target entity was not found.
     *
     * @param $param
     * @param $value
     * @param $status
     * @return Response
     */
    public function outOfBoundsError($param, $value, $status = Response::HTTP_BAD_REQUEST)
    {
        return $this->error('out_of_bounds:', "{$param}={$value}", $status);
    }

    /**
     * Returns an error response for api calls when the required variables are not met.
     *
     * @return Response
     */
    public function notValidError()
    {
        return $this->error('missing_required_data');
    }

    /**
     * Returns an error response for api calls when the required variables are not met.
     *
     * @return Response
     */
    public function notUniqueError()
    {
        return $this->error('not_unique_data');
    }

    /**
     * Returns an error response for api calls when the required variables are not met.
     *
     * @param $message
     * @param $precision
     * @param $status
     * @return Response
     */
    public function error($message, $precision = null, $status = Response::HTTP_BAD_REQUEST)
    {
        if ($precision)
            $message = $message  . " ({$precision})";
        $error = (new Message())
            ->addMessage(['code' => $status, 'message' => $message]);
        return $this->handleView(
            $this->view($error, $status)
        );
    }

    /**
     * Returns an error response for api calls when the required variables are not met.
     *
     * @param $messages
     * @return Response
     */
    public function errors($messages, $status = Response::HTTP_BAD_REQUEST)
    {
        $error = (new Message());
        foreach ($messages as $key => $message)
            $error->addMessage(['code' => $status, 'message' => $key . ': ' . $message]);
        return $this->handleView(
            $this->view($error, Response::HTTP_BAD_REQUEST)
        );
    }

    /**
     * Returns an ok response for api calls when the action was successful.
     *
     * @return Response
     */
    public function ok()
    {
        return $this->message('ok');
    }

    /**
     * Returns an ok response for api calls when the action was successful.
     *
     * @param $message
     * @return Response
     */
    public function message($message)
    {
        $ok = (new Message())
            ->setStatus(Message::STATUS_SUCCESS)
            ->addMessage(['code' => Response::HTTP_OK, 'message' => $message]);
        return $this->handleView(
            $this->view($ok, Response::HTTP_OK)
        );
    }

    /**
     * Returns an ok response for api calls when the action was successful.
     *
     * @param $messages
     * @return Response
     */
    public function messages($messages)
    {
        $ok = (new Message())
            ->setStatus(Message::STATUS_SUCCESS);
        foreach ($messages as $message)
            $ok->addMessage(['code' => Response::HTTP_OK, 'message' => $message]);
        return $this->handleView(
            $this->view($ok, Response::HTTP_OK)
        );
    }
    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return ManagerRegistry
     *
     * @throws \LogicException If DoctrineBundle is not available
     *
     * @final since version 3.4
     */
    abstract protected function getDoctrine();

    /**
     * Controller for reading a single entity
     * RESTful API only (GET).
     *
     * @param string $class
     * @param integer $id
     * @param array $groups
     * @return mixed
     */
    public function readEntity($class, $id, array $groups = ['Default'], $status = Response::HTTP_OK)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext($groups);

        $entity = $em->getRepository($class)->find($id);

        if ($entity) {
            return $this->handleView(
                $this->view([$class => $entity], $status)
                    ->setContext($ctx)
            );
        }
        return $this->notFoundError('id', $id);
    }

    /**
     * Controller for reading a single entity
     * RESTful API only (GET).
     *
     * @param array $data
     * @param array $groups
     * @return mixed
     */
    public function displayData(array $data, array $groups = ['Default'], $status = Response::HTTP_OK)
    {
        $ctx = $this->getContext($groups);

        return $this->handleView(
            $this->view($data, $status)
                ->setContext($ctx)
        );
    }
}