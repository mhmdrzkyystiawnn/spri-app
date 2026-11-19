<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Menampilkan form login admin meja layanan.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.verifications.index');
        }

        return view('auth.login');
    }

    /**
     * Memproses autentikasi petugas admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email dinas wajib diisi.',
            'email.email' => 'Format email dinas tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Kombinasi email dan kata sandi tidak sesuai dengan data petugas berwenang.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.verifications.index'));
    }

    /**
     * Keluar dari sesi administrasi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem meja layanan.');
    }
}
