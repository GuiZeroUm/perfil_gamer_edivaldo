<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        return response()->json(Profile::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'   => 'required|unique:profiles',
            'nickname'  => 'required|unique:profiles',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Aceita apenas imagens
            'bio'       => 'nullable|string',
            'country'   => 'nullable|string',
            'platforms' => 'nullable|array',
            'games'     => 'nullable|array',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = asset('storage/' . $path); // Gera o link completo da foto
        }

        return response()->json(Profile::create($data), 201);
    }

    public function show($user_id)
    {
        return response()->json(Profile::where('user_id', $user_id)->firstOrFail());
    }

    public function update(Request $request, $user_id)
    {
        $profile = Profile::where('user_id', $user_id)->firstOrFail();

        $data = $request->validate([
            'nickname'  => 'string|unique:profiles,nickname,' . $profile->id,
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bio'       => 'nullable|string',
            'country'   => 'nullable|string',
            'platforms' => 'nullable|array',
            'games'     => 'nullable|array',
        ]);

        if ($request->hasFile('avatar')) {
            // Remove a foto antiga se ela existir
            if ($profile->avatar) {
                $oldPath = str_replace(asset('storage/'), '', $profile->avatar);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = asset('storage/' . $path);
        }

        $profile->update($data);
        return response()->json($profile);
    }

    public function destroy($user_id)
    {
        $profile = Profile::where('user_id', $user_id)->firstOrFail();
        $profile->delete();
        return response()->json(['message' => 'Perfil removido']);
    }
}