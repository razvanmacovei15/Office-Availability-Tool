<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request)
    {
        $token = $request->query('token');

        // Look up the invitation by token
        $invitation = Invitation::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return view('mail.register', [
            'token' => $token,
            'submitRoute' => route('invite.submit'),
            'email' => $invitation->email,  // ✅ NOW email is defined
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required|string',
        ]);

        $invite = Invitation::where('token', $request->token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $user = User::create([
            'name' => $request->name,
            'email' => $invite->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($invite->role);

        $invite->update(['used' => true]);

        auth()->login($user);

        return redirect('/');
    }
}
