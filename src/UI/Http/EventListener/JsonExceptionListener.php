<?php

declare(strict_types=1);

namespace App\UI\Http\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class JsonExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (! $exception instanceof HttpExceptionInterface) {
            return;
        }

        $previous = $exception->getPrevious();

        if ($previous instanceof ValidationFailedException) {
            $messages = [];

            foreach ($previous->getViolations() as $violation) {
                $messages[] = $violation->getMessage();
            }

            $event->setResponse(new JsonResponse(['error' => implode(' ', $messages)], $exception->getStatusCode()));

            return;
        }

        $event->setResponse(new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode()));
    }
}
