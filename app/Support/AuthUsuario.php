<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class AuthUsuario
{
    /**
     * @return array{id: string, token: string}
     */
    public static function contexto(Request $request): array
    {
        $auth = $request->attributes->get('auth');

        if (! is_array($auth) || empty($auth['id']) || ! is_string($auth['id'])) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid or expired access token');
        }

        return [
            'id' => $auth['id'],
            'token' => is_string($auth['token'] ?? null) ? $auth['token'] : '',
        ];
    }

    public static function id(Request $request): string
    {
        return self::contexto($request)['id'];
    }
}
