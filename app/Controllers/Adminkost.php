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


    // Halaman Dashboard (Sudah Terfilter Spesifik Per User)
    public function index(): string
    {
        // Tangkap ID pemilik yang sedang aktif dari sesi login
        $userId = (int)session()->get('user_id');

        /**
         * Kendali data - hanya menarik kost yang memiliki user_id cocok dengan si pemilik!
         * Jika user baru yang belum punya kost, otomatis tabel bawah akan kosong, user diharuskan menginputkan data kost miliknya sendiri terlebih dahulu.
         */

        $myKosts = $this->kostModel->where('user_id', $userId)->findAll();

        return view('adminkost', [
            'myKosts' => $myKosts
        ]);
    }

    // Fungsi untuk menyimpan data kost baru ke database
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

        $userId    = (int)session()->get('user_id');
        $name      = $this->request->getPost('name');
        $price     = $this->request->getPost('price');
        $latitude  = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');
        $features  = $this->request->getPost('features') ?? [];

        // Pemrosesan Array Berkas Gambar Ganda secara Kolektif
        $uploadedImages = [];
        $imageFiles = $this->request->getFileMultiple('images'); // Menangkap array file

        if ($imageFiles) {
            foreach ($imageFiles as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    // Validasi tipe ekstensi secara manual demi menjaga performa
                    $mime = $file->getClientMimeType();
                    if (in_array($mime, ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'])) {
                        $encryptedName = $file->getRandomName();
                        $file->move(ROOTPATH . 'public/uploads/kosts/', $encryptedName);
                        $uploadedImages[] = $encryptedName;
                    }
                }
            }
        }

        $this->db->transStart();

        $kostData = [
            'user_id'   => $userId,
            'name'      => $name,
            'price'     => (float)$price,
            'latitude'  => (float)$latitude,
            'longitude' => (float)$longitude,
            'is_active' => 1,
            'is_full'   => 0,
            'image'     => !empty($uploadedImages) ? json_encode($uploadedImages) : null // Disimpan sebagai JSON string
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
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan internal.');
        }

        return redirect()->to(base_url('/owner/dashboard'))->with('success', 'Kost antum berhasil didaftarkan.');
    }
    // Toggle status kost (is_full) dengan proteksi IDOR
    public function toggleStatus(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $userId = (int)session()->get('user_id');

        /**
         * Keamanan Data: hanya menampilkan kost yang ID-nya cocok DAN user_id-nya adalah milik si login user
         * untuk mencegak adanya aktivitas merubah status kost orang lain via manipulasi angka di URL.
         */

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

    /**
     * Fungsi untuk menghapus kost beserta seluruh berkas foto fisiknya dari penyimpanan
     */
    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $userId = (int)session()->get('user_id');

        // Proteksi IDOR Barrier
        $kost = $this->kostModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$kost) {
            return redirect()->to(base_url('/owner/dashboard'))->with('error', 'Akses ilegal! Properti tidak ditemukan.');
        }

        // Cari file gambar terkait kost yang akan dihapus, lalu hapus file fisiknya dari penyimpanan
        if (!empty($kost['image'])) {
            $imagesArray = json_decode($kost['image'], true);
            if (is_array($imagesArray)) {
                foreach ($imagesArray as $fileName) {
                    $physicalPath = ROOTPATH . 'public/uploads/kosts/' . $fileName;
                    if (file_exists($physicalPath)) {
                        unlink($physicalPath); // Menghapus file dari penyimpanan
                    }
                }
            }
        }

        $this->db->transStart();
        $this->db->table('kost_features')->where('kost_id', $id)->delete();
        $this->kostModel->delete($id);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses penghapusan data.');
        }

        return redirect()->to(base_url('/owner/dashboard'))->with('success', 'Aset kost berhasil dihapus.');
    }
}
