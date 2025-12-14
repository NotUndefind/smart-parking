<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JWTService
{
    private string $secret;
    private string $issuer;
    private string $audience;
    private int $accessTokenTtl;

    public function __construct(
        string $secret,
        string $issuer = 'smart-parking',
        string $audience = 'smart-parking-clients',
        int $accessTokenTtl = 3600
    ) {
        $this->secret = $secret;
        $this->issuer = $issuer;
        $this->audience = $audience;
        $this->accessTokenTtl = $accessTokenTtl;
    }

    public function generateToken(string $userId, string $email, string $role = 'user'): string
    {
        $now = time();
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $now,
            'exp' => $now + $this->accessTokenTtl,
            'sub' => $userId,
            'email' => $email,
            'role' => $role,
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \RuntimeException('Invalid token: ' . $e->getMessage());
        }
    }

    public function getUserIdFromToken(string $token): string
    {
        $payload = $this->validateToken($token);
        return $payload['sub'] ?? '';
    }
}

