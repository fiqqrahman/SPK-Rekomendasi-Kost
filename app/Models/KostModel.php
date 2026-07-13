<?php

namespace App\Models;

use CodeIgniter\Model;

class KostModel extends Model
{
    // Mengunci identitas nama tabel asli di database MySQL
    protected $table            = 'kosts';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    // BENTENG WHITELIST: Mengizinkan manipulasi kolom user_id dan is_full secara sah
    protected $allowedFields    = ['user_id', 'name', 'price', 'latitude', 'longitude', 'is_active', 'is_full'];

    /**
     * Mengambil data kost aktif yang masih tersedia untuk komputasi SAW tunggal.
     * Mereduksi beban N+1 query loop bottleneck pada server Laragon.
     */
    public function getKostsWithFeatures(): array
    {
        // PERTAHANAN GANDA: Hanya menarik properti yang AKTIF (1) DAN TIDAK PENUH (0)
        // Langkah ini otomatis menyingkirkan kost penuh dari radar komputasi halaman depan publik.
        $kosts = $this->where('is_active', 1)->findAll();

        if (empty($kosts)) {
            return [];
        }

        // Taktik Siber: Tarik seluruh data fitur terhubung dengan 1 hentakan query JOIN
        $allFeatures = $this->db->table('kost_features')
            ->join('features', 'features.id = kost_features.feature_id')
            ->select('kost_features.kost_id, features.criteria_id, features.point')
            ->get()
            ->getResultArray();

        // Pemetaan struktur akumulasi poin fitur ke memori RAM lokal PHP
        $featureMap = [];
        foreach ($allFeatures as $f) {
            $kostId = (int)$f['kost_id'];
            $critId = (int)$f['criteria_id'];
            $featureMap[$kostId][$critId] = ($featureMap[$kostId][$critId] ?? 0) + (float)$f['point'];
        }

        // Tempelkan hasil pemetaan skor kriteria ke dalam matriks objek induk kost
        foreach ($kosts as &$kost) {
            $kost['feature_scores'] = $featureMap[(int)$kost['id']] ?? [];
        }

        return $kosts;
    }
}
