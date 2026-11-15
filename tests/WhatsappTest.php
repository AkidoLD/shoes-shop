<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
require __DIR__.'/../src/whatsapp.php';

class WhatsappTest extends TestCase {
    function testWhatsappNumberReturnTheExpectedValue(){
        $config = ["whatsapp_number" => "+237679251512"];
        $expected = "237679251512";
        $this->assertEquals($expected, whatsapp_number($config));
    }
}