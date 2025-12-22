<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

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

        // Créer le header
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);

        // Encoder en base64url
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        // Créer la signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        // Créer le JWT
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public function validateToken(string $token): array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                throw new \RuntimeException('Invalid token format');
            }

            [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret, true);
            $expectedSignature = $this->base64UrlEncode($signature);

            if ($base64UrlSignature !== $expectedSignature) {
                throw new \RuntimeException('Invalid signature');
            }

            // Décoder le payload
            $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);

            // Vérifier l'expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new \RuntimeException('Token expired');
            }

            return $payload;
        } catch (\Exception $e) {
            throw new \RuntimeException('Invalid token: ' . $e->getMessage());
        }
    }

    public function getUserIdFromToken(string $token): string
    {
        $payload = $this->validateToken($token);
        return $payload['sub'] ?? '';
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
