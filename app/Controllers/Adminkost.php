<?php

namespace App\Controllers;

use App\Models\KostModel;

class Adminkost extends BaseController
{
    protected \CodeIgniter\Database\BaseConnection $db;
    private KostModel $kostModel;

    public function __construct()
    {
        $this->db        = \Config\Database::connect();
        $this->kostModel = new KostModel();
    }

    /**
     * 1. Halaman Dashboard Terpadu (Sudah Terfilter Spesifik Per User)
     */
    public function index(): string
    {
        // Tangkap ID pemilik yang sedang aktif dari sesi login
        $userId = (int)session()->get('user_id');

        // PENGUNCI DATA: Hanya menarik kost yang memiliki user_id cocok dengan si pemilik!
        // Jika ini user baru yang belum punya kost, otomatis tabel bawah akan kosong bersih.
        $myKosts = $this->kostModel->where('user_id', $userId)->findAll();

        return view('adminkost', [
            'myKosts' => $myKosts
        ]);
    }

    /**
     * 2. Menyimpan Properti Baru Terikat Otomatis ke Akun Pemilik
     */
    public function save(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'name'      => 'required|min_length[3]|max_length[150]',
            'price'     => 'required|numeric|greater_than_equal_to[0]',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Format input data tidak valid.');
        }

        $userId    = (int)session()->get('user_id'); // Ambil ID si pemilik dari sesi secure
        $name      = $this->request->getPost('name');
        $price     = $this->request->getPost('price');
        $latitude  = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');
        $features  = $this->request->getPost('features') ?? [];

        $this->db->transStart();

        // Menyuntikkan user_id ke dalam matriks array insert
        $kostData = [
            'user_id'   => $userId, // Kunci kepemilikan aset!
            'name'      => $name,
            'price'     => (float)$price,
            'latitude'  => (float)$latitude,
            'longitude' => (float)$longitude,
            'is_active' => 1,
            'is_full'   => 0
        ];
        $this->kostModel->insert($kostData);

        $kostId = $this->kostModel->getInsertID();

        if (!empty($features) && is_array($features)) {
            $builder = $this->db->table('kost_features');
            $batchData = [];
            foreach ($features as $featureId) {
                $batchData[] = [
                    'kost_id'    => (int)$kostId,
                    'feature_id' => (int)$featureId
                ];
            }
            $builder->insertBatch($batchData);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan internal saat memetakan properti.');
        }

        return redirect()->to(base_url('/owner/dashboard'))->with('success', 'Kost anda sudah didaftarkan');
    }

    /**
     * 3. Saklar Kilat Status Kamar (Diproteksi dari Celah Pembajakan IDOR)
     */
    public function toggleStatus(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $userId = (int)session()->get('user_id');

        // BENTENG PERTAHANAN: Cari kost yang ID-nya cocok DAN user_id-nya adalah milik si login user!
        // Ini memotong celah hacker merubah status kost orang lain via manipulasi angka di URL.
        $kost = $this->kostModel->where('id', $id)->where('user_id', $userId)->first();

        if ($kost) {
            $newStatus = ((int)$kost['is_full'] === 1) ? 0 : 1;

            $this->kostModel->update($id, [
                'is_full' => $newStatus
            ]);
        } else {
            // Jika terdeteksi mencoba menembak ID kost orang lain, lempar error tanpa eksekusi
            return redirect()->to(base_url('/owner/dashboard'))->with('error', 'Akses ditolak.');
        }

        return redirect()->to(base_url('/owner/dashboard'));
    }
}
