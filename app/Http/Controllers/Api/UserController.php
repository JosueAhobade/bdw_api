<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Gestion des comptes :
 *  - superadmin : gère les admins et les comptables
 *  - admin      : gère les comptables
 *  - comptable  : aucun accès
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->whereIn('role', $request->user()->assignableRoles())
            ->with('creator:id,username')
            ->orderBy('role')
            ->orderBy('username')
            ->get(['id', 'username', 'email', 'role', 'is_active', 'created_by', 'last_login_at', 'created_at']);

        return response()->json(['data' => $users]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => ['required', Rule::in($request->user()->assignableRoles())],
        ], [
            'role.in' => "Vous n'avez pas le droit d'attribuer ce rôle.",
        ]);

        $user = User::create([
            ...$data,
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé.',
            'data' => $user,
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        abort_unless($request->user()->canManageUser($user), 403, 'Vous ne pouvez pas modifier cet utilisateur.');

        $data = $request->validate([
            'username' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => ['sometimes', 'required', Rule::in($request->user()->assignableRoles())],
            'is_active' => 'sometimes|boolean',
        ], [
            'role.in' => "Vous n'avez pas le droit d'attribuer ce rôle.",
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        // Mot de passe changé ou compte désactivé : on coupe ses sessions
        if (isset($data['password']) || (isset($data['is_active']) && ! $data['is_active'])) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Utilisateur mis à jour.',
            'data' => $user,
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->canManageUser($user), 403, 'Vous ne pouvez pas supprimer cet utilisateur.');

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }
}
