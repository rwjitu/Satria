<?php


class PegawaiController extends Controller
{

    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();

        // VERY IMPORTANT: All controllers/areas that should only be usable by logged-in users
        // need this line! Otherwise not-logged in users could do actions. If all of your pages should only
        // be usable by logged-in users: Put this line into libs/Controller->__construct
        Auth::checkAuthentication();
    }
	
	public function dataPegawai($page = 1, $limit = 10000)
    {
        Auth::isInSession('user_provider_type', 'employee');
		       
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
        $where = "`users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0 ORDER BY `users`.`nama_sdm`";
        $contact = GenericModel::getAll($table, $where, $field);
		$total_data_pegawai = "
            SELECT				
				COUNT(`uid`) as `total_user`,
				SUM(`is_active`) as `total_aktif`
            FROM 
                `users`			
            WHERE
                `users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0";	

        
        $this->View->render('pegawai/dataPegawai',
              array(               
                'title' => 'Data pegawai',
                'activelink1' => 'Pegawai',
                'activelink2' => 'Pegawai',
                'status_kepegawaian' => GenericModel::getAll('status_kepegawaian'),				
                'jenis_sdm' => GenericModel::getAll('jenis_sdm'),				
                'department' => GenericModel::getAll('department'),				
                'nama_status_aktif' => GenericModel::getAll('nama_status_aktif'),				
				'total_data_pegawai' => GenericModel::rawSelect($total_data_pegawai, false),
                'contact' => $contact
            )
        );
    }
	
	public function dataPribadi($page = 1, $limit = 10000)
    {
        Auth::isInSession('user_provider_type', 'employee');
		       
        $field = '`users`.`uid`,
                    `users`.`phone`,                   
					`users`.`user_name`,
					`users`.`id_sdm`,
					`users`.`nuptk`,
					`users`.`nip`,
                    `users`.`nama_sdm`,
					`users`.`fakultas`,
					`users`.`department`,
					`users`.`jenis_sdm`,	
					`users`.`email`,	
					`users`.`nama_status_aktif`,	
                    `users`.`nama_status_pegawai`,					
                    `users`.`nik`,					
                    `users`.`npwp`,					
                    `users`.`gender`,					
                    `users`.`place_of_birth`,					
                    `users`.`date_of_birth`					
					';
        $table = '`users`';
        $where = "`users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0 ORDER BY `users`.`nama_sdm`";
        $contact = GenericModel::getAll($table, $where, $field);
		$total_data_pegawai = "
            SELECT				
				COUNT(`uid`) as `total_user`,
				SUM(`is_active`) as `total_aktif`
            FROM 
                `users`			
            WHERE
                `users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0";	

        
        $this->View->render('pegawai/dataPribadi',
              array(               
                'title' => 'Data Pribadi',
                'activelink1' => 'Pegawai',
                'activelink2' => 'Pegawai',
                'status_kepegawaian' => GenericModel::getAll('status_kepegawaian'),				
                'jenis_sdm' => GenericModel::getAll('jenis_sdm'),				
                'department' => GenericModel::getAll('department'),				
                'nama_status_aktif' => GenericModel::getAll('nama_status_aktif'),				
				'total_data_pegawai' => GenericModel::rawSelect($total_data_pegawai, false),
                'contact' => $contact
            )
        );
    }
	
	public function inpassing()
    {
        Auth::isInSession('user_provider_type', 'employee');
		       
         $field = "
			`users`.`uid`,
			`users`.`user_name`,
			`users`.`id_sdm`,
			`users`.`nuptk`,
			`users`.`nip`,
			`users`.`nama_sdm`,
			`users`.`fakultas`,
			`users`.`department`,
			`users`.`jenis_sdm`,
			`users`.`email`,
			`users`.`nama_status_aktif`,
			`users`.`nama_status_pegawai`,
			COALESCE(di_latest.pangkat_golongan, '-') AS pangkat_golongan,
			di_latest.sk,
			di_latest.tanggal_sk,
			di_latest.tanggal_mulai
			";

			$table = "
			`users`
			LEFT JOIN (
			  SELECT t.uid_sdm, t.pangkat_golongan, t.sk, t.tanggal_sk, t.tanggal_mulai
			  FROM data_inpassing t
			  INNER JOIN (
				SELECT uid_sdm, MAX(tanggal_mulai) AS max_tgl
				FROM data_inpassing
				GROUP BY uid_sdm
			  ) m ON t.uid_sdm = m.uid_sdm AND t.tanggal_mulai = m.max_tgl
			) AS di_latest ON di_latest.uid_sdm = `users`.`uid`
			";

			$where = "`users`.`user_provider_type` = 'pegawai'
			  AND `users`.`jenis_sdm` = 'Dosen'
			  AND `users`.`is_deleted` = 0
			  ORDER BY `users`.`nama_sdm`";
        $contact = GenericModel::getAll($table, $where, $field);
        $this->View->render('pegawai/inpassing',
              array(               
                'title' => 'Data Inpassing',               
                'status_kepegawaian' => GenericModel::getAll('status_kepegawaian'),				
                'jenis_sdm' => GenericModel::getAll('jenis_sdm'),				
                'department' => GenericModel::getAll('department'),				
                'nama_status_aktif' => GenericModel::getAll('nama_status_aktif'),
                'pegawai' => $contact
            )
        );
    }
	
	public function jabatanFungsional()
    {
        Auth::isInSession('user_provider_type', 'employee');
		       
         $field = "
			`users`.`uid`,
			`users`.`user_name`,
			`users`.`id_sdm`,
			`users`.`nuptk`,
			`users`.`nip`,
			`users`.`nama_sdm`,
			`users`.`fakultas`,
			`users`.`department`,
			`users`.`jenis_sdm`,
			`users`.`email`,
			`users`.`nama_status_aktif`,
			`users`.`nama_status_pegawai`,
			COALESCE(di_latest.jabatan_fungsional, '-') AS jabatan_fungsional,
			di_latest.sk,			
			di_latest.tanggal_mulai
			";

		$table = "
			`users`
			LEFT JOIN (
			  SELECT t.uid_sdm, t.jabatan_fungsional, t.sk, t.tanggal_mulai
			  FROM jabatan_fungsional t
			  INNER JOIN (
				SELECT uid_sdm, MAX(tanggal_mulai) AS max_tgl
				FROM jabatan_fungsional
				GROUP BY uid_sdm
			  ) m ON t.uid_sdm = m.uid_sdm AND t.tanggal_mulai = m.max_tgl
			) AS di_latest ON di_latest.uid_sdm = `users`.`uid`
			";

			$where = "`users`.`user_provider_type` = 'pegawai'
			  AND `users`.`jenis_sdm` = 'Dosen'
			  AND `users`.`is_deleted` = 0
			  ORDER BY `users`.`nama_sdm`";
        $contact = GenericModel::getAll($table, $where, $field);
        $this->View->render('pegawai/jabatanFungsional',
              array(               
                'title' => 'Data Jabatan Fungsional',               
                'status_kepegawaian' => GenericModel::getAll('status_kepegawaian'),				
                'jenis_sdm' => GenericModel::getAll('jenis_sdm'),				
                'department' => GenericModel::getAll('department'),				
                'nama_status_aktif' => GenericModel::getAll('nama_status_aktif'),
                'pegawai' => $contact
            )
        );
    }
	
	public function kepangkatan()
    {
        Auth::isInSession('user_provider_type', 'employee');
		       
         $field = "
			`users`.`uid`,
			`users`.`user_name`,
			`users`.`id_sdm`,
			`users`.`nuptk`,
			`users`.`nip`,
			`users`.`nama_sdm`,
			`users`.`fakultas`,
			`users`.`department`,
			`users`.`jenis_sdm`,
			`users`.`email`,
			`users`.`nama_status_aktif`,
			`users`.`nama_status_pegawai`,
			COALESCE(di_latest.pangkat_golongan, '-') AS pangkat_golongan,
			di_latest.sk,			
			di_latest.tanggal_mulai
			";

		$table = "
			`users`
			LEFT JOIN (
			  SELECT t.uid_sdm, t.pangkat_golongan, t.sk, t.tanggal_mulai
			  FROM kepangkatan t
			  INNER JOIN (
				SELECT uid_sdm, MAX(tanggal_mulai) AS max_tgl
				FROM kepangkatan
				GROUP BY uid_sdm
			  ) m ON t.uid_sdm = m.uid_sdm AND t.tanggal_mulai = m.max_tgl
			) AS di_latest ON di_latest.uid_sdm = `users`.`uid`
			";

			$where = "`users`.`user_provider_type` = 'pegawai'
			  AND `users`.`jenis_sdm` = 'Dosen'
			  AND `users`.`is_deleted` = 0
			  ORDER BY `users`.`nama_sdm`";
        $contact = GenericModel::getAll($table, $where, $field);
        $this->View->render('pegawai/kepangkatan',
              array(               
                'title' => 'Data Kepangkatan',               
                'status_kepegawaian' => GenericModel::getAll('status_kepegawaian'),				
                'jenis_sdm' => GenericModel::getAll('jenis_sdm'),				
                'department' => GenericModel::getAll('department'),				
                'nama_status_aktif' => GenericModel::getAll('nama_status_aktif'),
                'pegawai' => $contact
            )
        );
    }
	
	public function penempatan()
    {
        Auth::isInSession('user_provider_type', 'employee');
		       
         $field = "
			`users`.`uid`,
			`users`.`user_name`,
			`users`.`id_sdm`,
			`users`.`nuptk`,
			`users`.`nip`,
			`users`.`nama_sdm`,
			`users`.`fakultas`,
			`users`.`department`,
			`users`.`jenis_sdm`,
			`users`.`email`,
			`users`.`nama_status_aktif`,
			`users`.`nama_status_pegawai`,
			COALESCE(di_latest.status_kepegawaian, '-') AS status_kepegawaian,			
			di_latest.apakah_penugasan_homebase,
			di_latest.unit_kerja,
			di_latest.tanggal_mulai
			";

		$table = "
			`users`
			LEFT JOIN (
			  SELECT t.uid_sdm, t.status_kepegawaian, t.apakah_penugasan_homebase, t.unit_kerja, t.tanggal_mulai
			  FROM penempatan t
			  INNER JOIN (
				SELECT uid_sdm, MAX(apakah_penugasan_homebase) AS max_homebase
				FROM penempatan
				GROUP BY uid_sdm
			  ) m ON t.uid_sdm = m.uid_sdm AND t.apakah_penugasan_homebase = m.max_homebase
			) AS di_latest ON di_latest.uid_sdm = `users`.`uid`
			";

			$where = "`users`.`user_provider_type` = 'pegawai'			  
			  AND `users`.`is_deleted` = 0
			  ORDER BY `users`.`nama_sdm`";
        $contact = GenericModel::getAll($table, $where, $field);
        $this->View->render('pegawai/penempatan',
              array(               
                'title' => 'Data Penempatan SDM',               
                'status_kepegawaian' => GenericModel::getAll('status_kepegawaian'),				
                'jenis_sdm' => GenericModel::getAll('jenis_sdm'),				
                'department' => GenericModel::getAll('department'),				
                'nama_status_aktif' => GenericModel::getAll('nama_status_aktif'),
                'pegawai' => $contact
            )
        );
    }
	
	public function profil($uid) 
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
					`users`.`phone`,                   
					`users`.`user_name`,
					`users`.`id_sdm`,
					`users`.`nuptk`,
					`users`.`nip`,
                    `users`.`nama_sdm`,
					`users`.`fakultas`,
					`users`.`department`,
					`users`.`program_studi`,
					`users`.`jenis_sdm`,	
					`users`.`email`,	
					`users`.`nik`,	
					`users`.`npwp`,	
					`users`.`nama_status_aktif`,	
                    `users`.`nama_status_pegawai`,					
                    `users`.`nik`,					
                    `users`.`npwp`,					
                    `users`.`bpjs_kesehatan`,					
                    `users`.`bpjs_kerja`,					
                    `users`.`id_sinta`,					
                    `users`.`gender`,					
                    `users`.`agama`,					
                    `users`.`sk_tmmd`,					
                    `users`.`tgl_tmmd`,					
                    `users`.`kewarganegaraan`,					
                    `users`.`place_of_birth`,					
                    `users`.`date_of_birth`,					
                    `users`.`alamat`,					
                    `users`.`rt`,					
                    `users`.`rw`,					
                    `users`.`kelurahan`,					
                    `users`.`kecamatan`,					
                    `users`.`kota_kabupaten`,					
                    `users`.`id_kota_kabupaten`,					
                    `users`.`propinsi`,					
                    `users`.`telepon_rumah`,					
                    `users`.`kode_pos`,					
                    `users`.`sister`					
					FROM
					`users`						
					WHERE
					`users`.`uid` = '$uid' AND `users`.`is_deleted` = 0 
					ORDER BY
					 `users`.`nama_sdm`
					LIMIT 1
					";		
		$field = '`dokumen_pokok`.`uid`,
					`dokumen_pokok`.`kategori`,
					`dokumen_pokok`.`uid_sdm`,
					`dokumen_pokok`.`id_dokumen`,
					`dokumen_pokok`.`item_name`,
					`dokumen_pokok`.`value`,
					`dokumen_pokok`.`created_at`,
					`dokumen_pokok`.`note`
					';
        $table = '`dokumen_pokok`';
        $where = "`dokumen_pokok`.`uid_sdm` = '$uid' AND `dokumen_pokok`.`is_deleted` = 0";
        $dokumen = GenericModel::getAll($table, $where, $field);
		
		$commonData = array(
                 'title' => 'Profil SDM Unitri',                
                'pegawai' => GenericModel::rawSelect($pegawai, false),
				'alamat_pegawai' => GenericModel::getAll('alamat_pegawai'),
				'department' => GenericModel::getAll('department'),
				'kategori' => GenericModel::getAll('kategori_dokumen'),
				'data_wilayah' => GenericModel::getAll('data_wilayah'),				
				'dokumen' => $dokumen				
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('pegawai/profil', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('pegawai/profil', $commonData);
		} 	
        
    }
	
	public function add_ajax_kab($id_prov){
		    
			$kabupaten = "SELECT `data_wilayah`.*  FROM `data_wilayah` WHERE `data_wilayah`.`id_induk_wilayah` = '$id_prov'";
			 
			$this->View->renderFileOnly('pegawai/kabupaten', array(
                'kabupaten' => GenericModel::rawSelect($kabupaten)
        ));
        
	}
	
	public function add_ajax_kec($id_kab){
		    
			$kecamatan = "SELECT `data_wilayah`.*  FROM `data_wilayah` WHERE `data_wilayah`.`id_induk_wilayah` = '$id_kab'";
			 
			$this->View->renderFileOnly('pegawai/kecamatan', array(
                'kecamatan' => GenericModel::rawSelect($kecamatan)
        ));
        
	}
	
	public function editDataPegawai($uid) 
	{
    
	$role = Session::get('user_provider_type');
    if ($role === 'pegawai') {
        $uid = Session::get('uid');
    }
	
	if ($role === 'employee') {
        $uid = (int)$uid; // cast ke int untuk keamanan
    }
	
	$pegawai = "SELECT						
					`users`.`uid`,
					`users`.`nisn`,
					`users`.`phone`,                   
					`users`.`user_name`,
					`users`.`id_sdm`,
					`users`.`nuptk`,
					`users`.`nip`,
                    `users`.`nama_sdm`,
					`users`.`fakultas`,
					`users`.`department`,
					`users`.`program_studi`,
					`users`.`jenis_sdm`,	
					`users`.`email`,	
					`users`.`nik`,	
					`users`.`npwp`,	
					`users`.`nama_status_aktif`,	
                    `users`.`nama_status_pegawai`,					
                    `users`.`nik`,					
                    `users`.`npwp`,
					`users`.`bpjs_kesehatan`,					
                    `users`.`bpjs_kerja`,					
                    `users`.`id_sinta`,		
                    `users`.`gender`,					
                    `users`.`agama`,					
                    `users`.`sk_tmmd`,					
                    `users`.`tgl_tmmd`,					
                    `users`.`kewarganegaraan`,					
                    `users`.`place_of_birth`,					
                    `users`.`date_of_birth`,					
                    `users`.`alamat`,					
                    `users`.`rt`,					
                    `users`.`rw`,					
                    `users`.`kelurahan`,					
                    `users`.`kecamatan`,					
                    `users`.`kota_kabupaten`,					
                    `users`.`id_kota_kabupaten`,					
                    `users`.`propinsi`,					
                    `users`.`telepon_rumah`,					
                    `users`.`kode_pos`,					
                    `users`.`sister`					
					FROM
					`users`						
					WHERE
					`users`.`uid` = '$uid' AND `users`.`is_deleted` = 0 LIMIT 1
					";		
		$field = '`dokumen_pokok`.`uid`,
					`dokumen_pokok`.`kategori`,
					`dokumen_pokok`.`uid_sdm`,
					`dokumen_pokok`.`id_dokumen`,
					`dokumen_pokok`.`item_name`,
					`dokumen_pokok`.`value`,
					`dokumen_pokok`.`created_at`,
					`dokumen_pokok`.`note`
					';
        $table = '`dokumen_pokok`';
        $where = "`dokumen_pokok`.`uid_sdm` = '$uid' AND `dokumen_pokok`.`is_deleted` = 0";
        $dokumen = GenericModel::getAll($table, $where, $field);
		
		$commonData = array(
                 'title' => 'Edit Profil SDM Unitri',                
                'pegawai' => GenericModel::rawSelect($pegawai, false),
				 'status_kepegawaian' => GenericModel::getAll('status_kepegawaian'),				
                'jenis_sdm' => GenericModel::getAll('jenis_sdm'),			
				'alamat_pegawai' => GenericModel::getAll('alamat_pegawai'),
				'department' => GenericModel::getAll('department'),
				'kategori' => GenericModel::getAll('kategori_dokumen'),
				'nama_status_aktif' => GenericModel::getAll('nama_status_aktif'),
				'data_wilayah' => GenericModel::getAll('data_wilayah'),				
				'dokumen' => $dokumen				
        );
		
		if ($role === 'pegawai') {
        $this->View->renderMhs('pegawai/editDataPegawai', $commonData);
		} 
		if ($role === 'employee') {
        $this->View->render('pegawai/editDataPegawai', $commonData);
		} 	
        
    }
	
	public static function saveAlamatPegawai()
	{
    require_once '../vendor/Sister/SisterClient.php';

    $id_sdm   = Request::post('id_sdm');
    $uid_sdm  = Request::post('uid_sdm');

    $sister = new SisterClient(Config::get('baseUrl'), Config::get('username'), Config::get('password'), Config::get('idPengguna'));
    $data = $sister->getdataAlamat($id_sdm);

    // normalisasi jika API mengembalikan array of rows
    if (isset($data[0]) && is_array($data[0])) {
        $row = $data[0];
    } elseif (is_array($data)) {
        $row = $data;
    } else {
        Session::add('feedback_negative', 'Data alamat dari SISTER tidak valid.');
        Redirect::to('pegawai/profil/'.$uid_sdm);
        return false;
    }

    // ambil nilai dengan fallback ke string kosong
    $email = $row['email'] ?? '';
    $alamat = $row['alamat'] ?? '';
    $rt = $row['rt'] ?? '';
    $rw = $row['rw'] ?? '';
    $dusun = $row['dusun'] ?? '';
    $kelurahan = $row['kelurahan'] ?? '';
    $kota_kabupaten = $row['kota_kabupaten'] ?? '';
    $id_kota_kabupaten = $row['id_kota_kabupaten'] ?? null;
    $kode_pos = $row['kode_pos'] ?? '';
    $telepon_rumah = $row['telepon_rumah'] ?? '';
    $telepon_hp = $row['telepon_hp'] ?? '';

    $db = DatabaseFactory::getFactory()->getConnection();

    try {
        $db->beginTransaction();

        // cek apakah id_sdm sudah ada
        $check = $db->prepare("SELECT COUNT(*) FROM alamat_pegawai WHERE id_sdm = :id_sdm");
        $check->execute([':id_sdm' => $id_sdm]);
        $exists = $check->fetchColumn() > 0;

        if ($exists) {
            // UPDATE alamat_pegawai
            $sql = "UPDATE alamat_pegawai SET
                        email = :email,
                        alamat = :alamat,
                        rt = :rt,
                        rw = :rw,
                        dusun = :dusun,
                        kelurahan = :kelurahan,
                        kota_kabupaten = :kota_kabupaten,
                        id_kota_kabupaten = :id_kota_kabupaten,
                        kode_pos = :kode_pos,
                        telepon_rumah = :telepon_rumah,
                        telepon_hp = :telepon_hp,
                        uid_sdm = :uid_sdm                        
                    WHERE id_sdm = :id_sdm";
        } else {
            // INSERT alamat_pegawai
            $sql = "INSERT INTO alamat_pegawai (
                        uid_sdm, id_sdm, email, alamat, rt, rw, dusun, kelurahan,
                        kota_kabupaten, id_kota_kabupaten, kode_pos, telepon_rumah, telepon_hp
                    ) VALUES (
                        :uid_sdm, :id_sdm, :email, :alamat, :rt, :rw, :dusun, :kelurahan,
                        :kota_kabupaten, :id_kota_kabupaten, :kode_pos, :telepon_rumah, :telepon_hp
                    )";
        }

        $stmt = $db->prepare($sql);
        $ok = $stmt->execute([
            ':uid_sdm' => $uid_sdm,
            ':id_sdm' => $id_sdm,
            ':email' => $email,
            ':alamat' => $alamat,
            ':rt' => $rt,
            ':rw' => $rw,
            ':dusun' => $dusun,
            ':kelurahan' => $kelurahan,
            ':kota_kabupaten' => $kota_kabupaten,
            ':id_kota_kabupaten' => $id_kota_kabupaten,
            ':kode_pos' => $kode_pos,
            ':telepon_rumah' => $telepon_rumah,
            ':telepon_hp' => $telepon_hp
        ]);

        if (!$ok) {
            $err = $stmt->errorInfo();
            throw new Exception('Gagal simpan alamat_pegawai: ' . implode(' | ', $err));
        }

        // Update tabel users email dan phone jika ada record dengan id_sdm yang sama
        // Pastikan kolom di tabel users bernama id_sdm, email, phone (sesuaikan jika berbeda)
        $updateUserSql = "UPDATE users SET 
						email = :email,
						alamat = :alamat,
						rt = :rt,
                        rw = :rw,
                        dusun = :dusun,
                        kelurahan = :kelurahan,
                        kota_kabupaten = :kota_kabupaten,
                        id_kota_kabupaten = :id_kota_kabupaten,
                        kode_pos = :kode_pos,                        							
                        telepon_rumah = :telepon_rumah,                        							
						phone = :phone 
						WHERE id_sdm = :id_sdm";
        $updateUserStmt = $db->prepare($updateUserSql);
        $ok2 = $updateUserStmt->execute([
            ':email' => $email,
            ':phone' => $telepon_hp,
            ':alamat' => $alamat,
			':rt' => $rt,
            ':rw' => $rw,
            ':dusun' => $dusun,
            ':kelurahan' => $kelurahan,
            ':kota_kabupaten' => $kota_kabupaten,
            ':id_kota_kabupaten' => $id_kota_kabupaten,
            ':kode_pos' => $kode_pos,
			':telepon_rumah' => $telepon_rumah,
            ':id_sdm' => $id_sdm
        ]);

        if ($updateUserStmt->rowCount() === 0) {
            // tidak wajib dianggap error, hanya log; jika ingin wajib, uncomment exception
            // throw new Exception('Tidak ada record users yang diupdate untuk id_sdm: ' . $id_sdm);
            error_log('saveAlamatPegawai: tidak ada users yang diupdate untuk id_sdm ' . $id_sdm);
        }

        if (!$ok2) {
            $err2 = $updateUserStmt->errorInfo();
            throw new Exception('Gagal update users: ' . implode(' | ', $err2));
        }

        $db->commit();
        Session::add('feedback_positive', 'Data alamat pegawai berhasil sinkron dan disimpan');
        Redirect::to('pegawai/profil/'.$uid_sdm);
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('saveAlamatPegawai error: ' . $e->getMessage());
        if (isset($stmt) && is_object($stmt)) {
            error_log('alamat_pegawai stmt errorInfo: ' . print_r($stmt->errorInfo(), true));
        }
        if (isset($updateUserStmt) && is_object($updateUserStmt)) {
            error_log('users stmt errorInfo: ' . print_r($updateUserStmt->errorInfo(), true));
        }
        Session::add('feedback_negative', 'Gagal simpan alamat pegawai: ' . $e->getMessage());
        Redirect::to('pegawai/profil/'.$uid_sdm);
        return false;
    }
	}

	public static function dataPribadiGender()
	{
    require_once '../vendor/Sister/SisterClient.php';

	// Gunakan sandbox dulu
	$id_sdm = Request::post('id_sdm');
	$uid_sdm = Request::post('uid_sdm');
	$baseUrl    = Config::get('baseUrl');
	$username   = Config::get('username');
	$password   = Config::get('password');
	$idPengguna = Config::get('idPengguna');
	
	$sister = new SisterClient($baseUrl, $username, $password, $idPengguna);
	
	$data = $sister->dataPribadi($id_sdm); 		
	
	$database = DatabaseFactory::getFactory()->getConnection();

    // cek apakah id_sdm sudah ada
    $check = $database->prepare("SELECT COUNT(*) FROM users WHERE id_sdm = :id_sdm");
    $check->execute([':id_sdm' => $id_sdm]);

    if ($check->fetchColumn() > 0) {
        // UPDATE
        $sql = "UPDATE users SET
                    gender = :gender,
                    place_of_birth = :place_of_birth,
                    date_of_birth = :date_of_birth                    
                WHERE id_sdm = :id_sdm";
    } else {
        // INSERT
        $sql = "INSERT INTO users (
                    id_sdm, gender, place_of_birth, date_of_birth
                ) VALUES (
                    :id_sdm, :gender, :place_of_birth, :date_of_birth
                )";
    }

    $query = $database->prepare($sql);
    $success = $query->execute([
		':id_sdm' => $id_sdm,
        ':gender' => $data['jenis_kelamin'],
        ':place_of_birth' => $data['tempat_lahir'],
        ':date_of_birth' => $data['tanggal_lahir']        
    ]);

    if ($success) {
        Redirect::to('pegawai/profil/'.$uid_sdm);
		Session::add('feedback_positive', 'Data alamat pegawai berhasil sinkron dan disimpan');
        return true;		
    } else {
        Redirect::to('pegawai/profil/'.$uid_sdm);
		$error = $query->errorInfo();
        Session::add('feedback_negative', 'Gagal simpan alamat pegawai: ' . implode(' | ', $error));
        return false;		
		}
	}
	
	public static function kependudukan()
	{
    require_once '../vendor/Sister/SisterClient.php';

	// Gunakan sandbox dulu
	$id_sdm = Request::post('id_sdm');
	$uid_sdm = Request::post('uid_sdm');
	$baseUrl    = Config::get('baseUrl');
	$username   = Config::get('username');
	$password   = Config::get('password');
	$idPengguna = Config::get('idPengguna');
	
	$sister = new SisterClient($baseUrl, $username, $password, $idPengguna);
	
	$data = $sister->kependudukan($id_sdm); 		
	
	$database = DatabaseFactory::getFactory()->getConnection();

    // cek apakah id_sdm sudah ada
    $check = $database->prepare("SELECT COUNT(*) FROM users WHERE id_sdm = :id_sdm");
    $check->execute([':id_sdm' => $id_sdm]);

    if ($check->fetchColumn() > 0) {
        // UPDATE
        $sql = "UPDATE users SET
                    nik = :nik,
                    agama = :agama,
                    kewarganegaraan = :kewarganegaraan                    
                WHERE id_sdm = :id_sdm";
    } else {
        // INSERT
        $sql = "INSERT INTO users (
                    id_sdm, nik, agama, kewarganegaraan
                ) VALUES (
                    :id_sdm, :nik, :agama, :kewarganegaraan
                )";
    }

    $query = $database->prepare($sql);
    $success = $query->execute([
		':id_sdm' => $id_sdm,
        ':nik' => $data['nik'],
        ':agama' => $data['agama'],
        ':kewarganegaraan' => $data['kewarganegaraan']        
    ]);

    if ($success) {
        Redirect::to('pegawai/profil/'.$uid_sdm);
		Session::add('feedback_positive', 'Data kependudukan pegawai berhasil sinkron dan disimpan');
        return true;		
    } else {
        Redirect::to('pegawai/profil/'.$uid_sdm);
		$error = $query->errorInfo();
        Session::add('feedback_negative', 'Gagal simpan alamat pegawai: ' . implode(' | ', $error));
        return false;		
		}
	}
	
	public static function kepegawaian()
	{
    require_once '../vendor/Sister/SisterClient.php';

	// Gunakan sandbox dulu
	$id_sdm = Request::post('id_sdm');
	$uid_sdm = Request::post('uid_sdm');
	$baseUrl    = Config::get('baseUrl');
	$username   = Config::get('username');
	$password   = Config::get('password');
	$idPengguna = Config::get('idPengguna');
	
	$sister = new SisterClient($baseUrl, $username, $password, $idPengguna);
	
	$data = $sister->kepegawaian($id_sdm); 		
	
	$database = DatabaseFactory::getFactory()->getConnection();

    // cek apakah id_sdm sudah ada
    $check = $database->prepare("SELECT COUNT(*) FROM users WHERE id_sdm = :id_sdm");
    $check->execute([':id_sdm' => $id_sdm]);

    if ($check->fetchColumn() > 0) {
        // UPDATE
        $sql = "UPDATE users SET
                    nip = :nip,
                    nuptk = :nuptk,
                    sk_tmmd = :sk_tmmd,                    
                    tgl_tmmd = :tgl_tmmd                    
                WHERE id_sdm = :id_sdm";
    } else {
        // INSERT
        $sql = "INSERT INTO users (
                    id_sdm, nip, nuptk, sk_tmmd, tgl_tmmd
                ) VALUES (
                    :id_sdm, :nip, :nuptk, :sk_tmmd, :tlg_tmmd
                )";
    }

    $query = $database->prepare($sql);
    $success = $query->execute([
		':id_sdm' => $id_sdm,
        ':nip' => $data['nip'],
        ':nuptk' => $data['nuptk'],
        ':sk_tmmd' => $data['sk_tmmd'],
        ':tgl_tmmd' => $data['tmmd']        
    ]);

    if ($success) {
        Redirect::to('pegawai/profil/'.$uid_sdm);
		Session::add('feedback_positive', 'Data kepegawaian berhasil sinkron dan disimpan');
        return true;		
    } else {
        Redirect::to('pegawai/profil/'.$uid_sdm);
		$error = $query->errorInfo();
        Session::add('feedback_negative', 'Gagal simpan alamat pegawai: ' . implode(' | ', $error));
        return false;		
		}
	}
	
		
	public function uploadDokumenPokok($uid)
{
    if (empty($uid)) {
        Session::add('feedback_negative', 'UID tidak ditemukan.');
        Redirect::to('pegawai/profil');
        return;
    }

    // ambil input
    $itemName = Request::post('item_name') ?? '';
    $kategori = Request::post('kategori') ?? '';
    $note     = Request::post('note') ?? '';

    // panggil model: nama input file 'file'
    $ok = UploadModel::uploadDocument('file', $uid, $kategori, $itemName, $note);

    if ($ok) {
        Session::add('feedback_positive', 'Upload berhasil.');
    } else {
        Session::add('feedback_negative', 'Upload gagal. Periksa ukuran/tipe file atau permission folder.');
    }

    Redirect::to('pegawai/profil/' . $uid);
}
	
	public function editDokumenPokok()
{
    $id_dokumen = Request::post('id_dokumen');
    $uid_sdm    = Request::post('uid_sdm');
    $item_name  = Request::post('item_name') ?? '';
    $kategori   = Request::post('kategori') ?? '';
    $note       = Request::post('note') ?? '';
	

    if (empty($id_dokumen) || empty($uid_sdm)) {
        Session::add('feedback_negative', 'Parameter tidak lengkap.');
        Redirect::to('pegawai/profil/' . ($uid_sdm ?? ''));
        return;
    }

    $ok = UploadModel::replaceDokumenPokok($id_dokumen, $uid_sdm, $_FILES['file'] ?? null, $kategori, $item_name, $note);

    if ($ok) {
        Session::add('feedback_positive', 'Dokumen berhasil diperbarui.');
    } else {
        Session::add('feedback_negative', 'Gagal memperbarui dokumen. Periksa ukuran/tipe file dan permission folder.');
    }

    Redirect::to('pegawai/profil/' . $uid_sdm);
}

	public function deleteDokumenPokok()
{
    // cek login/permission sesuai kebutuhan
    if (!Session::userIsLoggedIn()) {
        Session::add('feedback_negative', 'Akses ditolak.');
        Redirect::to('login');
        return;
    }

    $id_dokumen = Request::post('id_dokumen') ?? '';
    $uid_sdm = Request::post('uid_sdm') ?? '';
    $csrf = Request::post('csrf_token') ?? '';

    // sanitasi sederhana
    $id_dokumen = preg_replace('/[^\w\-]/', '', $id_dokumen);

    if (!Csrf::validateCsrfToken($csrf)) {
        Session::add('feedback_negative', 'Token CSRF tidak valid.');
        Redirect::to('pegawai/profil/' . $uid_sdm);
        return;
    }

    if (empty($id_dokumen) || empty($uid_sdm)) {
        Session::add('feedback_negative', 'Parameter tidak lengkap.');
        Redirect::to('pegawai/profil/' . $uid_sdm);
        return;
    }

    $ok = UploadModel::deleteDokumenPokok($id_dokumen, $uid_sdm);

    if ($ok) {
        Session::add('feedback_positive', 'Dokumen berhasil dihapus (dipindah ke archive).');
    } else {
        Session::add('feedback_negative', 'Gagal menghapus dokumen. Periksa log server.');
    }

    Redirect::to('pegawai/profil/' . $uid_sdm);
}	
	

	public function updatePegawai($uid)
{
    $database = DatabaseFactory::getFactory()->getConnection();

    // Ambil data lama untuk log
    $oldData = GenericModel::getOne('users', "`uid` = '{$uid}'", 'log');
    $post_array = $_POST;
    $post_array = array_filter($post_array);

    // Buat log edit
    $log = json_encode($_POST);
    $log = str_replace('","', '<br />', $log);
    $log = str_replace('":"', ' : ', $log);
    $log = str_replace('_', ' ', $log);
    $log = str_replace('{"', '', $log);
    $log = str_replace('"}', '', $log);
    $log = '<li><span class="badge badge-grey">' . SESSION::get('user_name') .
           '</span> edit employee:<br />' . $log . '<br>(' . date("Y-m-d") . ')</li>' . $oldData->log;

    $custom_array = array(
        'log' => $log,
        'modifier_id' => SESSION::get('uid'),
        'modified_timestamp' => date("Y-m-d H:i:s")
    );

    $update = array_merge($post_array, $custom_array);

    // Validasi duplikat user_name
    if (!empty($update['user_name'])) {
		$update['nisn'] = $update['user_name'];
        $check = $database->prepare("SELECT COUNT(*) FROM users WHERE user_name = :user_name AND uid != :uid");
        $check->execute([
            ':user_name' => $update['user_name'],            
            ':uid' => $uid
        ]);
        if ($check->fetchColumn() > 0) {
            Session::add('feedback_negative', 'Gagal, user_name sudah digunakan oleh pegawai lain');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit; // hentikan eksekusi agar tidak lanjut update
        }
    }
	
	// Validasi duplikat email
    if (!empty($update['email'])) {
		$check = $database->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND uid != :uid");
        $check->execute([
            ':email' => $update['email'],            
            ':uid' => $uid
        ]);
        if ($check->fetchColumn() > 0) {
            Session::add('feedback_negative', 'Gagal, email sudah digunakan oleh pegawai lain');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit; // hentikan eksekusi agar tidak lanjut update
        }
    }

    // Lanjut update kalau lolos validasi
    try {
        GenericModel::update('users', $update, "`uid` = '$uid'");
        Session::add('feedback_positive', 'Data berhasil diupdate');
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // duplicate entry
            Session::add('feedback_negative', 'Gagal, user_name sudah ada (duplicate entry)');
        } else {
            Session::add('feedback_negative', 'Error: ' . $e->getMessage());
        }
    }

     Redirect::to('pegawai/profil/' . $uid);
}
	
	public function updatePembayaran($uid)
    {
			
		$full_name 		= Request::post('full_name');
		$bayar = Request::post('jumlah');
		$jumlah = str_replace('.', '', $bayar);
		$update			= array(
						'nominal' => Request::post('usnominal'),
						'jumlah' => $jumlah,
						'metode' => Request::post('metode'),
						'petugas'    => SESSION::get('uid'),
						'tanggal_bayar'    => date("Y-m-d"),
						'time_detail_payment'    => date("Y-m-d H:i:s"),						
						'status' => 0	
                         );
					 
        GenericModel::update('tagihan', $update, "`uid` = '{$uid}'");
		$feedback_positive = Session::get('feedback_positive');
        $feedback_negative = Session::get('feedback_negative');
        // echo out positive messages
        if ($feedback_positive) {  
           Redirect::to('pelanggan/cetakNota/'.$uid);
		   Session::add('feedback_positive', 'Data Pembayaran <strong>'.$full_name.' </strong> Berhasil dimasukkan');			
        }
        // echo out negative messages
        if ($feedback_negative) {
             Redirect::to('pelanggan/dataTagihan', Session::add('feedback_negative', 'Nominal Pembayaran <strong>'.$full_name.' </strong> Gagal dimasukkan'));
        }
        // RESET counter feedback to unconfuse user     
		
    }
	
	public function cetakNota($uid) {
        $pelanggan = "SELECT	
					`tagihan`.`uid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,	
					`tagihan`.`petugas`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`full_name`,
					`users`.`nisn`,
					`users`.`alamat`
					FROM
					`tagihan`
					JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
					WHERE
					`tagihan`.`uid` = '$uid' AND `tagihan`.`status` = 0 LIMIT 1
					";				
		$petugas = "SELECT `uid`, `full_name` FROM `users` WHERE user_provider_type = 'employee' ORDER BY `uid`";			
		
        $this->View->render('pelanggan/cetak_nota', array(
				'title' => 'Cetak Nota',
			   'company' => GenericModel::getAll('`system_preference`', "`category` = 'company_identification' ORDER BY `item_name` ASC", '`value`, `item_name`'),
                'pelanggan' => GenericModel::rawSelect($pelanggan, false),
				'petugas' => GenericModel::rawSelect($petugas)
        ));
        
    }
	
	public function pdfnota($uid) {
    $pelanggan = "SELECT	
					`tagihan`.`uid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`tagihan`.`petugas`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`full_name`,
					`users`.`nisn`,
					`users`.`alamat`
					FROM
					`tagihan`
					JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
					WHERE
					`tagihan`.`uid` = '$uid' AND `tagihan`.`status` = 0 LIMIT 1
					";
	$petugas = "SELECT `uid`, `full_name` FROM `users` WHERE user_provider_type = 'employee' ORDER BY `uid`";				
	$this->View->renderFileOnly('pelanggan/pdf_nota_pembayaran', array(
               'company' => GenericModel::getAll('`system_preference`', "`category` = 'company_identification' ORDER BY `item_name` ASC", '`value`, `item_name`'),
                'pelanggan' => GenericModel::rawSelect($pelanggan, false),
				'petugas' => GenericModel::rawSelect($petugas)
        ));
        
    }
	
	public function updateTagihan($uid)
    {
    $bulan = Request::post('bulan');
	$tahun = Request::post('tahun');
	   
	   switch ($bulan) {
		case 'Januari':
			$angka_bulan = 1;
			break;
		case 'Februari':
			$angka_bulan = 2;
			break;
		case 'Maret':
			$angka_bulan = 3;
			break;
		case 'April':
			$angka_bulan = 4;
			break;
		case 'Mei':
			$angka_bulan = 5;
			break;
		case 'Juni':
			$angka_bulan = 6;
			break;
		case 'Juli':
			$angka_bulan = 7;
			break;
		case 'Agustus':
			$angka_bulan = 8;
			break;
		case 'September':
			$angka_bulan = 9;
		case 'Oktober':
			$angka_bulan = 10;
			break;
		case 'November':
			$angka_bulan = 11;
			break;
		case 'Desember':
			$angka_bulan = 12;
			break;
	  }	
	   
	   $update = array(
                        'bulan' => Request::post('bulan'),
						'angka_bulan' => $angka_bulan,
						'uid' => Request::post('uid'),
						'tahun' => Request::post('tahun')						
                        );
		
		self::updateDataTagihan($uid, $update);
		$return = true;

		if (!self::tagihanSudahAda($bulan, $tahun, $uid)) {
            Session::add('feedback_negative', 'Gagal, data sudah ada');
            $return = false;
        }
		return true;
		
	header('Location: ' . $_SERVER['HTTP_REFERER']);	
    }
		
	
	public static function jabfung()
	{
    require_once '../vendor/Sister/SisterClient.php';

	// Gunakan sandbox dulu
	$id_sdm = Request::post('id_sdm');
	$uid_sdm = Request::post('uid_sdm');
	$id_smt = 'eeaac974-f51c-4d7e-985c-d4a90562e37f';
	$baseUrl    = Config::get('baseUrl');
	$username   = Config::get('username');
	$password   = Config::get('password');
	$idPengguna = Config::get('idPengguna');
	
	$sister = new SisterClient($baseUrl, $username, $password, $idPengguna);
	
	$data = $sister->getPenugasanData($id_smt); 		
	print_r ($data);
	
	}
	
	
}
?>