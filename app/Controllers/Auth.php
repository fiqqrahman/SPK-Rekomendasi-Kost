<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // 1. Tampilkan Halaman Form Login
    public function login(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to(base_url('/owner/dashboard'));
        }
        return view('login');
    }

    // 2. Validasi Percobaan Login (Strict-Type Handling)
    public function attemptLogin(): \CodeIgniter\HTTP\RedirectResponse
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Cari data user berdasarkan kriteria email
        $user = $this->userModel->where('email', $email)->first();

        if ($user && password_verify((string)$password, (string)$user['password'])) {

            // Taktik Siber: Hancurkan session lama dan buat baru untuk mencegah Session Fixation Attack
            session()->regenerate();

            session()->set([
                'user_id'      => (int)$user['id'],
                'username'     => $user['username'],
                'email'        => $user['email'],
                'is_logged_in' => true
            ]);

            return redirect()->to(base_url('/owner/dashboard'));
        }

        // Jika gagal, kembalikan dengan pesan peringatan tajam
        return redirect()->back()->with('error', 'Kombinasi Email atau Password tidak sesuai!');
    }

    // 3. Proses Log Out (Penghancuran Sesi)
    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->destroy();
        return redirect()->to(base_url('/login'));
    }
}
