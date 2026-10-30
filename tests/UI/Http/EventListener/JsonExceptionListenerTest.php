<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\EventListener;

use App\UI\Http\EventListener\JsonExceptionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class JsonExceptionListenerTest extends TestCase
{
    public function testItReturnsJsonForAPlainHttpException(): void
    {
        $event = $this->createExceptionEvent(new NotFoundHttpException('Not found.'));

        (new JsonExceptionListener())->onKernelException($event);

        $response = $event->getResponse();

        $this->assertNotNull($response);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(['error' => 'Not found.'], json_decode($response->getContent(), true));
    }

    public function testItReturnsJsonWithViolationMessagesForAValidationFailure(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('This value should be between 0 and 4.', null, [], null, 'position', 9),
        ]);

        $exception = new UnprocessableEntityHttpException(
            'Validation failed.',
            new ValidationFailedException(9, $violations)
        );

        $event = $this->createExceptionEvent($exception);

        (new JsonExceptionListener())->onKernelException($event);

        $response = $event->getResponse();

        $this->assertNotNull($response);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(
            ['error' => 'This value should be between 0 and 4.'],
            json_decode($response->getContent(), true)
        );
    }

    private function createExceptionEvent(\Throwable $exception): ExceptionEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);

        return new ExceptionEvent($kernel, new Request(), HttpKernelInterface::MAIN_REQUEST, $exception);
    }
}
