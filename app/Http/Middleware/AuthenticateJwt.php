<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticateJwt
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (! $authHeader) {
            return response()->json(['message' => 'Missing Authorization header'], 401);
        }

        if (! preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches)) {
            return response()->json(['message' => 'Authorization must be Bearer token'], 401);
        }

        $publicKeyPem = config('jwt.public_key_pem');

        if (! is_string($publicKeyPem) || trim($publicKeyPem) === '') {
            return response()->json(['message' => 'JWT public key is not configured'], 500);
        }

        try {
            $publicKey = str_replace('\\n', "\n", $publicKeyPem);
            $decoded = JWT::decode($matches[1], new Key($publicKey, 'RS256'));

            $issuer = config('jwt.issuer');
            $audience = config('jwt.audience');

            if (($decoded->iss ?? null) !== $issuer) {
                throw new \InvalidArgumentException('Invalid issuer');
            }

            $tokenAudience = $decoded->aud ?? null;

            if (is_array($tokenAudience)) {
                $tokenAudience = $tokenAudience[0] ?? null;
            }

            if ($tokenAudience !== $audience) {
                throw new \InvalidArgumentException('Invalid audience');
            }

            if (! isset($decoded->sub) || ! is_string($decoded->sub) || trim($decoded->sub) === '') {
                throw new \InvalidArgumentException('Invalid sub claim');
            }

            if (! isset($decoded->iat, $decoded->exp) || ! is_numeric($decoded->iat) || ! is_numeric($decoded->exp)) {
                throw new \InvalidArgumentException('Invalid temporal claims');
            }

            $request->attributes->set('auth', [
                'id' => $decoded->sub,
                'token' => $matches[1],
            ]);
        } catch (Throwable) {
            return response()->json(['message' => 'Invalid or expired access token'], 401);
        }

        return $next($request);
    }
}
