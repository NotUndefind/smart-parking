<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Domain\Exceptions\UnauthorizedAccessException;
use App\Infrastructure\Security\JWTService;

final class AuthMiddleware
{
    public function __construct(private JWTService $jwtService)
    {
    }

    public function handle(): array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (empty($authHeader)) {
            throw new UnauthorizedAccessException('Authorization header missing');
        }

        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            throw new UnauthorizedAccessException('Invalid authorization header format');
        }

        $token = $matches[1];

        try {
            $payload = $this->jwtService->validateToken($token);
            return $payload;
        } catch (\Exception $e) {
            throw new UnauthorizedAccessException('Invalid or expired token');
        }
    }
}

