<?php
/**
 * ENGINE FORWARD CHAINING
 * ============================================================
 * Ini adalah inti dari sistem pakar. Metode Forward Chaining
 * bekerja dengan cara:
 * 
 * 1. Menerima FAKTA (gejala yang dipilih pasien)
 * 2. Mencocokkan fakta dengan ATURAN di basis pengetahuan
 * 3. Jika semua gejala dalam suatu aturan terpenuhi,
 *    maka penyakit tersebut terdiagnosa
 * 4. Menghitung persentase kesesuaian gejala
 * 
 * Alur Forward Chaining:
 * FAKTA (Gejala Pasien) --> MESIN INFERENSI --> KESIMPULAN (Diagnosa)
 * ============================================================
 */

require_once __DIR__ . '/config/database.php';

class ForwardChaining {
    private $conn;           // Koneksi database
    private $faktaAwal;      // Gejala yang dipilih pasien (Working Memory)
    private $hasilDiagnosa;  // Hasil diagnosa akhir
    
    /**
     * Constructor - inisialisasi engine dengan koneksi DB dan fakta awal
     * @param mysqli $conn - Koneksi database
     * @param array $gejalaDipilih - Array ID gejala yang dipilih pasien
     */
    public function __construct($conn, $gejalaDipilih) {
        $this->conn = $conn;
        $this->faktaAwal = $gejalaDipilih; // Fakta awal = gejala yang dipilih
        $this->hasilDiagnosa = [];
    }
    
    /**
     * FUNGSI UTAMA: Jalankan proses Forward Chaining
     * 
     * Proses:
     * 1. Ambil semua penyakit dari database
     * 2. Untuk setiap penyakit, ambil gejala yang dibutuhkan (aturan)
     * 3. Cocokkan dengan fakta awal (gejala pasien)
     * 4. Hitung persentase kecocokan
     * 5. Simpan sebagai hasil diagnosa
     */
    public function diagnosa() {
        // LANGKAH 1: Ambil semua penyakit dari database
        $queryPenyakit = "SELECT * FROM penyakit ORDER BY kode";
        $resultPenyakit = $this->conn->query($queryPenyakit);
        
        while ($penyakit = $resultPenyakit->fetch_assoc()) {
            // LANGKAH 2: Ambil semua gejala yang diperlukan untuk penyakit ini
            $queryAturan = "SELECT g.id, g.kode, g.nama 
                           FROM aturan a 
                           JOIN gejala g ON a.gejala_id = g.id 
                           WHERE a.penyakit_id = ?
                           ORDER BY g.kode";
            $stmt = $this->conn->prepare($queryAturan);
            $stmt->bind_param('i', $penyakit['id']);
            $stmt->execute();
            $resultAturan = $stmt->get_result();
            
            $gejalaPenyakit = []; // Gejala yang dibutuhkan penyakit ini
            while ($gejala = $resultAturan->fetch_assoc()) {
                $gejalaPenyakit[] = $gejala;
            }
            
            if (empty($gejalaPenyakit)) continue; // Skip jika tidak ada aturan
            
            // LANGKAH 3: PROSES INFERENSI FORWARD CHAINING
            // Cocokkan fakta (gejala pasien) dengan gejala yang dibutuhkan
            $gejalaCocok = [];    // Gejala yang cocok
            $gejalaKurang = [];   // Gejala yang tidak cocok
            
            foreach ($gejalaPenyakit as $g) {
                if (in_array($g['id'], $this->faktaAwal)) {
                    // Fakta terpenuhi: pasien memiliki gejala ini
                    $gejalaCocok[] = $g;
                } else {
                    // Fakta tidak terpenuhi: pasien tidak memiliki gejala ini
                    $gejalaKurang[] = $g;
                }
            }
            
            // LANGKAH 4: Hitung persentase kecocokan
            $totalGejala = count($gejalaPenyakit);
            $jumlahCocok = count($gejalaCocok);
            $persentase = ($jumlahCocok / $totalGejala) * 100;
            
            // LANGKAH 5: Simpan hasil jika ada minimal 1 gejala yang cocok
            // Minimal 50% gejala harus cocok agar dianggap terdiagnosa
            if ($jumlahCocok > 0) {
                $this->hasilDiagnosa[] = [
                    'penyakit'        => $penyakit,
                    'gejala_cocok'    => $gejalaCocok,
                    'gejala_kurang'   => $gejalaKurang,
                    'total_gejala'    => $totalGejala,
                    'jumlah_cocok'    => $jumlahCocok,
                    'persentase'      => round($persentase, 2),
                ];
            }
        }
        
        // Urutkan hasil berdasarkan persentase tertinggi ke terendah
        usort($this->hasilDiagnosa, function($a, $b) {
            return $b['persentase'] <=> $a['persentase'];
        });
        
        return $this->hasilDiagnosa;
    }
    
    /**
     * Ambil hasil diagnosa utama (persentase tertinggi)
     */
    public function getHasilUtama() {
        if (!empty($this->hasilDiagnosa)) {
            return $this->hasilDiagnosa[0];
        }
        return null;
    }
    
    /**
     * Ambil semua hasil diagnosa
     */
    public function getAllHasil() {
        return $this->hasilDiagnosa;
    }
}
?>
