<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

require __DIR__.'/../src/whatsapp.php';

/**
 * @internal
 *
 * @coversNothing
 */
class WhatsappNumberTest extends TestCase
{
    private array $config;

    #[\Override]
    public function setUp(): void
    {
        $this->config = ['whatsapp_number' => '+00237679251512'];
    }

    public function testIfValidNumberReturnTheExpectedValue()
    {
        $expected = '237679251512';
        $this->assertEquals($expected, whatsapp_number($this->config));
    }

    public function testEmptyNumberReturnEmptyResult()
    {
        $config = ['whatsapp_number' => ''];
        $this->assertEmpty(whatsapp_number($config));
    }
}
