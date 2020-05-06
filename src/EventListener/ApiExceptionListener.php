<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (strpos('api', $request->getPathInfo()) !== false) {
            return;
        }

        // Customize your response object to display the exception details
        $response = new JsonResponse();

        $status_code = 500;
        if (method_exists($exception, 'getStatusCode')) {
            $status_code = $exception->getStatusCode();
        }
        $message = $exception->getMessage();

        // HttpExceptionInterface is a special type of exception that header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->headers->replace($exception->getHeaders());
        }

        // sends the modified response object to the event
        $response->setStatusCode($status_code);
        $response->setData([
            'error' => [
                'status_code' => $status_code,
                'message' => $message
            ]
        ]);
        $event->setResponse($response);
    }
}