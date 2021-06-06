<?php

namespace Tests\Unit;

use Tests\TestCase;

class exampleTest extends TestCase
{
    public function test_example(): void
    {
        $response = $this->get('/');
        $response->assertStatus(302);
    }
}
