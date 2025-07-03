<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Mail\InviteUserMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class InviteController extends Controller
{
    public function invite(Request $request)
    {

        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }
        $request->validate([
            'email' => 'required|email|unique:invitations,email',
            'role' => 'required|in:' . implode(',', Role::pluck('name')->toArray()),
        ]);

        $token = Str::uuid();

        Invitation::create([
            'email' => $request->email,
            'role' => $request->role,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($request->email)->send(new InviteUserMail($token));

        return back()->with('success', 'Invitation sent!');
    }
}
