<?php

declare(strict_types=1);

return [
    'secret' => $_ENV['JWT_SECRET'] ?? 'change-me-in-env',
    'issuer' => $_ENV['JWT_ISSUER'] ?? 'smart-parking',
    'audience' => $_ENV['JWT_AUDIENCE'] ?? 'smart-parking-clients',
    'access_token_ttl' => (int) ($_ENV['JWT_ACCESS_TTL'] ?? 3600),
];


