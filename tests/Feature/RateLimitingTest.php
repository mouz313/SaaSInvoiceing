<?php

namespace Tests\Feature;

use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    public function test_it_throttles_contact_form_submissions_after_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', [
                'name' => 'Tester',
                'email' => 'test@example.com',
                'subject' => 'Hello',
                'message' => 'Test message',
            ]);
        }

        // 6th attempt should be blocked with 429 Too Many Requests
        $response = $this->post('/contact', [
            'name' => 'Tester',
            'email' => 'test@example.com',
            'subject' => 'Hello',
            'message' => 'Test message',
        ]);

        $response->assertStatus(429);
    }
}
