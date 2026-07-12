<?php

namespace App\Models;

use CodeIgniter\Model;

class KostModel extends Model
{
    // Mengunci identitas nama tabel di database
    protected $table            = 'kosts';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    // BENTENG WHITELIST: Menambahkan 'user_id' dan 'is_full' agar lolos sensor Mass Assignment
    protected $allowedFields    = ['user_id', 'name', 'price', 'latitude', 'longitude', 'is_active', 'is_full'];

    /**
     * Mengambil data kost aktif dengan agregasi poin fitur menggunakan 1 query tunggal.
     * Mengeliminasi celah performa N+1 query loop bottleneck.
     */
    public function getKostsWithFeatures(): array
    {
        $kosts = $this->where('is_active', 1)->findAll();

        if (empty($kosts)) {
            return [];
        }

        // Taktik Siber: Tarik semua fitur terhubung dalam satu hentakan query JOIN
        $allFeatures = $this->db->table('kost_features')
            ->join('features', 'features.id = kost_features.feature_id')
            ->select('kost_features.kost_id, features.criteria_id, features.point')
            ->get()
            ->getResultArray();

        // Pemetaan struktur poin ke memori lokal PHP
        $featureMap = [];
        foreach ($allFeatures as $f) {
            $kostId = (int)$f['kost_id'];
            $critId = (int)$f['criteria_id'];
            $featureMap[$kostId][$critId] = ($featureMap[$kostId][$critId] ?? 0) + (float)$f['point'];
        }

        // Tempelkan hasil kalkulasi poin fitur ke objek induk kost
        foreach ($kosts as &$kost) {
            $kost['feature_scores'] = $featureMap[(int)$kost['id']] ?? [];
        }

        return $kosts;
    }
}
