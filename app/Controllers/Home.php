<?php

namespace App\Controllers;

use App\Models\CampusModel;
use App\Models\CriteriaModel;
use App\Models\KostModel;

class Home extends BaseController
{
    protected \CodeIgniter\Database\BaseConnection $db;
    private CampusModel $campusModel;
    private CriteriaModel $criteriaModel;
    private KostModel $kostModel;

    public function __construct()
    {
        $this->db            = \Config\Database::connect();
        $this->campusModel   = new CampusModel();
        $this->criteriaModel = new CriteriaModel();
        $this->kostModel     = new KostModel();
    }

    public function index(): string
    {
        $campuses = $this->campusModel->findAll();
        $selectedCampusId = $this->request->getGet('campus_id') ?? ($campuses[0]['id'] ?? null);
        $lifestyle = $this->request->getGet('lifestyle') ?? 'default';

        $currentCampus = null;
        foreach ($campuses as $campus) {
            if ($campus['id'] == $selectedCampusId) {
                $currentCampus = $campus;
                break;
            }
        }

        $criterias = $this->criteriaModel->findAll();

        $weights = [];
        foreach ($criterias as $crit) {
            $weights[$crit['code']] = (float)$crit['weight'];
        }

        if ($lifestyle === 'mendang_mending') {
            $weights['C1'] = 0.50; // Harga dominan
            $weights['C2'] = 0.40; // Jarak dominan
            $weights['C3'] = 0.05; // Parkir dipangkas
            $weights['C4'] = 0.05; // Keamanan dipangkas
        } elseif ($lifestyle === 'anak_sultan') {
            $weights['C1'] = 0.10;
            $weights['C2'] = 0.10;
            $weights['C3'] = 0.30; // Parkir naik porsi
            $weights['C4'] = 0.50; // Keamanan berkuasa mutlak
        }

        foreach ($criterias as &$crit) {
            $crit['weight'] = $weights[$crit['code']];
        }

        $kostsRaw = $this->kostModel->where('is_active', 1)->findAll();

        if (empty($currentCampus) || empty($kostsRaw)) {
            return view('home', [
                'campuses' => $campuses,
                'results' => [],
                'selectedCampusId' => $selectedCampusId,
                'currentCampus' => $currentCampus,
                'selectedLifestyle' => $lifestyle
            ]);
        }

        $allFeatures = $this->db->table('kost_features')
            ->join('features', 'features.id = kost_features.feature_id')
            ->select('kost_features.kost_id, features.name as feature_name, features.criteria_id, features.point')
            ->get()
            ->getResultArray();

        $featureMap = [];
        $featureNamesMap = [];
        foreach ($allFeatures as $f) {
            $kId = (int)$f['kost_id'];
            $cId = (int)$f['criteria_id'];
            $featureMap[$kId][$cId] = ($featureMap[$kId][$cId] ?? 0) + (float)$f['point'];
            $featureNamesMap[$kId][] = $f['feature_name']; // Simpan nama teks fasilitas
        }

        $alternatives = [];
        foreach ($kostsRaw as $kost) {
            $distance = $this->calculateHaversine(
                (float)$currentCampus['latitude'],
                (float)$currentCampus['longitude'],
                (float)$kost['latitude'],
                (float)$kost['longitude']
            );

            $scores = [
                1 => (float)$kost['price'],
                2 => (float)$distance,
                3 => (float)($featureMap[(int)$kost['id']][3] ?? 0.0),
                4 => (float)($featureMap[(int)$kost['id']][4] ?? 0.0),
            ];

            $alternatives[] = [
                'id'        => $kost['id'],
                'name'      => $kost['name'],
                'price'     => $kost['price'],
                'distance'  => $distance,
                'latitude'  => $kost['latitude'],
                'longitude' => $kost['longitude'],
                'features'  => $featureNamesMap[(int)$kost['id']] ?? [],
                'scores'    => $scores
            ];
        }

        $minMax = [];
        foreach ($criterias as $crit) {
            $cId = (int)$crit['id'];
            $allScoresForCrit = array_column(array_column($alternatives, 'scores'), $cId);
            $minMax[$cId] = ($crit['type'] === 'cost')
                ? (!empty($allScoresForCrit) ? min($allScoresForCrit) : 0.0)
                : (!empty($allScoresForCrit) ? max($allScoresForCrit) : 0.0);
        }

        $finalRankings = [];
        foreach ($alternatives as $alt) {
            $totalScore = 0.0;
            foreach ($criterias as $crit) {
                $cId = (int)$crit['id'];
                $score = $alt['scores'][$cId] ?? 0.0;
                $weight = (float)$crit['weight'];

                if ($minMax[$cId] == 0.0 || $score == 0.0) continue;

                $normalized = ($crit['type'] === 'cost') ? ($minMax[$cId] / $score) : ($score / $minMax[$cId]);
                $totalScore += $normalized * $weight;
            }

            $finalRankings[] = [
                'name'        => $alt['name'],
                'price'       => $alt['price'],
                'distance'    => round($alt['distance'], 2),
                'final_score' => round($totalScore, 4),
                'latitude'    => $alt['latitude'],
                'longitude'   => $alt['longitude'],
                'features'    => $alt['features'] // Mengirim data array fasilitas ke view
            ];
        }

        usort($finalRankings, fn(array $a, array $b): int => $b['final_score'] <=> $a['final_score']);

        return view('home', [
            'campuses'          => $campuses,
            'results'           => $finalRankings,
            'selectedCampusId'  => $selectedCampusId,
            'currentCampus'     => $currentCampus,
            'selectedLifestyle' => $lifestyle
        ]);
    }

    private function calculateHaversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
