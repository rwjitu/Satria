<?php


class RincianController extends Controller
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
        
    }
	
	public function rincian($nisn) {
        $pelanggan = "SELECT						
					`users`.`uid`,
					`users`.`full_name`,
					`users`.`nisn`,
					`users`.`alamat`,
					`users`.`nominal_bulan`					
					FROM
					`users`						
					WHERE
					`users`.`nisn` = '$nisn' AND `users`.`is_active` = 1 LIMIT 1
					";		

        $this->View->renderFileOnly('pelanggan/rincian', array(
                'company' => GenericModel::getAll('`system_preference`', "`category` = 'company_identification' ORDER BY `item_name` ASC", '`value`, `item_name`'),
                'pelanggan' => GenericModel::rawSelect($pelanggan, false),
				'tagihan' => GenericModel::getAll('tagihan')	
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
	$this->View->renderFileOnly('pelanggan/pdf_nota_pembayaran', array(
               'company' => GenericModel::getAll('`system_preference`', "`category` = 'company_identification' ORDER BY `item_name` ASC", '`value`, `item_name`'),
                'pelanggan' => GenericModel::rawSelect($pelanggan, false)
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
	
	
	public function updateDataTagihan($uid, $update)
    {
       
		GenericModel::update('tagihan', $update, "`uid` = '$uid'");      
      
		
        //Redirect::to('mahasiswa/detailMhs/' . $uid);
		header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
	
	public function TambahTagihanBaru()
    {
      $tagihan_successful = DaftarModel::TambahTagihanBaru();

        if ($tagihan_successful) {
            Redirect::to('pelanggan/dataTagihan');
        } else {
            Redirect::to('pelanggan/dataTagihan');
        }		
    }
	
	public function inputTagihanBaru()
    {
      //$uid 			= $_POST['uid'];
      $bulan 		= Request::post('bulan');        
      $tahun 	   	= Request::post('tahun');
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
	  //$jumlah = count($uid);	
	  $data      = $_POST;
	  //$post_array = array_filter($post_array);
	  
		//$total		= count($uid);
		$database = DatabaseFactory::getFactory()->getConnection();
		//$stmt = $pdo->prepare("INSERT INTO data_bulan (bulan, tahun, uid) VALUES (:bulan, :tahun, :uid)");
		
		$stmt = $database->prepare("INSERT INTO tagihan (bulan, angka_bulan, tahun, cid, status) VALUES (:bulan, :angka_bulan, :tahun, :cid, :status)");

		foreach ($data['cid'] as $cid) {
		$stmt->execute([
        ':bulan' => $data['bulan'],
		':angka_bulan' => $angka_bulan,
        ':tahun' => $data['tahun'],
        ':cid' => $cid,
		':status' => 1
		]);		
		}
		$count =  $stmt->rowCount();
        if ($count == 1) {
			header('Location: ' . $_SERVER['HTTP_REFERER']);
            Session::add('feedback_positive', 'Hore, data berhasil disimpan');
			
        }
		
		
		
    }
	
	public function tagihanSudahAda($bulan, $tahun, $uid)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT uid FROM tagihan WHERE `uid` = :uid AND `bulan` = :bulan AND `tahun` = :tahun LIMIT 1");
        $query->execute(array(':uid' => $uid, ':bulan' => $bulan, ':tahun' => $tahun));
        if ($query->rowCount() == 1) {
            return true;
        }
        return false;		
    }

	public function add_ajax_pembayaran($uid){
		    
			$pilih_tagihan = "SELECT `tagihan`.*  FROM `tagihan` WHERE `tagihan`.`uid` = '$uid'";
			 
			$this->View->renderFileOnly('pelanggan/ajax_data_tagihan', array(
                'pilih_tagihan' => GenericModel::rawSelect($pilih_tagihan)
        ));
        
	}
	
	
}
?>