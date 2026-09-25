<?php

namespace Tests\Unit;

use App\Services\SvgSanitizer;
use PHPUnit\Framework\TestCase;

class SvgSanitizerTest extends TestCase
{
    public function test_it_removes_script_tags_from_svg(): void
    {
        $malicious = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert("xss")</script><circle cx="50" cy="50" r="40"/></svg>';
        $cleaned = SvgSanitizer::sanitize($malicious);

        $this->assertStringNotContainsString('<script', $cleaned);
        $this->assertStringNotContainsString('alert', $cleaned);
        $this->assertStringContainsString('<circle', $cleaned);
    }

    public function test_it_removes_inline_event_handlers(): void
    {
        $malicious = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(\'xss\')"><rect width="100" height="100" onclick="evil()"/></svg>';
        $cleaned = SvgSanitizer::sanitize($malicious);

        $this->assertStringNotContainsString('onload', $cleaned);
        $this->assertStringNotContainsString('onclick', $cleaned);
        $this->assertStringNotContainsString('evil()', $cleaned);
        $this->assertStringContainsString('<rect', $cleaned);
    }

    public function test_it_rejects_xxe_entity_declarations(): void
    {
        $xxe = '<!DOCTYPE svg [ <!ENTITY xxe SYSTEM "file:///etc/passwd"> ]><svg>&xxe;</svg>';

        $this->expectException(\Exception::class);
        SvgSanitizer::sanitize($xxe);
    }
}
