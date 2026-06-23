<?php
// Engine forward chaining untuk sistem pakar diagnosa penyakit gigi

require_once __DIR__ . '/config/database.php';

class ForwardChaining {
    private $conn;
    private $faktaAwal;     // gejala yang dipilih pasien
    private $hasilDiagnosa;

    public function __construct($conn, $gejalaDipilih) {
        $this->conn = $conn;
        $this->faktaAwal = $gejalaDipilih;
        $this->hasilDiagnosa = [];
    }

    // Jalankan forward chaining berdasarkan gejala yang dipilih pasien
    public function diagnosa() {
        // Ambil semua aturan sekaligus pakai 1 query JOIN
        // (hindari N+1: sebelumnya ada 1 query per penyakit di dalam loop)
        $sql = "SELECT a.penyakit_id, p.kode, p.nama, p.deskripsi, p.solusi,
                       a.gejala_id, g.nama AS nama_gejala, g.kode AS kode_gejala
                FROM aturan a
                JOIN penyakit p ON a.penyakit_id = p.id
                JOIN gejala g ON a.gejala_id = g.id
                ORDER BY p.kode, g.kode";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        // Group hasil query ke dalam array berdasarkan penyakit_id
        $basisPengetahuan = [];
        while ($row = $result->fetch_assoc()) {
            $pid = $row['penyakit_id'];
            if (!isset($basisPengetahuan[$pid])) {
                $basisPengetahuan[$pid] = [
                    'id'       => $pid,
                    'kode'     => $row['kode'],
                    'nama'     => $row['nama'],
                    'deskripsi'=> $row['deskripsi'],
                    'solusi'   => $row['solusi'],
                    'gejala'   => [],
                ];
            }
            $basisPengetahuan[$pid]['gejala'][] = [
                'id'   => $row['gejala_id'],
                'kode' => $row['kode_gejala'],
                'nama' => $row['nama_gejala'],
            ];
        }

        // Proses inferensi: cocokkan fakta pasien dengan setiap aturan penyakit
        foreach ($basisPengetahuan as $penyakit) {
            $gejalaCocok  = [];
            $gejalaKurang = [];

            foreach ($penyakit['gejala'] as $g) {
                if (in_array($g['id'], $this->faktaAwal)) {
                    $gejalaCocok[] = $g;
                } else {
                    $gejalaKurang[] = $g;
                }
            }

            $totalGejala = count($penyakit['gejala']);
            $jumlahCocok = count($gejalaCocok);

            if ($jumlahCocok === 0) continue;

            $persentase = ($jumlahCocok / $totalGejala) * 100;

            // Simpan data penyakit tanpa key 'gejala' agar formatnya sama seperti sebelumnya
            $dataPenyakit = [
                'id'       => $penyakit['id'],
                'kode'     => $penyakit['kode'],
                'nama'     => $penyakit['nama'],
                'deskripsi'=> $penyakit['deskripsi'],
                'solusi'   => $penyakit['solusi'],
            ];

            $this->hasilDiagnosa[] = [
                'penyakit'      => $dataPenyakit,
                'gejala_cocok'  => $gejalaCocok,
                'gejala_kurang' => $gejalaKurang,
                'total_gejala'  => $totalGejala,
                'jumlah_cocok'  => $jumlahCocok,
                'persentase'    => round($persentase, 2),
            ];
        }

        // Urutkan dari persentase tertinggi ke terendah
        usort($this->hasilDiagnosa, function($a, $b) {
            return $b['persentase'] <=> $a['persentase'];
        });

        return $this->hasilDiagnosa;
    }

    // Ambil hasil dengan persentase tertinggi
    public function getHasilUtama() {
        return $this->hasilDiagnosa[0] ?? null;
    }

    // Ambil semua hasil diagnosa
    public function getAllHasil() {
        return $this->hasilDiagnosa;
    }
}
?>
