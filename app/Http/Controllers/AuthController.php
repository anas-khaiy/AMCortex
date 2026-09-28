<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // Page login et register
    public function showLogin() {
        return view('auth.login'); 
    }
    public function showRegister() {
        return view('auth.register');
    }

    // Logique d'Inscription (Stockage en BD)
    public function register(Request $request) {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => trim($request->first_name . ' ' . $request->last_name),
            'email' => $request->email,
            'password' => Hash::make($request->password), // Cryptage du mot de passe
        ]);

        Auth::login($user); 
        return redirect()->route('dashboard');
    }

    // Logique de Connexion
    public function login(Request $request) {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $login = $request->login;

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        $credentials = [
           $field => $login,
           'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            if (auth()->user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.']);
    }

    // Logique de Déconnexion
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }


    // Afficher le profil de l'enseignant
    public function profile() {
        return view('auth.profile');
    }
    
    


    // Afficher les paramètres de compte
    public function settings()
    {
        return view('auth.settings');
    }

   
    // Mettre à jour les informations du profil
public function updateProfile(Request $request)
{
    $request->validate([
        'username' => 'required|string|max:255|unique:users,username,' . auth()->id(),
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
    ]);

    auth()->user()->update([
        'username' => $request->username,
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
    ]);

    return back()->with('success', 'Profil mis à jour avec succès.');
}

 // Mettre à jour le mot de passe

public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if (!Hash::check($request->current_password, auth()->user()->password)) {
        return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
    }

    auth()->user()->update([
        'password' => Hash::make($request->password),
    ]);

    return back()->with('success', 'Mot de passe modifié avec succès.');
}


public function showForgotPassword()
{
    return view('auth.forgot-password');
}

public function sendResetLink(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ], [
        'email.exists' => 'Aucun compte n’est associé à cette adresse email.',
    ]);


    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? back()->with('success', 'Lien de réinitialisation envoyé à votre adresse email.')
        : back()->withErrors(['email' => 'Impossible d’envoyer le lien.']);
}

public function showResetPassword(Request $request, string $token)
{
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->email,
    ]);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('success', 'Mot de passe modifié avec succès.')
        : back()->withErrors(['email' => 'Le lien est invalide ou expiré.']);
}

public function showForgotEmail()
{
    return view('emails.forgot-email');
}

public function sendForgotEmail(Request $request)
{
    $request->validate([
    'username' => 'required|string|exists:users,username',
], [
    'username.required' => 'Veuillez saisir votre nom d’utilisateur.',
    'username.exists' => 'Aucun compte n’est associé à ce nom d’utilisateur. Vérifiez les informations saisies puis réessayez.',
]);

    $user = User::where('username', $request->username)->first();

    if (!$user) {
        return back()->withErrors([
            'username' => 'Aucun compte trouvé avec ce nom d’utilisateur.'
        ]);
    }

    $token = Password::createToken($user);

    $resetUrl = url(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
    ], false));

    Mail::send('emails.forgot-email', [
        'user' => $user,
        'resetUrl' => $resetUrl,
    ], function ($message) use ($user) {
        $message->to($user->email)
            ->subject('Récupération de votre compte AMCortex');
    });

    return back()->with(
        'success',
        'Un email contenant vos informations de connexion a été envoyé à l’adresse associée à ce compte.'
    );
}
}