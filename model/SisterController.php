<?php
class SisterClient {
    private $baseUrl;
    private $username;
    private $password;
    private $idPengguna;
    private $token;

    public function __construct($baseUrl, $username, $password, $idPengguna) {
        $this->baseUrl    = rtrim($baseUrl, '/');
        $this->username   = $username;
        $this->password   = $password;
        $this->idPengguna = $idPengguna;
    }

    // Ambil token dari endpoint /authorize
    public function authorize() {
        $url = $this->baseUrl . "/authorize";

        $data = array(
            "username"    => $this->username,
            "password"    => $this->password,
            "id_pengguna" => $this->idPengguna
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['token'])) {
            $this->token = $result['token'];
            return $this->token;
        } else {
            throw new Exception("Gagal authorize: " . $response);
        }
    }

    // Request ke endpoint lain dengan token
    public function request($endpoint) {
        if (!$this->token) {
            $this->authorize();
        }

        $url = $this->baseUrl . "/" . ltrim($endpoint, '/');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer {$this->token}",
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // Contoh fungsi ambil data dosen
    public function getDosen() {
        return $this->request("/referensi/sdm");
    }
	
	public function getReferensi($name) {
        return $this->request("/referensi/" . $name);
    }


    // Contoh fungsi ambil data publikasi
   public function getInpassing($id_sdm) { 
   return $this->request("inpassing?id_sdm=" . $id_sdm);
   }
   
   public function getdataAlamat($id_sdm) { 
   return $this->request("data_pribadi/alamat/" . $id_sdm);
   }
      
    // Referensi endpoints
    public function kategoriCapaianLuaran() { return $this->request("/referensi/kategori_capaian_luaran"); }
    public function perguruanTinggi()       { return $this->request("/referensi/perguruan_tinggi"); }
    public function unitKerja()             { return $this->request("/referensi/unit_kerja"); }
    public function detailUnitKerja()       { return $this->request("/referensi/detail_unit_kerja"); }
    public function mahasiswaPddikti()      { return $this->request("/referensi/mahasiswa_pddikti"); }
    public function agama()                 { return $this->request("/referensi/agama"); }
    public function bidangStudi()           { return $this->request("/referensi/bidang_studi"); }
    public function bidangUsaha()           { return $this->request("/referensi/bidang_usaha"); }
    public function dudi()                  { return $this->request("/referensi/dudi"); }
    public function gelarAkademik()         { return $this->request("/referensi/gelar_akademik"); }
    public function golonganPangkat()       { return $this->request("/referensi/golongan_pangkat"); }
    public function ikatanKerja()           { return $this->request("/referensi/ikatan_kerja"); }
    public function jenisDokumen()          { return $this->request("/referensi/jenis_dokumen"); }
    public function jabatanFungsional()     { return $this->request("/referensi/jabatan_fungsional"); }
    public function jabatanNegara()         { return $this->request("/referensi/jabatan_negara"); }
    public function jabatanTugasTambahan()  { return $this->request("/referensi/jabatan_tugas_tambahan"); }
    public function jenisBahanAjar()        { return $this->request("/referensi/jenis_bahan_ajar"); }
    public function jenisPenghargaan()      { return $this->request("/referensi/jenis_penghargaan"); }
    public function jenisKepanitiaan()      { return $this->request("/referensi/jenis_kepanitiaan"); }
    public function jenisKesejahteraan()    { return $this->request("/referensi/jenis_kesejahteraan"); }
    public function jenisBeasiswa()         { return $this->request("/referensi/jenis_beasiswa"); }
    public function jenisDiklat()           { return $this->request("/referensi/jenis_diklat"); }
    public function jenisKeluar()           { return $this->request("/referensi/jenis_keluar"); }
    public function jenisPekerjaan()        { return $this->request("/referensi/jenis_pekerjaan"); }
    public function jenisPublikasi()        { return $this->request("/referensi/jenis_publikasi"); }
    public function jenisTes()              { return $this->request("/referensi/jenis_tes"); }
    public function jenisTunjangan()        { return $this->request("/referensi/jenis_tunjangan"); }
    public function jenjangPendidikan()     { return $this->request("/referensi/jenjang_pendidikan"); }
    public function profilPT()              { return $this->request("/referensi/profil_pt"); }
    public function statusKepegawaian()     { return $this->request("/referensi/status_kepegawaian"); }
    public function skimKegiatan()          { return $this->request("/referensi/skim_kegiatan"); }
    public function tingkatPenghargaan()    { return $this->request("/referensi/tingkat_penghargaan"); }
    public function mediaPublikasi()        { return $this->request("/referensi/media_publikasi"); }
    public function negara()                { return $this->request("/referensi/negara"); }
    public function kategoriKegiatan()      { return $this->request("/referensi/kategori_kegiatan"); }
    public function kelompokBidang()        { return $this->request("/referensi/kelompok_bidang"); }
    public function lembagaSertifikasi()    { return $this->request("/referensi/lembaga_sertifikasi"); }
    public function wilayah()               { return $this->request("/referensi/wilayah"); }
    public function semester()              { return $this->request("/referensi/semester"); }
    public function sumberGaji()            { return $this->request("/referensi/sumber_gaji"); }

}
