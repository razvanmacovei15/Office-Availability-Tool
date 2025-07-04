<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Invitation;
use App\Mail\InviteUserMail;
use Spatie\Permission\Models\Role;
use Filament\Notifications\Notification;

class SendInvite extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static string $view = 'filament.pages.send-invite';
    protected static ?string $title = 'Send Invitation';
    protected static ?string $navigationGroup = 'Memberships';


    public $email = '';
    public $role = '';

    public function getRoles()
    {
        return Role::pluck('name', 'name')->toArray();
    }

    public function send()
    {
        $this->validate([
            'email' => 'required|email',
            'role' => 'required',
        ]);

        $token = Str::uuid();

        Invitation::create([
            'email' => $this->email,
            'role' => $this->role,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($this->email)->send(new InviteUserMail($token));

        Notification::make()
            ->title('Invitation sent successfully.')
            ->success()
            ->send();

        $this->email = '';
        $this->role = '';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasRole('admin');
    }
}


