<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:Admin,Desarrollador,Ayudante'],
        ]);

        User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'clave' => $request->password,
            'role' => $request->role,
        ]);

        return back()->with('success', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', 'in:Admin,Desarrollador,Ayudante'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $data = [
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['clave'] = $request->password;
        }

        $user->update($data);

        return back()->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        if (User::count() <= 1) {
            return back()->with('error', 'No se puede eliminar el único usuario del sistema.');
        }

        $user->delete();
        return back()->with('success', 'Usuario eliminado exitosamente.');
    }
}
