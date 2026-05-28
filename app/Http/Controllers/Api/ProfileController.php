<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Support\AuthUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProfileController extends Controller
{
    public function index()
    {
        return response()->json(Profile::all());
    }

    public function me(Request $request)
    {
        $userId = AuthUsuario::id($request);

        return response()->json(
            Profile::where('user_id', $userId)->firstOrFail()
        );
    }

    public function store(Request $request)
    {
        $userId = AuthUsuario::id($request);

        if (Profile::where('user_id', $userId)->exists()) {
            return response()->json([
                'message' => 'Este usuário já possui um perfil gamer.',
            ], 422);
        }

        $data = $request->validate([
            'nickname' => 'required|unique:profiles',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bio' => 'nullable|string',
            'country' => 'nullable|string',
            'platforms' => 'nullable|array',
            'games' => 'nullable|array',
        ]);

        $data['user_id'] = $userId;

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = asset('storage/'.$path);
        }

        return response()->json(Profile::create($data), 201);
    }

    public function show(Request $request, string $user_id)
    {
        $this->garantirAcessoAoPerfil($request, $user_id);

        return response()->json(
            Profile::where('user_id', $user_id)->firstOrFail()
        );
    }

    public function update(Request $request, string $user_id)
    {
        $this->garantirAcessoAoPerfil($request, $user_id);

        $profile = Profile::where('user_id', $user_id)->firstOrFail();

        $data = $request->validate([
            'nickname' => 'string|unique:profiles,nickname,'.$profile->id,
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bio' => 'nullable|string',
            'country' => 'nullable|string',
            'platforms' => 'nullable|array',
            'games' => 'nullable|array',
        ]);

        if ($request->hasFile('avatar')) {
            if ($profile->avatar) {
                $oldPath = str_replace(asset('storage/'), '', $profile->avatar);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = asset('storage/'.$path);
        }

        $profile->update($data);

        return response()->json($profile->fresh());
    }

    public function destroy(Request $request, string $user_id)
    {
        $this->garantirAcessoAoPerfil($request, $user_id);

        $profile = Profile::where('user_id', $user_id)->firstOrFail();
        $profile->delete();

        return response()->json(['message' => 'Perfil removido']);
    }

    private function garantirAcessoAoPerfil(Request $request, string $user_id): void
    {
        if (AuthUsuario::id($request) !== $user_id) {
            throw new AccessDeniedHttpException('Você só pode acessar o seu próprio perfil gamer.');
        }
    }
}
