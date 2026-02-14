<?php


class ProfilController extends Controller
{
	/**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();
		Auth::checkAuthentication();
	}
	
public function jabatanFungsional($uid) 
	{
    $role = Session::get('user_provider_type');
    if ($role === 'pegawai') {
        $uid = Session::get('uid');
    }
	
	if ($role === 'employee') {
        $uid = (int) $uid; // cast ke int untuk keamanan
    }
		
		$pegawai = "SELECT						
					`users`.`uid`,
					`users`.`nisn`,					                   
					`users`.`user_name`,					
					`users`.`id_sdm`,
					`users`.`nuptk`,
					`users`.`nip`,
                    `users`.`nama_sdm`,					
					`users`.`department`,					
					`users`.`jenis_sdm`,	
					`users`.`sister`								
					FROM					
					`users`					
					WHERE
					`users`.`uid` = '$uid' AND `users`.`is_active` = 1 LIMIT 1";		
		$jabatan = "SELECT
					`jabatan_fungsional`.`uid`,																						
					`jabatan_fungsional`.`uid_sdm`,									
					`jabatan_fungsional`.`id_sdm`,									
					`jabatan_fungsional`.`id_dokumen`,									
					`jabatan_fungsional`.`jabatan_fungsional`,									
					`jabatan_fungsional`.`sk`,									
					`jabatan_fungsional`.`tanggal_mulai`,									
					`jabatan_fungsional`.`id_stat_pegawai`,									
					`jabatan_fungsional`.`nm_stat_pegawai`,									
					`jabatan_fungsional`.`created_at`,									
					`jabatan_fungsional`.`sister`
					FROM
					`jabatan_fungsional`
					where
					`jabatan_fungsional`.`uid_sdm` = '$uid'
					";
					
        $commonData = array(
                 'title' => 'Jabatan Fungsional',                
                'pegawai' => GenericModel::rawSelect($pegawai, false),
                'jabatan_fungsional' => GenericModel::rawSelect($jabatan),
				'alamat_pegawai' => GenericModel::getAll('alamat_pegawai'),
				'department' => GenericModel::getAll('department'),
				'data_wilayah' => GenericModel::getAll('data_wilayah')				
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/jabatan_fungsional', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/jabatan_fungsional', $commonData);
		} 	
    }
	


public function dataJabfung($id_dokumen) 
	{
    $role = Session::get('user_provider_type');
	
		$field = '`users`.`uid`,
					`users`.`nama_sdm`,					
					`users`.`department`';
        $table = '`users`';
        $where = "`users`.`user_provider_type` = 'pegawai' AND `users`.`jenis_sdm` = 'Dosen' AND `users`.`is_deleted` = 0 ";
        $pegawai = GenericModel::getAll($table, $where, $field);
		$jabatan = "SELECT
					`jabatan_fungsional`.`uid`,																						
					`jabatan_fungsional`.`uid_sdm`,									
					`jabatan_fungsional`.`id_sdm`,									
					`jabatan_fungsional`.`id_dokumen`,									
					`jabatan_fungsional`.`jabatan_fungsional`,									
					`jabatan_fungsional`.`sk`,														
					`jabatan_fungsional`.`tanggal_mulai`,
					`jabatan_fungsional`.`id_stat_pegawai`,
					`jabatan_fungsional`.`nm_stat_pegawai`,					
					`jabatan_fungsional`.`sister`
					FROM
					`jabatan_fungsional`
					where
					`jabatan_fungsional`.`id_dokumen` = '$id_dokumen' LIMIT 1
					";
		$field = '`dokumen_pegawai`.`uid`,
					`dokumen_pegawai`.`kategori`,
					`dokumen_pegawai`.`uid_sdm`,
					`dokumen_pegawai`.`id_dokumen`,
					`dokumen_pegawai`.`item_name`,
					`dokumen_pegawai`.`value`,
					`dokumen_pegawai`.`created_at`,
					`dokumen_pegawai`.`note`
					';
        $table = '`dokumen_pegawai`';
        $where = "`dokumen_pegawai`.`id_dokumen` = '$id_dokumen' AND `dokumen_pegawai`.`is_deleted` = 0";
        $dokumen = GenericModel::getAll($table, $where, $field);
					
        $commonData = array(
                 'title' => 'Detail Data Jabatan Fungsional',				
                'jabatan_fungsional' => GenericModel::rawSelect($jabatan, false),				
				'kategori' => GenericModel::getAll('kategori_dokumen'),
				'link' => 'dataJabfung',
				'pegawai' => $pegawai,
				'dokumen' => $dokumen							
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/dataJabfung', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/dataJabfung', $commonData);
		} 	
    }
	
	public function inpassing($uid) 
	{
    $role = Session::get('user_provider_type');
    if ($role === 'pegawai') {
        $uid = Session::get('uid');
    }
	
	if ($role === 'employee') {
        $uid = (int) $uid; // cast ke int untuk keamanan
    }
		
		$pegawai = "SELECT						
					`users`.`uid`,
					`users`.`nisn`,					                   
					`users`.`user_name`,					
					`users`.`id_sdm`,
					`users`.`nuptk`,
					`users`.`nip`,
                    `users`.`nama_sdm`,					
					`users`.`department`,					
					`users`.`jenis_sdm`,	
					`users`.`sister`								
					FROM					
					`users`					
					WHERE
					`users`.`uid` = '$uid' AND `users`.`is_active` = 1 LIMIT 1";		
		$inpassing = "SELECT
					`data_inpassing`.`uid`,																						
					`data_inpassing`.`uid_sdm`,									
					`data_inpassing`.`id_sdm`,									
					`data_inpassing`.`id_dokumen`,									
					`data_inpassing`.`pangkat_golongan`,									
					`data_inpassing`.`sk`,									
					`data_inpassing`.`tanggal_sk`,									
					`data_inpassing`.`tanggal_mulai`
					FROM
					`data_inpassing`
					where
					`data_inpassing`.`uid_sdm` = '$uid'
					";
					
        $commonData = array(
                 'title' => 'Profil SDM Unitri',                
                'pegawai' => GenericModel::rawSelect($pegawai, false),
                'inpassing' => GenericModel::rawSelect($inpassing),
				'alamat_pegawai' => GenericModel::getAll('alamat_pegawai'),
				'department' => GenericModel::getAll('department'),
				'data_wilayah' => GenericModel::getAll('data_wilayah')				
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/inpassing', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/inpassing', $commonData);
		} 	
    }
	
	public function kepangkatan($uid) 
	{
    $role = Session::get('user_provider_type');
    if ($role === 'pegawai') {
        $uid = Session::get('uid');
    }
	
	if ($role === 'employee') {
        $uid = (int) $uid; // cast ke int untuk keamanan
    }
		
		$pegawai = "SELECT						
					`users`.`uid`,
					`users`.`nisn`,					                   
					`users`.`user_name`,					
					`users`.`id_sdm`,
					`users`.`nuptk`,
					`users`.`nip`,
                    `users`.`nama_sdm`,					
					`users`.`department`,					
					`users`.`jenis_sdm`,	
					`users`.`sister`								
					FROM					
					`users`					
					WHERE
					`users`.`uid` = '$uid' AND `users`.`is_active` = 1 LIMIT 1";		
		$kepangkatan = "SELECT
					`kepangkatan`.`uid`,																						
					`kepangkatan`.`uid_sdm`,									
					`kepangkatan`.`id_sdm`,									
					`kepangkatan`.`id_dokumen`,									
					`kepangkatan`.`pangkat_golongan`,									
					`kepangkatan`.`sk`,									
					`kepangkatan`.`tanggal_sk`,									
					`kepangkatan`.`tanggal_mulai`
					FROM
					`kepangkatan`
					where
					`kepangkatan`.`uid_sdm` = '$uid'
					";
					
        $commonData = array(
                 'title' => 'Profil Kepangkatan SDM Unitri',                
                'pegawai' => GenericModel::rawSelect($pegawai, false),
                'kepangkatan' => GenericModel::rawSelect($kepangkatan),
				'alamat_pegawai' => GenericModel::getAll('alamat_pegawai'),
				'department' => GenericModel::getAll('department')				
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/kepangkatan', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/kepangkatan', $commonData);
		} 	
    }
	
	public function penempatan($uid) 
	{
    $role = Session::get('user_provider_type');
    if ($role === 'pegawai') {
        $uid = Session::get('uid');
    }
	
	if ($role === 'employee') {
        $uid = (int) $uid; // cast ke int untuk keamanan
    }
		
		$pegawai = "SELECT						
					`users`.`uid`,
					`users`.`nisn`,					                   
					`users`.`user_name`,					
					`users`.`id_sdm`,
					`users`.`nuptk`,
					`users`.`nip`,
                    `users`.`nama_sdm`,					
					`users`.`department`,					
					`users`.`jenis_sdm`,	
					`users`.`sister`								
					FROM					
					`users`					
					WHERE
					`users`.`uid` = '$uid' AND `users`.`is_active` = 1 LIMIT 1";		
		$penempatan = "SELECT
					`penempatan`.`uid`,																						
					`penempatan`.`uid_sdm`,									
					`penempatan`.`id_sdm`,									
					`penempatan`.`id_dokumen`,									
					`penempatan`.`status_kepegawaian`,									
					`penempatan`.`ikatan_kerja`,									
					`penempatan`.`jenjang_pendidikan`,									
					`penempatan`.`unit_kerja`,									
					`penempatan`.`tanggal_mulai`,									
					`penempatan`.`tanggal_keluar`,
					`penempatan`.`perguruan_tinggi`,
					`penempatan`.`apakah_penugasan_homebase`
					FROM
					`penempatan`
					where
					`penempatan`.`uid_sdm` = '$uid'
					";
					
        $commonData = array(
                 'title' => 'Profil Penempatan SDM Unitri',                
                'pegawai' => GenericModel::rawSelect($pegawai, false),
                'penempatan' => GenericModel::rawSelect($penempatan),
				'alamat_pegawai' => GenericModel::getAll('alamat_pegawai'),
				'department' => GenericModel::getAll('department')				
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/penempatan', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/penempatan', $commonData);
		} 	
    }
	
	public static function inpassingSister()
	{
    require_once '../vendor/Sister/SisterClient.php';

    $id_sdm  = Request::post('id_sdm');
    $uid_sdm = Request::post('uid_sdm');

    $sister = new SisterClient(Config::get('baseUrl'), Config::get('username'), Config::get('password'), Config::get('idPengguna'));
    $data = $sister->getInpassing($id_sdm);

    // debug: lihat struktur data
    error_log('SISTER getInpassing: ' . print_r($data, true));

    if (!$data) {
        Session::add('feedback_negative', 'Tidak ada data dari SISTER.');
        Redirect::to('profil/inpassing/'.$uid_sdm);
        return false;
    }

    // normalisasi jadi array of rows
    $rows = (isset($data[0]) && is_array($data[0])) ? $data : [$data];

    $db = DatabaseFactory::getFactory()->getConnection();
    try {
        $db->beginTransaction();
		$checkStmt = $db->prepare("SELECT COUNT(*) FROM data_inpassing WHERE id_dokumen = :id_dokumen");        
        $insertSql = "INSERT INTO data_inpassing (uid_sdm, id_sdm, pangkat_golongan, id_dokumen, sk, tanggal_sk, tanggal_mulai)
                      VALUES (:uid_sdm, :id_sdm, :pangkat_golongan, :id_dokumen, :sk, :tanggal_sk, :tanggal_mulai)";
        $updateSql = "UPDATE data_inpassing SET
                        pangkat_golongan = :pangkat_golongan,
                        id_dokumen = :id_dokumen,
                        sk = :sk,
                        tanggal_sk = :tanggal_sk,
                        tanggal_mulai = :tanggal_mulai,
                        uid_sdm = :uid_sdm
                      WHERE id_dokumen = :id_dokumen";

        foreach ($rows as $row) {
            // mapping field dari API (sesuaikan jika key berbeda)
            $pangkat = $row['pangkat_golongan'] ?? null;
            $idDokumen = $row['id'] ?? null;
            $sk = $row['sk'] ?? null;

            // format tanggal ke YYYY-MM-DD atau null
            $tanggal_sk = !empty($row['tanggal_sk']) ? date('Y-m-d', strtotime($row['tanggal_sk'])) : null;
            $tanggal_mulai = !empty($row['tanggal_mulai']) ? date('Y-m-d', strtotime($row['tanggal_mulai'])) : null;

            // cek ada atau tidak
            $checkStmt->execute([':id_dokumen' => $idDokumen]);
            $exists = $checkStmt->fetchColumn() > 0;

            $stmt = $db->prepare($exists ? $updateSql : $insertSql);
            $ok = $stmt->execute([
                ':uid_sdm' => $uid_sdm,
                ':id_sdm' => $id_sdm,
                ':pangkat_golongan' => $pangkat,
                ':id_dokumen' => $idDokumen,
                ':sk' => $sk,
                ':tanggal_sk' => $tanggal_sk,
                ':tanggal_mulai' => $tanggal_mulai
            ]);

            if (!$ok) {
                $err = $stmt->errorInfo();
                throw new Exception('DB error: ' . implode(' | ', $err) . ' | row: ' . json_encode($row));
            }
        }

        $db->commit();
        Session::add('feedback_positive', 'Data Inpassing berhasil sinkron dan disimpan');
        Redirect::to('profil/inpassing/'.$uid_sdm);
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('inpassingSister error: ' . $e->getMessage());
        Session::add('feedback_negative', 'Gagal simpan Data: ' . $e->getMessage());
        Redirect::to('profil/inpassing/'.$uid_sdm);
        return false;
    }
	}
	
	public static function kepangkatanSister()
	{
    require_once '../vendor/Sister/SisterClient.php';

    $id_sdm  = Request::post('id_sdm');
    $uid_sdm = Request::post('uid_sdm');

    $sister = new SisterClient(Config::get('baseUrl'), Config::get('username'), Config::get('password'), Config::get('idPengguna'));
    $data = $sister->getKepangkatan($id_sdm);

    // debug: lihat struktur data
    error_log('SISTER getKepangkatan: ' . print_r($data, true));

    if (!$data) {
        Session::add('feedback_negative', 'Tidak ada data dari SISTER.');
        Redirect::to('profil/kepangkatan/'.$uid_sdm);
        return false;
    }

    // normalisasi jadi array of rows
    $rows = (isset($data[0]) && is_array($data[0])) ? $data : [$data];

    $db = DatabaseFactory::getFactory()->getConnection();
    try {
        $db->beginTransaction();
		$checkStmt = $db->prepare("SELECT COUNT(*) FROM kepangkatan WHERE id_dokumen = :id_dokumen");        
        $insertSql = "INSERT INTO kepangkatan (uid_sdm, id_sdm, pangkat_golongan, id_dokumen, sk, tanggal_mulai)
                      VALUES (:uid_sdm, :id_sdm, :pangkat_golongan, :id_dokumen, :sk, :tanggal_mulai)";
        $updateSql = "UPDATE kepangkatan SET
                        pangkat_golongan = :pangkat_golongan,
                        id_dokumen = :id_dokumen,
                        sk = :sk,                        
                        tanggal_mulai = :tanggal_mulai,
                        uid_sdm = :uid_sdm
                      WHERE id_dokumen = :id_dokumen";

        foreach ($rows as $row) {
            // mapping field dari API (sesuaikan jika key berbeda)
            $pangkat = $row['pangkat_golongan'] ?? null;
            $idDokumen = $row['id'] ?? null;
            $sk = $row['sk'] ?? null;

            // format tanggal ke YYYY-MM-DD atau null            
            $tanggal_mulai = !empty($row['tanggal_mulai']) ? date('Y-m-d', strtotime($row['tanggal_mulai'])) : null;

            // cek ada atau tidak
            $checkStmt->execute([':id_dokumen' => $idDokumen]);
            $exists = $checkStmt->fetchColumn() > 0;

            $stmt = $db->prepare($exists ? $updateSql : $insertSql);
            $ok = $stmt->execute([
                ':uid_sdm' => $uid_sdm,
                ':id_sdm' => $id_sdm,
                ':pangkat_golongan' => $pangkat,
                ':id_dokumen' => $idDokumen,
                ':sk' => $sk,               
                ':tanggal_mulai' => $tanggal_mulai
            ]);

            if (!$ok) {
                $err = $stmt->errorInfo();
                throw new Exception('DB error: ' . implode(' | ', $err) . ' | row: ' . json_encode($row));
            }
        }

        $db->commit();
        Session::add('feedback_positive', 'Data Inpassing berhasil sinkron dan disimpan');
        Redirect::to('profil/kepangkatan/'.$uid_sdm);
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('inpassingSister error: ' . $e->getMessage());
        Session::add('feedback_negative', 'Gagal simpan Data: ' . $e->getMessage());
        Redirect::to('profil/kepangkatan/'.$uid_sdm);
        return false;
    }
	}
	
	public static function penempatanSister()
	{
    require_once '../vendor/Sister/SisterClient.php';

    $id_sdm  = Request::post('id_sdm');
    $uid_sdm = Request::post('uid_sdm');

    $sister = new SisterClient(Config::get('baseUrl'), Config::get('username'), Config::get('password'), Config::get('idPengguna'));
    $data = $sister->getPenugasan($id_sdm);

    // debug: lihat struktur data
    error_log('SISTER getPenempatan: ' . print_r($data, true));

    if (!$data) {
        Session::add('feedback_negative', 'Tidak ada data dari SISTER.');
        Redirect::to('profil/penempatan/'.$uid_sdm);
        return false;
    }

    // normalisasi jadi array of rows
    $rows = (isset($data[0]) && is_array($data[0])) ? $data : [$data];

    $db = DatabaseFactory::getFactory()->getConnection();
    try {
        $db->beginTransaction();
		$checkStmt = $db->prepare("SELECT COUNT(*) FROM penempatan WHERE id_dokumen = :id_dokumen");        
        $insertSql = "INSERT INTO penempatan (uid_sdm, id_sdm, id_dokumen, status_kepegawaian, ikatan_kerja, jenjang_pendidikan, unit_kerja, tanggal_mulai, tanggal_keluar, perguruan_tinggi, apakah_penugasan_homebase)
                      VALUES (:uid_sdm, :id_sdm, :id_dokumen, :status_kepegawaian, :ikatan_kerja, :jenjang_pendidikan, :unit_kerja, :tanggal_mulai, :tanggal_keluar, :perguruan_tinggi, :apakah_penugasan_homebase)";
        $updateSql = "UPDATE penempatan SET                        
                        id_dokumen = :id_dokumen,
						status_kepegawaian = :status_kepegawaian,
                        ikatan_kerja = :ikatan_kerja,                        
                        jenjang_pendidikan = :jenjang_pendidikan,
                        unit_kerja = :unit_kerja,
                        tanggal_mulai = :tanggal_mulai,
                        tanggal_keluar = :tanggal_keluar,
                        perguruan_tinggi = :perguruan_tinggi,
						apakah_penugasan_homebase = :apakah_penugasan_homebase,
                        uid_sdm = :uid_sdm
                      WHERE id_dokumen = :id_dokumen";

        foreach ($rows as $row) {
            // mapping field dari API (sesuaikan jika key berbeda)
            $status_kepegawaian = $row['status_kepegawaian'] ?? null;
            $idDokumen = $row['id'] ?? null;
            $ikatan_kerja = $row['ikatan_kerja'] ?? null;
            $jenjang_pendidikan = $row['jenjang_pendidikan'] ?? null;
            $unit_kerja = $row['unit_kerja'] ?? null; 
            $perguruan_tinggi = $row['perguruan_tinggi'] ?? null;
			$apakah = $row['apakah_penugasan_homebase'] ?? null;
            
			// format tanggal ke YYYY-MM-DD atau null            
            $tanggal_mulai = !empty($row['tanggal_mulai']) ? date('Y-m-d', strtotime($row['tanggal_mulai'])) : null;
			$tanggal_keluar = !empty($row['tanggal_keluar']) ? date('Y-m-d', strtotime($row['tanggal_keluar'])) : null;
            // cek ada atau tidak
            $checkStmt->execute([':id_dokumen' => $idDokumen]);
            $exists = $checkStmt->fetchColumn() > 0;

            $stmt = $db->prepare($exists ? $updateSql : $insertSql);
            $ok = $stmt->execute([
                ':uid_sdm' => $uid_sdm,
                ':id_sdm' => $id_sdm,                
                ':id_dokumen' => $idDokumen,
                ':status_kepegawaian' => $status_kepegawaian,               
                ':ikatan_kerja' => $ikatan_kerja,               
                ':jenjang_pendidikan' => $jenjang_pendidikan,               
                ':unit_kerja' => $unit_kerja,               
                ':perguruan_tinggi' => $perguruan_tinggi,               
                ':tanggal_mulai' => $tanggal_mulai,
                ':tanggal_keluar' => $tanggal_keluar,
                ':apakah_penugasan_homebase' => $apakah
            ]);

            if (!$ok) {
                $err = $stmt->errorInfo();
                throw new Exception('DB error: ' . implode(' | ', $err) . ' | row: ' . json_encode($row));
            }
        }

        $db->commit();
        Session::add('feedback_positive', 'Data Inpassing berhasil sinkron dan disimpan');
        Redirect::to('profil/penempatan/'.$uid_sdm);
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('inpassingSister error: ' . $e->getMessage());
        Session::add('feedback_negative', 'Gagal simpan Data: ' . $e->getMessage());
        Redirect::to('profil/penempatan/'.$uid_sdm);
        return false;
    }
	}
	
	public function dataInpassing($id_dokumen) 
	{
    $role = Session::get('user_provider_type');
	
		$field = '`users`.`uid`,
					`users`.`nama_sdm`,					
					`users`.`department`';
        $table = '`users`';
        $where = "`users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0 ";
        $pegawai = GenericModel::getAll($table, $where, $field);
		$inpassing = "SELECT
					`data_inpassing`.`uid`,																						
					`data_inpassing`.`uid_sdm`,									
					`data_inpassing`.`id_sdm`,									
					`data_inpassing`.`id_dokumen`,									
					`data_inpassing`.`pangkat_golongan`,									
					`data_inpassing`.`sk`,									
					`data_inpassing`.`tanggal_sk`,									
					`data_inpassing`.`tanggal_mulai`,
					`data_inpassing`.`angka_kredit`,
					`data_inpassing`.`masa_kerja_tahun`,
					`data_inpassing`.`masa_kerja_bulan`,
					`data_inpassing`.`sister`
					FROM
					`data_inpassing`
					where
					`data_inpassing`.`id_dokumen` = '$id_dokumen' LIMIT 1
					";
		$field = '`dokumen_pegawai`.`uid`,
					`dokumen_pegawai`.`kategori`,
					`dokumen_pegawai`.`uid_sdm`,
					`dokumen_pegawai`.`id_dokumen`,
					`dokumen_pegawai`.`item_name`,
					`dokumen_pegawai`.`value`,
					`dokumen_pegawai`.`created_at`,
					`dokumen_pegawai`.`note`
					';
        $table = '`dokumen_pegawai`';
        $where = "`dokumen_pegawai`.`id_dokumen` = '$id_dokumen' AND `dokumen_pegawai`.`is_deleted` = 0";
        $dokumen = GenericModel::getAll($table, $where, $field);
					
        $commonData = array(
                 'title' => 'Detail Data Inpassing',				
                'inpassing' => GenericModel::rawSelect($inpassing, false),				
				'department' => GenericModel::getAll('department'),
				'kategori' => GenericModel::getAll('kategori_dokumen'),
				'link' => 'dataInpassing',
				'pegawai' => $pegawai,
				'dokumen' => $dokumen							
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/dataInpassing', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/dataInpassing', $commonData);
		} 	
    }
	
	public function dataKepangkatan($id_dokumen) 
	{
    $role = Session::get('user_provider_type');
	
		$field = '`users`.`uid`,
					`users`.`nama_sdm`,					
					`users`.`department`';
        $table = '`users`';
        $where = "`users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0 ";
        $pegawai = GenericModel::getAll($table, $where, $field);
		$kepangkatan = "SELECT
					`kepangkatan`.`uid`,																						
					`kepangkatan`.`uid_sdm`,									
					`kepangkatan`.`id_sdm`,									
					`kepangkatan`.`id_dokumen`,									
					`kepangkatan`.`pangkat_golongan`,									
					`kepangkatan`.`sk`,														
					`kepangkatan`.`tanggal_sk`,					
					`kepangkatan`.`tanggal_mulai`,					
					`kepangkatan`.`masa_kerja_tahun`,
					`kepangkatan`.`masa_kerja_bulan`,
					`kepangkatan`.`sister`
					FROM
					`kepangkatan`
					where
					`kepangkatan`.`id_dokumen` = '$id_dokumen' LIMIT 1
					";
		$field = '`dokumen_pegawai`.`uid`,
					`dokumen_pegawai`.`kategori`,
					`dokumen_pegawai`.`uid_sdm`,
					`dokumen_pegawai`.`id_dokumen`,
					`dokumen_pegawai`.`item_name`,
					`dokumen_pegawai`.`value`,
					`dokumen_pegawai`.`created_at`,
					`dokumen_pegawai`.`note`
					';
        $table = '`dokumen_pegawai`';
        $where = "`dokumen_pegawai`.`id_dokumen` = '$id_dokumen' AND `dokumen_pegawai`.`is_deleted` = 0";
        $dokumen = GenericModel::getAll($table, $where, $field);
					
        $commonData = array(
                 'title' => 'Detail Data Kepangkatan',				
                'kepangkatan' => GenericModel::rawSelect($kepangkatan, false),				
				'department' => GenericModel::getAll('department'),
				'kategori' => GenericModel::getAll('kategori_dokumen'),
				'link' => 'dataKepangkatan',
				'pegawai' => $pegawai,
				'dokumen' => $dokumen							
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/dataKepangkatan', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/dataKepangkatan', $commonData);
		} 	
    }
	
	public function dataPenempatan($id_dokumen) 
	{
    $role = Session::get('user_provider_type');
	
		$field = '`users`.`uid`,
					`users`.`nama_sdm`,					
					`users`.`department`';
        $table = '`users`';
        $where = "`users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0 ";
        $pegawai = GenericModel::getAll($table, $where, $field);
		$penempatan = "SELECT
					`penempatan`.`uid`,																						
					`penempatan`.`uid_sdm`,									
					`penempatan`.`id_sdm`,									
					`penempatan`.`id_dokumen`,									
					`penempatan`.`status_kepegawaian`,									
					`penempatan`.`ikatan_kerja`,									
					`penempatan`.`jenjang_pendidikan`,									
					`penempatan`.`unit_kerja`,									
					`penempatan`.`tanggal_mulai`,									
					`penempatan`.`tanggal_keluar`,
					`penempatan`.`perguruan_tinggi`,
					`penempatan`.`jenis_keluar`,
					`penempatan`.`surat_tugas`,
					`penempatan`.`tanggal_surat_tugas`,
					`penempatan`.`apakah_penugasan_homebase`,
					`penempatan`.`sister`
					FROM
					`penempatan`
					where
					`penempatan`.`id_dokumen` = '$id_dokumen' LIMIT 1
					";
		$field = '`dokumen_pegawai`.`uid`,
					`dokumen_pegawai`.`kategori`,
					`dokumen_pegawai`.`uid_sdm`,
					`dokumen_pegawai`.`id_dokumen`,
					`dokumen_pegawai`.`item_name`,
					`dokumen_pegawai`.`value`,
					`dokumen_pegawai`.`created_at`,
					`dokumen_pegawai`.`note`
					';
        $table = '`dokumen_pegawai`';
        $where = "`dokumen_pegawai`.`id_dokumen` = '$id_dokumen' AND `dokumen_pegawai`.`is_deleted` = 0";
        $dokumen = GenericModel::getAll($table, $where, $field);
					
        $commonData = array(
                 'title' => 'Detail Data Penempatan',				
                'penempatan' => GenericModel::rawSelect($penempatan, false),				
				'department' => GenericModel::getAll('department'),
				'kategori' => GenericModel::getAll('kategori_dokumen'),
				'link' => 'dataPenempatan',
				'pegawai' => $pegawai,
				'dokumen' => $dokumen							
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/dataPenempatan', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/dataPenempatan', $commonData);
		} 	
    }
	
	public static function inpassingSisterData()
{
    // muat class SisterClient (sesuaikan path jika perlu)
    require_once '../vendor/Sister/SisterClient.php';

    // ambil input dari request
    $id_sdm  = Request::post('id_sdm');
    $uid_sdm = Request::post('uid_sdm');
    $id_post = Request::post('id_dokumen'); // id yang dikirim dari UI (opsional)

    // ambil data dari SISTER
    $sister = new SisterClient(Config::get('baseUrl'), Config::get('username'), Config::get('password'), Config::get('idPengguna'));
    $data = $sister->getInpassingData($id_post);

    // debug struktur data (opsional, aktifkan saat troubleshooting)
    // error_log('SISTER getInpassingData: ' . print_r($data, true));

    // normalisasi: jika API mengembalikan array of rows, ambil baris pertama
    if (isset($data[0]) && is_array($data[0])) {
        $row = $data[0];
    } elseif (is_array($data)) {
        $row = $data;
    } else {
        Session::add('feedback_negative', 'Data SISTER tidak valid atau kosong.');
        Redirect::to('profil/dataInpassing/' . ($id_post ?? ''));
        return false;
    }

    // ambil id dokumen dari API jika ada, fallback ke POST id
    $idDokumen = $row['id'] ?? $id_post;
    if (empty($idDokumen)) {
        Session::add('feedback_negative', 'ID dokumen tidak tersedia dari SISTER atau form.');
        Redirect::to('profil/dataInpassing/' . ($id_post ?? ''));
        return false;
    }

    // mapping field dan format tanggal
    $pangkat = $row['pangkat_golongan'] ?? null;
    $sk = $row['sk'] ?? null;
    $angka_kredit = $row['angka_kredit'] ?? null;
    $masa_kerja_tahun = $row['masa_kerja_tahun'] ?? null;
    $masa_kerja_bulan = $row['masa_kerja_bulan'] ?? null;

    $tanggal_sk = !empty($row['tanggal_sk']) ? date('Y-m-d', strtotime($row['tanggal_sk'])) : null;
    $tanggal_mulai = !empty($row['tanggal_mulai']) ? date('Y-m-d', strtotime($row['tanggal_mulai'])) : null;

    $db = DatabaseFactory::getFactory()->getConnection();

    try {
        $db->beginTransaction();

        // cek apakah record sudah ada berdasarkan kombinasi id_sdm dan id_dokumen
        $checkSql = "SELECT COUNT(*) FROM data_inpassing WHERE id_sdm = :id_sdm AND id_dokumen = :id_dokumen";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([':id_sdm' => $id_sdm, ':id_dokumen' => $idDokumen]);
        $exists = $checkStmt->fetchColumn() > 0;

        if ($exists) {
            // update
            $sql = "UPDATE data_inpassing SET
                        uid_sdm = :uid_sdm,
                        pangkat_golongan = :pangkat_golongan,
                        sk = :sk,
                        tanggal_sk = :tanggal_sk,
                        tanggal_mulai = :tanggal_mulai,
                        angka_kredit = :angka_kredit,
                        masa_kerja_tahun = :masa_kerja_tahun,
                        masa_kerja_bulan = :masa_kerja_bulan,
                        updated_at = NOW()
                    WHERE id_sdm = :id_sdm AND id_dokumen = :id_dokumen";
        } else {
            // insert
            $sql = "INSERT INTO data_inpassing
                        (uid_sdm, id_sdm, pangkat_golongan, id_dokumen, sk, tanggal_sk, tanggal_mulai, angka_kredit, masa_kerja_tahun, masa_kerja_bulan, created_at, updated_at)
                    VALUES
                        (:uid_sdm, :id_sdm, :pangkat_golongan, :id_dokumen, :sk, :tanggal_sk, :tanggal_mulai, :angka_kredit, :masa_kerja_tahun, :masa_kerja_bulan, NOW(), NOW())";
        }

        $stmt = $db->prepare($sql);
        $ok = $stmt->execute([
            ':uid_sdm' => $uid_sdm,
            ':id_sdm' => $id_sdm,
            ':pangkat_golongan' => $pangkat,
            ':id_dokumen' => $idDokumen,
            ':sk' => $sk,
            ':tanggal_sk' => $tanggal_sk,
            ':tanggal_mulai' => $tanggal_mulai,
            ':angka_kredit' => $angka_kredit,
            ':masa_kerja_tahun' => $masa_kerja_tahun,
            ':masa_kerja_bulan' => $masa_kerja_bulan
        ]);

        if (!$ok) {
            $err = $stmt->errorInfo();
            throw new Exception('DB error: ' . implode(' | ', $err));
        }

        $db->commit();
        Session::add('feedback_positive', 'Data Inpassing berhasil sinkron dan disimpan');
        Redirect::to('profil/dataInpassing/' . $idDokumen);
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('inpassingSisterData error: ' . $e->getMessage());
        // log statement errorInfo jika tersedia
        if (isset($stmt) && is_object($stmt)) {
            error_log('Statement errorInfo: ' . print_r($stmt->errorInfo(), true));
        }
        Session::add('feedback_negative', 'Gagal simpan Data: ' . $e->getMessage());
        Redirect::to('profil/dataInpassing/' . ($id_post ?? ''));
        return false;
    }
}

	public static function kepangkatanSisterData()
{
    // muat class SisterClient (sesuaikan path jika perlu)
    require_once '../vendor/Sister/SisterClient.php';

    // ambil input dari request
    $id_sdm  = Request::post('id_sdm');
    $uid_sdm = Request::post('uid_sdm');
    $id_post = Request::post('id_dokumen'); // id yang dikirim dari UI (opsional)

    // ambil data dari SISTER
    $sister = new SisterClient(Config::get('baseUrl'), Config::get('username'), Config::get('password'), Config::get('idPengguna'));
    $data = $sister->getKepangkatanData($id_post);

    // debug struktur data (opsional, aktifkan saat troubleshooting)
    // error_log('SISTER getInpassingData: ' . print_r($data, true));

    // normalisasi: jika API mengembalikan array of rows, ambil baris pertama
    if (isset($data[0]) && is_array($data[0])) {
        $row = $data[0];
    } elseif (is_array($data)) {
        $row = $data;
    } else {
        Session::add('feedback_negative', 'Data SISTER tidak valid atau kosong.');
        Redirect::to('profil/dataKepangkatan/' . ($id_post ?? ''));
        return false;
    }

    // ambil id dokumen dari API jika ada, fallback ke POST id
    $idDokumen = $row['id'] ?? $id_post;
    if (empty($idDokumen)) {
        Session::add('feedback_negative', 'ID dokumen tidak tersedia dari SISTER atau form.');
        Redirect::to('profil/dataKepangkatan/' . ($id_post ?? ''));
        return false;
    }

    // mapping field dan format tanggal
    $pangkat_golongan = $row['pangkat_golongan'] ?? null;
    $sk = $row['sk'] ?? null;
    $pangkat = $row['pangkat'] ?? null;
    $golongan = $row['golongan'] ?? null;
    $id_pangkat = $row['id_pangkat_golongan'] ?? null;
    $masa_kerja_tahun = $row['masa_kerja_tahun'] ?? null;
    $masa_kerja_bulan = $row['masa_kerja_bulan'] ?? null;
    $tanggal_sk = !empty($row['tanggal_sk']) ? date('Y-m-d', strtotime($row['tanggal_sk'])) : null;
    $tanggal_mulai = !empty($row['tanggal_mulai']) ? date('Y-m-d', strtotime($row['tanggal_mulai'])) : null;

    $db = DatabaseFactory::getFactory()->getConnection();

    try {
        $db->beginTransaction();

        // cek apakah record sudah ada berdasarkan kombinasi id_sdm dan id_dokumen
        $checkSql = "SELECT COUNT(*) FROM kepangkatan WHERE id_sdm = :id_sdm AND id_dokumen = :id_dokumen";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([':id_sdm' => $id_sdm, ':id_dokumen' => $idDokumen]);
        $exists = $checkStmt->fetchColumn() > 0;

        if ($exists) {
            // update
            $sql = "UPDATE kepangkatan SET
                        uid_sdm = :uid_sdm,
                        pangkat_golongan = :pangkat_golongan,
                        sk = :sk,
                        tanggal_sk = :tanggal_sk,
                        tanggal_mulai = :tanggal_mulai,
                        pangkat = :pangkat,
                        golongan = :golongan,
						id_pangkat_golongan = :id_pangkat_golongan, 
                        masa_kerja_tahun = :masa_kerja_tahun,
                        masa_kerja_bulan = :masa_kerja_bulan,
                        updated_at = NOW()
                    WHERE id_sdm = :id_sdm AND id_dokumen = :id_dokumen";
        } else {
            // insert
            $sql = "INSERT INTO kepangkatan
                        (uid_sdm, id_sdm, pangkat_golongan, id_dokumen, sk, tanggal_sk, tanggal_mulai, pangkat, golongan, id_pangkat_golongan, masa_kerja_tahun, masa_kerja_bulan, created_at, updated_at)
                    VALUES
                        (:uid_sdm, :id_sdm, :pangkat_golongan, :id_dokumen, :sk, :tanggal_sk, :tanggal_mulai, :pangkat, :golongan, :id_pangkat_golongan, :masa_kerja_tahun, :masa_kerja_bulan, NOW(), NOW())";
        }

        $stmt = $db->prepare($sql);
        $ok = $stmt->execute([
            ':uid_sdm' => $uid_sdm,
            ':id_sdm' => $id_sdm,
            ':pangkat_golongan' => $pangkat_golongan,
            ':id_dokumen' => $idDokumen,
            ':sk' => $sk,
            ':tanggal_sk' => $tanggal_sk,
            ':tanggal_mulai' => $tanggal_mulai,
            ':pangkat' => $pangkat,
            ':golongan' => $golongan,
            ':id_pangkat_golongan' => $id_pangkat,
            ':masa_kerja_tahun' => $masa_kerja_tahun,
            ':masa_kerja_bulan' => $masa_kerja_bulan
        ]);

        if (!$ok) {
            $err = $stmt->errorInfo();
            throw new Exception('DB error: ' . implode(' | ', $err));
        }

        $db->commit();
        Session::add('feedback_positive', 'Data Inpassing berhasil sinkron dan disimpan');
        Redirect::to('profil/dataKepangkatan/' . $idDokumen);
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('inpassingSisterData error: ' . $e->getMessage());
        // log statement errorInfo jika tersedia
        if (isset($stmt) && is_object($stmt)) {
            error_log('Statement errorInfo: ' . print_r($stmt->errorInfo(), true));
        }
        Session::add('feedback_negative', 'Gagal simpan Data: ' . $e->getMessage());
        Redirect::to('profil/dataKepangkatan/' . ($id_post ?? ''));
        return false;
    }
}

public static function penempatanSisterData()
{
    // muat class SisterClient (sesuaikan path jika perlu)
    require_once '../vendor/Sister/SisterClient.php';

    // ambil input dari request
    $id_sdm  = Request::post('id_sdm');
    $uid_sdm = Request::post('uid_sdm');
    $id_post = Request::post('id_dokumen'); // id yang dikirim dari UI (opsional)

    // ambil data dari SISTER
    $sister = new SisterClient(Config::get('baseUrl'), Config::get('username'), Config::get('password'), Config::get('idPengguna'));
    $data = $sister->getPenugasanData($id_post);

    // debug struktur data (opsional, aktifkan saat troubleshooting)
    // error_log('SISTER getInpassingData: ' . print_r($data, true));

    // normalisasi: jika API mengembalikan array of rows, ambil baris pertama
    if (isset($data[0]) && is_array($data[0])) {
        $row = $data[0];
    } elseif (is_array($data)) {
        $row = $data;
    } else {
        Session::add('feedback_negative', 'Data SISTER tidak valid atau kosong.');
        Redirect::to('profil/dataPenempatan/' . ($id_post ?? ''));
        return false;
    }

    // ambil id dokumen dari API jika ada, fallback ke POST id
    $idDokumen = $row['id'] ?? $id_post;
    if (empty($idDokumen)) {
        Session::add('feedback_negative', 'ID dokumen tidak tersedia dari SISTER atau form.');
        Redirect::to('profil/dataPenempatan/' . ($id_post ?? ''));
        return false;
    }

    // mapping field dan format tanggal
    $status_kepegawaian = $row['status_kepegawaian'] ?? null;
    $idDokumen = $row['id'] ?? null;
    $ikatan_kerja = $row['ikatan_kerja'] ?? null;
    $jenjang_pendidikan = $row['jenjang_pendidikan'] ?? null;
    $unit_kerja = $row['unit_kerja'] ?? null; 
    $perguruan_tinggi = $row['perguruan_tinggi'] ?? null;
	$id_perguruan_tinggi = $row['id_perguruan_tinggi'] ?? null;
	$surat_tugas = $row['surat_tugas'] ?? null;
	$jenis_keluar = $row['jenis_keluar'] ?? null;
	            
	// format tanggal ke YYYY-MM-DD atau null            
    $tanggal_mulai = !empty($row['tanggal_mulai']) ? date('Y-m-d', strtotime($row['tanggal_mulai'])) : null;
	$tanggal_keluar = !empty($row['tanggal_keluar']) ? date('Y-m-d', strtotime($row['tanggal_keluar'])) : null;
	$tanggal_surat_tugas = !empty($row['tanggal_surat_tugas']) ? date('Y-m-d', strtotime($row['tanggal_surat_tugas'])) : null;

    $db = DatabaseFactory::getFactory()->getConnection();

    try {
    $db->beginTransaction();

    // cek apakah record sudah ada berdasarkan kombinasi id_sdm dan id_dokumen
    $checkSql = "SELECT COUNT(*) FROM penempatan 
                 WHERE id_sdm = :id_sdm AND id_dokumen = :id_dokumen";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([
        ':id_sdm'     => $id_sdm,
        ':id_dokumen' => $idDokumen
    ]);
    $exists = $checkStmt->fetchColumn() > 0;

    if ($exists) {
        // update
        $sql = "UPDATE penempatan SET
                    status_kepegawaian = :status_kepegawaian,
                    ikatan_kerja = :ikatan_kerja,
                    jenjang_pendidikan = :jenjang_pendidikan,
                    unit_kerja = :unit_kerja,
                    perguruan_tinggi = :perguruan_tinggi,
                    id_perguruan_tinggi = :id_perguruan_tinggi,
                    surat_tugas = :surat_tugas,
                    jenis_keluar = :jenis_keluar,
                    tanggal_mulai = :tanggal_mulai,
                    tanggal_keluar = :tanggal_keluar,
                    tanggal_surat_tugas = :tanggal_surat_tugas,
                    updated_at = NOW()
                WHERE id_sdm = :id_sdm AND id_dokumen = :id_dokumen";
    } else {
        // insert
        $sql = "INSERT INTO penempatan
                    (id_dokumen, id_sdm, status_kepegawaian, ikatan_kerja, jenjang_pendidikan, unit_kerja, perguruan_tinggi, id_perguruan_tinggi, surat_tugas, jenis_keluar, tanggal_mulai, tanggal_keluar, tanggal_surat_tugas, created_at, updated_at)
                VALUES
                    (:id_dokumen, :id_sdm, :status_kepegawaian, :ikatan_kerja, :jenjang_pendidikan, :unit_kerja, :perguruan_tinggi, :id_perguruan_tinggi, :surat_tugas, :jenis_keluar, :tanggal_mulai, :tanggal_keluar, :tanggal_surat_tugas, NOW(), NOW())";
    }

    $stmt = $db->prepare($sql);
    $ok = $stmt->execute([
        ':id_dokumen'          => $idDokumen,
        ':id_sdm'              => $id_sdm,
        ':status_kepegawaian'  => $status_kepegawaian,
        ':ikatan_kerja'        => $ikatan_kerja,
        ':jenjang_pendidikan'  => $jenjang_pendidikan,
        ':unit_kerja'          => $unit_kerja,
        ':perguruan_tinggi'    => $perguruan_tinggi,
        ':id_perguruan_tinggi'	=> $id_perguruan_tinggi,
        ':surat_tugas'         => $surat_tugas,
        ':jenis_keluar'        => $jenis_keluar,
        ':tanggal_mulai'       => $tanggal_mulai,
        ':tanggal_keluar'      => $tanggal_keluar,
        ':tanggal_surat_tugas' => $tanggal_surat_tugas
    ]);

    if (!$ok) {
        $err = $stmt->errorInfo();
        throw new Exception('DB error: ' . implode(' | ', $err));
    }

    $db->commit();
    Session::add('feedback_positive', 'Data Penempatan berhasil sinkron dan disimpan');
    Redirect::to('profil/dataPenempatan/' . $idDokumen);
    return true;

} catch (Exception $e) {
    $db->rollBack();
    error_log('penempatanSisterData error: ' . $e->getMessage());
    if (isset($stmt) && is_object($stmt)) {
        error_log('Statement errorInfo: ' . print_r($stmt->errorInfo(), true));
    }
    Session::add('feedback_negative', 'Gagal simpan Data: ' . $e->getMessage());
    Redirect::to('profil/dataPenempatan/' . ($id_post ?? ''));
    return false;
}
}
	
	public function TambahDataInpassing($uid) 
	{
    $role = Session::get('user_provider_type');
    if ($role === 'pegawai') {
        $uid = Session::get('uid');
    }
	
	if ($role === 'employee') {
        $uid = (int) $uid; // cast ke int untuk keamanan
    }
		
		$pegawai = "SELECT						
					`users`.`uid`,
					`users`.`nisn`,					                   
					`users`.`user_name`,					
					`users`.`id_sdm`,
					`users`.`nuptk`,
					`users`.`nip`,
                    `users`.`nama_sdm`,					
					`users`.`department`,					
					`users`.`jenis_sdm`,	
					`users`.`email`								
					FROM					
					`users`					
					WHERE
					`users`.`uid` = '$uid' AND `users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0 LIMIT 1";		
		$inpassing = "SELECT
					`data_inpassing`.`uid`,																						
					`data_inpassing`.`uid_sdm`,									
					`data_inpassing`.`id_sdm`,									
					`data_inpassing`.`id_dokumen`,									
					`data_inpassing`.`pangkat_golongan`,									
					`data_inpassing`.`sk`,									
					`data_inpassing`.`tanggal_sk`,									
					`data_inpassing`.`tanggal_mulai`
					FROM
					`data_inpassing`
					where
					`data_inpassing`.`uid_sdm` = '$uid'
					";
					
        $commonData = array(
                 'title' => 'Tambah Data Inpassing',                
                'pegawai' => GenericModel::rawSelect($pegawai, false),
                'inpassing' => GenericModel::rawSelect($inpassing),
				'alamat_pegawai' => GenericModel::getAll('alamat_pegawai'),
				'department' => GenericModel::getAll('department')								
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/TambahDataInpassing', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/TambahDataInpassing', $commonData);
		} 	
    }
	
	public function TambahDataPenempatan($uid) 
	{
    $role = Session::get('user_provider_type');
    if ($role === 'pegawai') {
        $uid = Session::get('uid');
    }
	
	if ($role === 'employee') {
        $uid = (int) $uid; // cast ke int untuk keamanan
    }
		
		$pegawai = "SELECT						
					`users`.`uid`,
					`users`.`nisn`,					                   
					`users`.`user_name`,					
					`users`.`id_sdm`,
					`users`.`nuptk`,
					`users`.`nip`,
                    `users`.`nama_sdm`,					
					`users`.`department`,					
					`users`.`jenis_sdm`,	
					`users`.`email`								
					FROM					
					`users`					
					WHERE
					`users`.`uid` = '$uid' AND `users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0 LIMIT 1";		
		$penempatan = "SELECT
					`penempatan`.`uid`,																						
					`penempatan`.`uid_sdm`,									
					`penempatan`.`id_sdm`,									
					`penempatan`.`id_dokumen`,									
					`penempatan`.`status_kepegawaian`,									
					`penempatan`.`ikatan_kerja`,									
					`penempatan`.`jenjang_pendidikan`,									
					`penempatan`.`unit_kerja`,									
					`penempatan`.`tanggal_mulai`,									
					`penempatan`.`tanggal_keluar`,
					`penempatan`.`perguruan_tinggi`,
					`penempatan`.`jenis_keluar`,
					`penempatan`.`surat_tugas`,
					`penempatan`.`tanggal_surat_tugas`,
					`penempatan`.`apakah_penugasan_homebase`,
					`penempatan`.`sister`
					FROM
					`penempatan`
					where
					`penempatan`.`uid_sdm` = '$uid'
					";
					
        $commonData = array(
                 'title' => 'Tambah Data Penempatan',                
                'pegawai' => GenericModel::rawSelect($pegawai, false),
                'penempatan' => GenericModel::rawSelect($penempatan),
				'status_kepegawaian' => GenericModel::getAll('status_kepegawaian'),
				'jenis_sdm' => GenericModel::getAll('jenis_sdm'),
				'department' => GenericModel::getAll('department')								
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/TambahDataPenempatan', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/TambahDataPenempatan', $commonData);
		} 	
    }
	
	public function editDataInpassing($id_dokumen) 
	{
    $role = Session::get('user_provider_type');
	    
		$inpassing = "SELECT
					`data_inpassing`.`uid`,																						
					`data_inpassing`.`uid_sdm`,									
					`data_inpassing`.`id_sdm`,									
					`data_inpassing`.`id_dokumen`,									
					`data_inpassing`.`pangkat_golongan`,									
					`data_inpassing`.`sk`,									
					`data_inpassing`.`tanggal_sk`,									
					`data_inpassing`.`tanggal_mulai`,
					`data_inpassing`.`angka_kredit`,
					`data_inpassing`.`masa_kerja_tahun`,
					`data_inpassing`.`masa_kerja_bulan`
					FROM
					`data_inpassing`
					where
					`data_inpassing`.`id_dokumen` = '$id_dokumen'
					";
		$field = '`users`.`uid`,
                    `users`.`phone`,                   
					`users`.`user_name`,
					`users`.`nuptk`,
					`users`.`nip`,
                    `users`.`id_sdm`,
                    `users`.`nama_sdm`,
					`users`.`fakultas`,
					`users`.`department`,
					`users`.`jenis_sdm`,	
					`users`.`email`,	
					`users`.`nama_status_aktif`,	
                    `users`.`nama_status_pegawai`';
        $table = '`users`';
        $where = "`users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0 AND `users`.`jenis_sdm` = 'Dosen'";
        $contact = GenericModel::getAll($table, $where, $field);
						
        $commonData = array(
                 'title' => 'Edit Data Inpassing',                
                'pegawai' => $contact,
                'inpassing' => GenericModel::rawSelect($inpassing, false),				
				'department' => GenericModel::getAll('department'),
				'data_wilayah' => GenericModel::getAll('data_wilayah')				
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('profil/editDataInpassing', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('profil/editDataInpassing', $commonData);
		} 	
    }
	
	public static function inputDataInpassing()
{
    // ambil input dari request
    $id_sdm            = Request::post('id_sdm');
    $uid_sdm           = Request::post('uid_sdm');
    $uid               = Request::post('uid'); // jika tidak dipakai, boleh diabaikan
    $id_dokumen        = Request::post('id_dokumen'); // optional, jika kosong -> buat baru

    // mapping field dan format tanggal
    $pangkat           = Request::post('pangkat_golongan');
    $sk                = Request::post('sk');
    $angka_kredit      = Request::post('angka_kredit');
    $masa_kerja_tahun  = Request::post('masa_kerja_tahun');
    $masa_kerja_bulan  = Request::post('masa_kerja_bulan');
    $tanggal_sk_raw    = Request::post('tanggal_sk');
    $tanggal_mulai_raw = Request::post('tanggal_mulai');

    // validasi / normalisasi tanggal ke format Y-m-d atau null
    $tanggal_sk = null;
    if (!empty($tanggal_sk_raw)) {
        $d = DateTime::createFromFormat('Y-m-d', $tanggal_sk_raw);
        if ($d && $d->format('Y-m-d') === $tanggal_sk_raw) {
            $tanggal_sk = $tanggal_sk_raw;
        } else {
            // coba format lain seperti d-m-Y
            $d2 = DateTime::createFromFormat('d-m-Y', $tanggal_sk_raw);
            if ($d2) $tanggal_sk = $d2->format('Y-m-d');
        }
    }
    $tanggal_mulai = null;
    if (!empty($tanggal_mulai_raw)) {
        $d = DateTime::createFromFormat('Y-m-d', $tanggal_mulai_raw);
        if ($d && $d->format('Y-m-d') === $tanggal_mulai_raw) {
            $tanggal_mulai = $tanggal_mulai_raw;
        } else {
            $d2 = DateTime::createFromFormat('d-m-Y', $tanggal_mulai_raw);
            if ($d2) $tanggal_mulai = $d2->format('Y-m-d');
        }
    }

    // jika id_dokumen kosong, buat baru untuk insert
    if (empty($id_dokumen)) {
        $id_dokumen = GenericModel::uuid_v4();
        $isInsert = true;
    } else {
        $isInsert = false;
    }

    $db = DatabaseFactory::getFactory()->getConnection();

    try {
        $db->beginTransaction();

        // cek apakah record sudah ada berdasarkan kombinasi uid_sdm dan id_dokumen
        $checkSql = "SELECT COUNT(*) FROM data_inpassing WHERE uid_sdm = :uid_sdm AND id_dokumen = :id_dokumen";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([
            ':uid_sdm'    => $uid_sdm,
            ':id_dokumen' => $id_dokumen
        ]);
        $exists = $checkStmt->fetchColumn() > 0;

        if ($exists) {
            // update
            $sql = "UPDATE data_inpassing SET
                        pangkat_golongan = :pangkat_golongan,
                        sk = :sk,
                        tanggal_sk = :tanggal_sk,
                        tanggal_mulai = :tanggal_mulai,
                        angka_kredit = :angka_kredit,
                        masa_kerja_tahun = :masa_kerja_tahun,
                        masa_kerja_bulan = :masa_kerja_bulan,
                        updated_at = NOW()
                    WHERE uid_sdm = :uid_sdm AND id_dokumen = :id_dokumen";
            $stmt = $db->prepare($sql);
            $ok = $stmt->execute([
                ':pangkat_golongan' => $pangkat,
                ':sk'               => $sk,
                ':tanggal_sk'       => $tanggal_sk,
                ':tanggal_mulai'    => $tanggal_mulai,
                ':angka_kredit'     => $angka_kredit,
                ':masa_kerja_tahun' => $masa_kerja_tahun,
                ':masa_kerja_bulan' => $masa_kerja_bulan,
                ':uid_sdm'          => $uid_sdm,
                ':id_dokumen'       => $id_dokumen
            ]);
        } else {
            // insert
            $sql = "INSERT INTO data_inpassing
                        (uid_sdm, id_sdm, pangkat_golongan, id_dokumen, sk, tanggal_sk, tanggal_mulai, angka_kredit, masa_kerja_tahun, masa_kerja_bulan, created_at, updated_at, sister)
                    VALUES
                        (:uid_sdm, :id_sdm, :pangkat_golongan, :id_dokumen, :sk, :tanggal_sk, :tanggal_mulai, :angka_kredit, :masa_kerja_tahun, :masa_kerja_bulan, NOW(), NOW(), :sister)";
            $stmt = $db->prepare($sql);
            $ok = $stmt->execute([
                ':uid_sdm'          => $uid_sdm,
                ':id_sdm'           => $id_sdm,
                ':pangkat_golongan' => $pangkat,
                ':id_dokumen'       => $id_dokumen,
                ':sk'               => $sk,
                ':tanggal_sk'       => $tanggal_sk,
                ':tanggal_mulai'    => $tanggal_mulai,
                ':angka_kredit'     => $angka_kredit,
                ':masa_kerja_tahun' => $masa_kerja_tahun,
                ':masa_kerja_bulan' => $masa_kerja_bulan,
                ':sister'           => 1
            ]);
        }

        if (!$ok) {
            $err = $stmt->errorInfo();
            throw new Exception('DB error: ' . implode(' | ', $err));
        }

        $db->commit();
        Session::add('feedback_positive', 'Data Inpassing berhasil sinkron dan disimpan');

        // redirect ke halaman yang relevan
        Redirect::to('profil/dataInpassing/' . $id_dokumen);
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('inputDataInpassing error: ' . $e->getMessage());
        if (isset($stmt) && is_object($stmt)) {
            error_log('Statement errorInfo: ' . print_r($stmt->errorInfo(), true));
        }
        Session::add('feedback_negative', 'Gagal simpan Data: ' . $e->getMessage());
        Redirect::to('profil/dataInpassing/' . ($id_dokumen ?? ''));
        return false;
    }
}
	
public static function inputDataPenempatan()
{
    // ambil input dari request
    $id_sdm            = Request::post('id_sdm');
    $uid_sdm           = Request::post('uid_sdm');
    $uid               = Request::post('uid'); // jika tidak dipakai, boleh diabaikan
    $id_dokumen        = Request::post('id_dokumen'); // optional, jika kosong -> buat baru

    // mapping field dan format tanggal
    $status_kepegawaian	= Request::post('status_kepegawaian');
    $ikatan_kerja       = Request::post('ikatan_kerja');
    $unit_kerja      	= Request::post('unit_kerja');
    $surat_tugas  		= Request::post('surat_tugas');
    $tanggal_sk_raw  	= Request::post('tanggal_surat_tugas');    
    $tanggal_mulai_raw	= Request::post('tanggal_mulai');
    $tanggal_keluar		= Request::post('tanggal_keluar');
    $alasan_keluar		= Request::post('alasan_keluar');

    // validasi / normalisasi tanggal ke format Y-m-d atau null
    $tanggal_sk = null;
    if (!empty($tanggal_sk_raw)) {
        $d = DateTime::createFromFormat('Y-m-d', $tanggal_sk_raw);
        if ($d && $d->format('Y-m-d') === $tanggal_sk_raw) {
            $tanggal_sk = $tanggal_sk_raw;
        } else {
            // coba format lain seperti d-m-Y
            $d2 = DateTime::createFromFormat('d-m-Y', $tanggal_sk_raw);
            if ($d2) $tanggal_sk = $d2->format('Y-m-d');
        }
    }
    $tanggal_mulai = null;
    if (!empty($tanggal_mulai_raw)) {
        $d = DateTime::createFromFormat('Y-m-d', $tanggal_mulai_raw);
        if ($d && $d->format('Y-m-d') === $tanggal_mulai_raw) {
            $tanggal_mulai = $tanggal_mulai_raw;
        } else {
            $d2 = DateTime::createFromFormat('d-m-Y', $tanggal_mulai_raw);
            if ($d2) $tanggal_mulai = $d2->format('Y-m-d');
        }
    }

    // jika id_dokumen kosong, buat baru untuk insert
    if (empty($id_dokumen)) {
        $id_dokumen = GenericModel::uuid_v4();
        $isInsert = true;
    } else {
        $isInsert = false;
    }

    $db = DatabaseFactory::getFactory()->getConnection();

    try {
        $db->beginTransaction();

        // cek apakah record sudah ada berdasarkan kombinasi uid_sdm dan id_dokumen
        $checkSql = "SELECT COUNT(*) FROM penempatan WHERE uid_sdm = :uid_sdm AND id_dokumen = :id_dokumen";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([
            ':uid_sdm'    => $uid_sdm,
            ':id_dokumen' => $id_dokumen
        ]);
        $exists = $checkStmt->fetchColumn() > 0;

        if ($exists) {
            // update
            $sql = "UPDATE penempatan SET
                        status_kepegawaian = :status_kepegawaian,
                        surat_tugas = :surat_tugas,
                        tanggal_surat_tugas = :tanggal_surat_tugas,
                        tanggal_mulai = :tanggal_mulai,
                        unit_kerja = :unit_kerja,
                        ikatan_kerja = :ikatan_kerja,
						tanggal_keluar = :tanggal_keluar,
                        alasan_keluar = :alasan_keluar,
                        updated_at = NOW()
                    WHERE uid_sdm = :uid_sdm AND id_dokumen = :id_dokumen";
            $stmt = $db->prepare($sql);
            $ok = $stmt->execute([
                ':status_kepegawaian' 	=> $status_kepegawaian,
                ':surat_tugas'          => $surat_tugas,
                ':tanggal_surat_tugas'  => $tanggal_sk,
                ':tanggal_mulai'    	=> $tanggal_mulai_raw,
                ':tanggal_keluar'     	=> $tanggal_keluar,
                ':ikatan_kerja' 		=> $ikatan_kerja,
                ':unit_kerja' 			=> $unit_kerja,
                ':uid_sdm'          	=> $uid_sdm,
                ':id_dokumen'       	=> $id_dokumen
            ]);
        } else {
            // insert
            $sql = "INSERT INTO penempatan
                        (uid_sdm, id_sdm, id_dokumen, status_kepegawaian, surat_tugas, tanggal_surat_tugas, tanggal_mulai, tanggal_keluar, ikatan_kerja, unit_kerja, perguruan_tinggi, created_at, updated_at, sister)
                    VALUES
                        (:uid_sdm, :id_sdm, :id_dokumen, :status_kepegawaian, :surat_tugas, :tanggal_surat_tugas, :tanggal_mulai, :tanggal_keluar, :ikatan_kerja, :unit_kerja, :perguruan_tinggi, NOW(), NOW(), :sister)";
            $stmt = $db->prepare($sql);
            $ok = $stmt->execute([
                ':uid_sdm'          	=> $uid_sdm,
                ':id_sdm'           	=> $id_sdm,               
                ':id_dokumen'       	=> $id_dokumen,
                ':status_kepegawaian'	=> $status_kepegawaian,
                ':surat_tugas'          => $surat_tugas,
                ':tanggal_surat_tugas'  => $tanggal_sk,
                ':tanggal_mulai'    	=> $tanggal_mulai,
                ':tanggal_keluar'     	=> $tanggal_keluar,
                ':ikatan_kerja' 		=> $ikatan_kerja,
                ':unit_kerja' 			=> $unit_kerja,
                ':perguruan_tinggi' 	=> 'Universitas Tribhuwana Tungga Dewi',
                ':sister'           	=> 1
            ]);
        }

        if (!$ok) {
            $err = $stmt->errorInfo();
            throw new Exception('DB error: ' . implode(' | ', $err));
        }

        $db->commit();
        Session::add('feedback_positive', 'Data Inpassing berhasil sinkron dan disimpan');

        // redirect ke halaman yang relevan
        Redirect::to('profil/dataPenempatan/' . $id_dokumen);
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('inputDataInpassing error: ' . $e->getMessage());
        if (isset($stmt) && is_object($stmt)) {
            error_log('Statement errorInfo: ' . print_r($stmt->errorInfo(), true));
        }
        Session::add('feedback_negative', 'Gagal simpan Data: ' . $e->getMessage());
        Redirect::to('profil/dataPenempatan/' . ($id_dokumen ?? ''));
        return false;
    }
}	

public static function JabfungSisterData()
{
    require_once '../vendor/Sister/SisterClient.php';

    $id_sdm  = Request::post('id_sdm');
    $uid_sdm = Request::post('uid_sdm');

    $sister = new SisterClient(Config::get('baseUrl'), Config::get('username'), Config::get('password'), Config::get('idPengguna'));
    $data = $sister->getJabfung($id_sdm);

    if (!$data) {
        Session::add('feedback_negative', 'Data SISTER tidak valid atau kosong.');
        Redirect::to('profil/jabatanFungsional/' . ($uid_sdm ?? ''));
        return false;
    }

    // normalisasi jadi array of rows
    $rows = [];
    if (isset($data[0]) && is_array($data[0])) {
        $rows = $data;
    } elseif (is_array($data)) {
        $rows = [$data];
    } else {
        Session::add('feedback_negative', 'Format data SISTER tidak dikenali.');
        Redirect::to('profil/jabatanFungsional/' . ($uid_sdm ?? ''));
        return false;
    }

    $db = DatabaseFactory::getFactory()->getConnection();

    try {
        $db->beginTransaction();

        // prepare statements: cek berdasarkan uid_sdm + id_dokumen
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM jabatan_fungsional WHERE uid_sdm = :uid_sdm AND id_dokumen = :id_dokumen");
        $insertSql = "INSERT INTO jabatan_fungsional (uid_sdm, id_sdm, jabatan_fungsional, id_dokumen, sk, tanggal_mulai, id_stat_pegawai, nm_stat_pegawai, created_at, updated_at)
                      VALUES (:uid_sdm, :id_sdm, :jabatan_fungsional, :id_dokumen, :sk, :tanggal_mulai, :id_stat_pegawai, :nm_stat_pegawai, NOW(), NOW())";
        $updateSql = "UPDATE jabatan_fungsional SET
                        jabatan_fungsional = :jabatan_fungsional,
                        sk = :sk,
                        tanggal_mulai = :tanggal_mulai,
                        id_stat_pegawai = :id_stat_pegawai,
                        nm_stat_pegawai = :nm_stat_pegawai,
                        updated_at = NOW()
                      WHERE uid_sdm = :uid_sdm AND id_dokumen = :id_dokumen";

        foreach ($rows as $row) {
            // ambil field dari API
            $jabatan_fungsional = $row['jabatan_fungsional'] ?? null;
            $idDokumen = $row['id'] ?? null;
            $sk = $row['sk'] ?? null;
            $id_stat_pegawai = isset($row['id_stat_pegawai']) && $row['id_stat_pegawai'] !== '' ? $row['id_stat_pegawai'] : null;
            $nm_stat_pegawai = isset($row['nm_stat_pegawai']) && $row['nm_stat_pegawai'] !== '' ? $row['nm_stat_pegawai'] : null;

            // jika idDokumen kosong, buat UUID baru (atau skip sesuai kebijakan)
            if (empty($idDokumen)) {
                $idDokumen = GenericModel::uuid_v4();
                error_log("JabfungSisterData: id dari SISTER kosong, generate id_dokumen={$idDokumen}");
            }

            // parse tanggal dengan aman
            $tanggal_mulai = null;
            if (!empty($row['tanggal_mulai'])) {
                $d = DateTime::createFromFormat('Y-m-d', $row['tanggal_mulai']);
                if ($d && $d->format('Y-m-d') === $row['tanggal_mulai']) {
                    $tanggal_mulai = $row['tanggal_mulai'];
                } else {
                    $ts = strtotime($row['tanggal_mulai']);
                    if ($ts !== false) $tanggal_mulai = date('Y-m-d', $ts);
                }
            }

            // cek eksistensi untuk pegawai ini
            $checkStmt->execute([':uid_sdm' => $uid_sdm, ':id_dokumen' => $idDokumen]);
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                $stmt = $db->prepare($updateSql);
                $ok = $stmt->execute([
                    ':jabatan_fungsional' => $jabatan_fungsional,
                    ':sk' => $sk,
                    ':tanggal_mulai' => $tanggal_mulai,
                    ':id_stat_pegawai' => $id_stat_pegawai,
                    ':nm_stat_pegawai' => $nm_stat_pegawai,
                    ':uid_sdm' => $uid_sdm,
                    ':id_dokumen' => $idDokumen
                ]);
            } else {
                $stmt = $db->prepare($insertSql);
                $ok = $stmt->execute([
                    ':uid_sdm' => $uid_sdm,
                    ':id_sdm' => $id_sdm,
                    ':jabatan_fungsional' => $jabatan_fungsional,
                    ':id_dokumen' => $idDokumen,
                    ':sk' => $sk,
                    ':tanggal_mulai' => $tanggal_mulai,
                    ':id_stat_pegawai' => $id_stat_pegawai,
                    ':nm_stat_pegawai' => $nm_stat_pegawai
                ]);
            }

            if (!$ok) {
                $err = $stmt->errorInfo();
                throw new Exception('DB error: ' . implode(' | ', $err) . ' | row: ' . json_encode($row));
            }
        }

        $db->commit();
        Session::add('feedback_positive', 'Data jabatan fungsional berhasil sinkron dan disimpan');
        Redirect::to('profil/jabatanFungsional/' . $uid_sdm);
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('JabfungSisterData error: ' . $e->getMessage());
        if (isset($stmt) && is_object($stmt)) {
            error_log('Statement errorInfo: ' . print_r($stmt->errorInfo(), true));
        }
        Session::add('feedback_negative', 'Gagal simpan Data: ' . $e->getMessage());
        Redirect::to('profil/jabatanFungsional/' . ($uid_sdm ?? ''));
        return false;
    }
}
	
	
	public function uploadDokumenpegawai($id_dokumen)
{
    if (empty($id_dokumen)) {
        Session::add('feedback_negative', 'UID tidak ditemukan.');
        Redirect::to('prodil/dataInpassing/'.$id_dokumen);
        return;
    }

    // ambil input
    $itemName = Request::post('item_name') ?? '';
    $kategori = Request::post('kategori') ?? '';
    $note     = Request::post('note') ?? '';
    $uid_sdm  = Request::post('uid_sdm') ?? '';
    $link  	= Request::post('link') ?? '';

    // panggil model: nama input file 'file'
    $ok = UploadModel::uploadDocumentPegawai('file', $id_dokumen, $kategori, $itemName, $uid_sdm, $note, $link);

    if ($ok) {
        Session::add('feedback_positive', 'Upload berhasil.');
    } else {
        Session::add('feedback_negative', 'Upload gagal. Periksa ukuran/tipe file atau permission folder.');
    }

    Redirect::to('profil/'.$link.'/' . $id_dokumen);
}
	
	public function editDokumenPegawai()
{
    $uid 		= Request::post('uid');
    $id_dokumen 		= Request::post('id_dokumen');
    $uid_sdm    = Request::post('uid_sdm');
    $item_name  = Request::post('item_name') ?? '';
    $kategori   = Request::post('kategori') ?? '';
    $note       = Request::post('note') ?? '';
	$link  		= Request::post('link') ?? '';

    if (empty($uid) || empty($uid_sdm)) {
        Session::add('feedback_negative', 'Parameter tidak lengkap.');
        Redirect::to('profil/'.$link.'/' . ($id_dokumen ?? ''));
        return;
    }

    $ok = UploadModel::replaceDokumenPegawai($uid, $uid_sdm, $_FILES['file'] ?? null, $kategori, $item_name, $note, $link);

    if ($ok) {
        Session::add('feedback_positive', 'Dokumen berhasil diperbarui.');
    } else {
        Session::add('feedback_negative', 'Gagal memperbarui dokumen. Periksa ukuran/tipe file dan permission folder.');
    }

    Redirect::to('profil/'.$link.'/' . $id_dokumen);
}

public function deleteDokumenPegawai()
{
    // cek login/permission sesuai kebutuhan
    if (!Session::userIsLoggedIn()) {
        Session::add('feedback_negative', 'Akses ditolak.');
        Redirect::to('login');
        return;
    }

    $id_dokumen = Request::post('id_dokumen') ?? '';
    $uid_sdm = Request::post('uid_sdm') ?? '';
    $uid = Request::post('uid') ?? '';
    $csrf = Request::post('csrf_token') ?? '';
	$link  = Request::post('link') ?? '';

    // sanitasi sederhana
    $id_dokumen = preg_replace('/[^\w\-]/', '', $id_dokumen);

    if (!Csrf::validateCsrfToken($csrf)) {
        Session::add('feedback_negative', 'Token CSRF tidak valid.');
        Redirect::to('profil/'.$link.'/' . $id_dokumen);
        return;
    }

    if (empty($uid) || empty($uid_sdm)) {
        Session::add('feedback_negative', 'Parameter tidak lengkap.');
        Redirect::to('profil/'.$link.'/' . $uid_sdm);
        return;
    }

    $ok = UploadModel::deleteDokumenPegawai($uid, $uid_sdm);

    if ($ok) {
        Session::add('feedback_positive', 'Dokumen berhasil dihapus (dipindah ke archive).');
    } else {
        Session::add('feedback_negative', 'Gagal menghapus dokumen. Periksa log server.');
    }

    Redirect::to('profil/'.$link.'/' . $id_dokumen);
}
	
}	