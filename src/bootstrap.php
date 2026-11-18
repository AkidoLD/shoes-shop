<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

$config = require __DIR__.'/config.php';

require __DIR__.'/helpers.php';

require __DIR__.'/schema.org.php';

require __DIR__.'/opengraph.php';

require __DIR__.'/whatsapp.php';

return $config;
