<?php

namespace App\Models;

use CodeIgniter\Model;

class KostModel extends Model
{
    protected $table            = 'kosts';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    // protected $allowedFields berfungsi untuk menentukan kolom-kolom data yang dapat di manipulasi melalui model.
    protected $allowedFields    = ['user_id', 'name', 'price', 'latitude', 'longitude', 'is_active', 'is_full', 'image'];

    public function getKostsWithFeatures(): array
    {
        $kosts = $this->where('is_active', 1)->findAll();

        if (empty($kosts)) {
            return [];
        }

        $allFeatures = $this->db->table('kost_features')
            ->join('features', 'features.id = kost_features.feature_id')
            ->select('kost_features.kost_id, features.criteria_id, features.point')
            ->get()
            ->getResultArray();

        $featureMap = [];
        foreach ($allFeatures as $f) {
            $kostId = (int)$f['kost_id'];
            $critId = (int)$f['criteria_id'];
            $featureMap[$kostId][$critId] = ($featureMap[$kostId][$critId] ?? 0) + (float)$f['point'];
        }

        foreach ($kosts as &$kost) {
            $kost['feature_scores'] = $featureMap[(int)$kost['id']] ?? [];
        }

        return $kosts;
    }
}
