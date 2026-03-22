<?php

namespace Tests\Unit;

use App\Classes\PterodactylClient;
use Database\Seeders\GeneralPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TestUserCommand extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('invalidPteroIdDataProvider')]
    public function testMakeUserCommand(array $apiResponse, int $expectedExitCode): void
    {
        $this->seed(GeneralPermissionsSeeder::class);

        $pterodactyl = $this->getMockBuilder(PterodactylClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pterodactyl->expects(self::once())->method('getUser')->willReturn($apiResponse);

        $this->app->instance(PterodactylClient::class, $pterodactyl);

        $this->artisan('make:user')
            ->expectsQuestion('Please specify your Pterodactyl ID.', 1)
            ->expectsQuestion('password', 'password')
            ->assertExitCode($expectedExitCode);
    }

    public static function invalidPteroIdDataProvider(): array
    {
        return [
            'Good Response' => [
                'apiResponse' => [
                    'id' => 12345,
                    'first_name' => 'Test',
                    'email' => 'test@test.test',
                ],
                'expectedExitCode' => 1,
            ],
            'Bad Response' => [
                'apiResponse' => [],
                'expectedExitCode' => 0,
            ],
        ];
    }
}
