<?php

use Jose\Component\KeyManagement\JWKFactory;

require __DIR__.'/../vendor/autoload.php';

$key = JWKFactory::createECKey('P-256', ['use' => 'sig', 'alg' => 'ES256']);

echo 'VAPID_PUBLIC_KEY='.$key->get('x').PHP_EOL;
echo 'VAPID_PRIVATE_KEY='.$key->get('d').PHP_EOL;
