<?php

declare(strict_types=1);

namespace App\Tests\UI\Http;

use PHPUnit\Framework\TestCase;
use App\Application\Port\In\SpinWheelUseCase;
use App\Application\Dto\SpinWheelResult;
use App\Application\Dto\Request\SpinWheelInput;
use App\UI\Http\SpinWheelController;
use Symfony\Component\HttpFoundation\JsonResponse;

class SpinWheelControllerTest extends TestCase
{
    public function testInvokeReturnsNotFoundWhenUseCaseReturnsFailure(): void
    {
        $spinWheelUseCase = $this->createStub(SpinWheelUseCase::class);
        $spinWheelUseCase->method('execute')->willReturn(SpinWheelResult::failure('Invalid user id provided.'));

        $controller = new SpinWheelController($spinWheelUseCase);

        $response = $controller(99, new SpinWheelInput(500));

        $this->assertSame(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame(
            ['error' => 'Invalid user id provided.'],
            json_decode($response->getContent(), true)
        );
    }

    public function testInvokeReturnsCreatedWhenUseCaseSucceeds(): void
    {
        $spinWheelUseCase = $this->createStub(SpinWheelUseCase::class);
        $spinWheelUseCase->method('execute')->willReturn(SpinWheelResult::success('2x', 1000, 1500));

        $controller = new SpinWheelController($spinWheelUseCase);

        $response = $controller(1, new SpinWheelInput(500));

        $this->assertSame(JsonResponse::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame(
            ['multiplier' => '2x', 'earned' => 1000, 'balance' => 1500],
            json_decode($response->getContent(), true)
        );
    }

    public function testInvokeCallsUseCaseWithUserIdAndSpinCost(): void
    {
        $spinWheelUseCase = $this->createMock(SpinWheelUseCase::class);
        $spinWheelUseCase->expects($this->once())
            ->method('execute')
            ->with(1, 500)
            ->willReturn(SpinWheelResult::success('2x', 1000, 1500));

        $controller = new SpinWheelController($spinWheelUseCase);

        $controller(1, new SpinWheelInput(500));
    }
}
