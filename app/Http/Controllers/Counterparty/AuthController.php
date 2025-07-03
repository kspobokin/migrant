<?php
namespace App\Http\Controllers\Counterparty;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Services\Transliterator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('counterparty.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'last_name_ru' => 'required|string|max:255',
            'first_name_ru' => 'required|string|max:255',
            'patronymic_ru' => 'required|string|max:255',
            'email' => 'required_without:phone|email|unique:contractors,email',
            'phone' => 'required_without:email|string|max:20|unique:contractors,phone',
            'password' => 'required|string|min:8|confirmed',
            'type' => 'required|in:individual,legal',
            'role' => 'required|in:customer,performer',
            'inn' => 'nullable|string|regex:/^\d{10}$|regex:/^\d{12}$/',
            'insurance_policy' => 'nullable|string|max:50',
            'registration_address' => 'nullable|string|max:255',
            'extra_fields' => 'nullable|json'
        ]);

        $data['last_name_lat'] = Transliterator::transliterate($data['last_name_ru']);
        $data['first_name_lat'] = Transliterator::transliterate($data['first_name_ru']);
        $data['patronymic_lat'] = Transliterator::transliterate($data['patronymic_ru']);
        $data['password'] = Hash::make($data['password']);

        $contractor = Contractor::create($data);
        Auth::guard('counterparty')->login($contractor);
        return redirect()->route('counterparty.profile')->with('success', 'Registration successful');
    }

    public function showLogin()
    {
        return view('counterparty.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [$field => $request->input($field ?: 'email'), 'password' => $request->input('password')];

        if (Auth::guard('counterparty')->attempt($credentials)) {
            return redirect()->route('counterparty.profile')->with('success', 'Login successful');
        }
        return back()->withErrors(['login' => 'Invalid credentials']);
    }

    public function showForgotPassword()
    {
        return view('counterparty.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::broker('contractors')->sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword($token)
    {
        return view('counterparty.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::broker('contractors')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('counterparty.login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
