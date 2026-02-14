<?php


class KeuanganController extends Controller
{

    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();
        // special authentication check for the entire controller: Note the check-ADMIN-authentication!
        // All methods inside this controller are only accessible for admins (= users that have role type 7)
        Auth::checkAuthentication();		
    }

	public static function exportDataPegawai()
    {
        $user = "
            SELECT
                `users`.`uid`,
                `users`.`id_sdm`,
                `users`.`phone`,                   
				`users`.`user_name`,
				`users`.`nuptk`,
				`users`.`nip`,
                `users`.`nama_sdm`,
				`users`.`fakultas`,
				`users`.`department`,
				`users`.`jenis_sdm`,	
				`users`.`email`,	
				`users`.`nama_status_aktif`,	
                `users`.`nama_status_pegawai`			
            FROM 
                `users`
            WHERE
                `users`.`user_provider_type` = 'pegawai' AND `users`.`is_deleted` = 0";
        $user = GenericModel::rawSelect($user);       
		$department = GenericModel::getAll('department');	
        /** Create a new Spreadsheet Object **/
		$styleArray = [
        'font' => [
            'bold'  =>  true,
            'size'  =>  12,
            'name'  =>  'Arial'
        ]
		];
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
		$sheet->getStyle('A1')->applyFromArray($styleArray);
		//$spreadsheet->getActiveSheet()->mergeCells('A1:C3', Worksheet::MERGE_CELL_CONTENT_MERGE);
		//$sheet->mergeCells('A1:F1', Worksheet::MERGE_CELL_CONTENT_MERGE);
        $sheet->setCellValue('A1', 'DATA PEGAWAI UNIVERSITAS TRIBHUWANA TUNGGADEWI');
        $sheet->setCellValue('A2', 'NO');
        $sheet->setCellValue('B2', 'NAMA');
		$sheet->setCellValue('C2', 'NIDN');
		$sheet->setCellValue('D2', 'NUPTK');
		$sheet->setCellValue('E2', 'NIP');		
		$sheet->setCellValue('F2', 'STATUS');
		$sheet->setCellValue('G2', 'PENUGASAN');
		$sheet->setCellValue('H2', 'PENEMPATAN');
		$sheet->setCellValue('I2', 'KEAKTIFAN');
		$sheet->setCellValue('J2', 'TELEPON');
		$sheet->setCellValue('K2', 'EMAIL');
		$sheet->setCellValue('L2', 'ID-SDM');
		        

        $no = 3;
		$nomor = 1;
        foreach ($user as $key => $value) {
            $sheet->setCellValue('A' . $no, $nomor);
            $sheet->setCellValue('B' . $no, $value->nama_sdm);
            $sheet->setCellValue('C' . $no, $value->user_name);
            $sheet->setCellValue('D' . $no, $value->nuptk);
            $sheet->setCellValue('E' . $no, $value->nip);
            $sheet->setCellValue('F' . $no, $value->nama_status_pegawai);
            $sheet->setCellValue('G' . $no, $value->jenis_sdm); 
			foreach($department as $key => $value_d) {
			if($value->department == $value_d->id){
            $sheet->setCellValue('H' . $no, $value_d->nama);}}
			$sheet->setCellValue('I' . $no, $value->nama_status_aktif);	
			$sheet->setCellValue('J' . $no, $value->phone);	
			$sheet->setCellValue('K' . $no, $value->email);	
			$sheet->setCellValue('L' . $no, $value->id_sdm);	
            $no++;
			$nomor++;
        }

		ob_end_clean();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="data-pegawai-unitri.xlsx"');
        $writer->save("php://output"); 
    }
	
	public static function exportDataTagihan()
    {
    
	$bulan = Request::post('bulan');
    $tahun = Request::post('tahun');
	
	if ($bulan == null AND $tahun == null) {		
		$tagihanQuery = "
            SELECT
                `tagihan`.`cid`,
                `tagihan`.`bulan`,
				`tagihan`.`tahun`,
				`tagihan`.`nominal` as usnominal,
				`tagihan`.`jumlah`,
				`tagihan`.`metode`,
				`tagihan`.`tanggal_bayar`,
				`users`.`full_name`,
				`users`.`armada`,
				`users`.`alamat`,
				`users`.`nominal_bulan`
            FROM 
                `tagihan`
			JOIN
                `users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE
                `tagihan`.`status` = 1
			ORDER BY
				`tagihan`.`tahun` ASC, `tagihan`.`angka_bulan` ASC ";
		$tagihan = GenericModel::rawSelect($tagihanQuery);
		$nama = "semua";	
	} 
	if ($bulan == "0" AND $tahun == "0") {
		$tagihanQuery = "
            SELECT
                `tagihan`.`cid`,
                `tagihan`.`bulan`,
				`tagihan`.`tahun`,
				`tagihan`.`nominal` as usnominal,
				`tagihan`.`jumlah`,
				`tagihan`.`metode`,
				`tagihan`.`tanggal_bayar`,
				`users`.`full_name`,
				`users`.`armada`,
				`users`.`alamat`,
				`users`.`nominal_bulan`
            FROM 
                `tagihan`
			JOIN
                `users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE
                `tagihan`.`status` = 1
			ORDER BY
			`tagihan`.`tahun` ASC, `tagihan`.`angka_bulan` ASC";
		$tagihan = GenericModel::rawSelect($tagihanQuery);	
		$nama = "Semua";	
	}
	if ($bulan != "0" AND $tahun == "0") {
		$tagihanQuery = "
            SELECT
                `tagihan`.`cid`,
                `tagihan`.`bulan`,
				`tagihan`.`tahun`,
				`tagihan`.`nominal` as usnominal,
				`tagihan`.`jumlah`,
				`tagihan`.`metode`,
				`tagihan`.`tanggal_bayar`,
				`users`.`full_name`,
				`users`.`armada`,
				`users`.`alamat`,
				`users`.`nominal_bulan`
            FROM 
                `tagihan`
			JOIN
                `users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE
                `tagihan`.`status` = 1 AND `tagihan`.`bulan` = '$bulan'
			ORDER BY
				`tagihan`.`angka_bulan` ASC ";
		$tagihan = GenericModel::rawSelect($tagihanQuery);	
		$nama = $bulan;	
	}
	if ($bulan == "0" AND $tahun != "0") {
		$tagihanQuery = "
            SELECT
                `tagihan`.`cid`,
                `tagihan`.`bulan`,
				`tagihan`.`tahun`,
				`tagihan`.`nominal` as usnominal,
				`tagihan`.`jumlah`,
				`tagihan`.`metode`,
				`tagihan`.`tanggal_bayar`,
				`users`.`full_name`,
				`users`.`armada`,
				`users`.`alamat`,
				`users`.`nominal_bulan`
            FROM 
                `tagihan`
			JOIN
                `users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE
                `tagihan`.`status` = 1 AND `tagihan`.`tahun` = '$tahun'
			ORDER BY
				`tagihan`.`tahun` ASC ";
		$tagihan = GenericModel::rawSelect($tagihanQuery);	
		$nama = $tahun;	
	}
	if ($bulan = $bulan AND $tahun = $tahun){
		$tagihanQuery = "
            SELECT
                `tagihan`.`cid`,
                `tagihan`.`bulan`,
				`tagihan`.`tahun`,
				`tagihan`.`nominal` as usnominal,
				`tagihan`.`jumlah`,
				`tagihan`.`metode`,
				`tagihan`.`tanggal_bayar`,
				`users`.`full_name`,
				`users`.`armada`,
				`users`.`alamat`,
				`users`.`nominal_bulan`
            FROM 
                `tagihan`
			JOIN
                `users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE
                `tagihan`.`status` = 1 AND `tagihan`.`tahun` = '$tahun' AND `tagihan`.`bulan` = '$bulan'
			ORDER BY
				`tagihan`.`tahun` ASC, `tagihan`.`angka_bulan` ASC 	";
        $tagihan = GenericModel::rawSelect($tagihanQuery);		
		$nama = $bulan.'-'.$tahun;
		}
        
      //foreach ($tagihan as $key => $value) {
	  //echo $value->bulan; echo $value->tahun; }
		// Create a new Spreadsheet Object 
		$styleArray = [
        'font' => [
            'bold'  =>  true,
            'size'  =>  11,
            'name'  =>  'Calibri'
        ]
		];
		$headerfile = strtoupper($nama);
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
		$sheet->getStyle('A1')->applyFromArray($styleArray);
		$sheet->getStyle('A1')->getAlignment()->setHorizontal('center');		
		$sheet->mergeCells('A1:E1');
		
        $sheet->setCellValue('A1', 'DATA TAGIHAN TPST MULYOAGUNG - '.$headerfile);
        $sheet->setCellValue('A2', 'NO');
        $sheet->setCellValue('B2', 'NAMA');
		$sheet->setCellValue('C2', 'BULAN');
		$sheet->setCellValue('D2', 'TAHUN');		
		$sheet->setCellValue('E2', 'NOMINAL');  
		$sheet->setCellValue('F2', 'ARMADA');
		

        $no = 3;
		$nomor = 1;
        foreach ($tagihan as $key => $value) {
            $sheet->setCellValue('A' . $no, $nomor);
            $sheet->setCellValue('B' . $no, $value->full_name.' - '.$value->alamat);
            $sheet->setCellValue('C' . $no, $value->bulan);
            $sheet->setCellValue('D' . $no, $value->tahun);
            $sheet->setCellValue('E' . $no, $value->nominal_bulan); 
            $sheet->setCellValue('F' . $no, $value->armada);
            $no++;
			$nomor++;
        }

		ob_end_clean();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="data-tagihan-tpst-'.$nama.'.xlsx"');
        $writer->save("php://output"); 
		
    }
	
	public static function exportDataPembayaran()
    {
		$bulan = Request::post('bulan');
		$tahun = Request::post('tahun');
		if ($bulan == null AND $tahun == null) {
		$tagihan = "
            SELECT
                `tagihan`.`cid`,
                `tagihan`.`bulan`,
				`tagihan`.`tahun`,
				`tagihan`.`nominal` as usnominal,
				`tagihan`.`jumlah`,
				`tagihan`.`angka_bulan`,
				`tagihan`.`metode`,
				`tagihan`.`tanggal_bayar`,
				`users`.`full_name`,
				`users`.`alamat`,
				`users`.`nominal_bulan`
            FROM 
                `tagihan`
			JOIN
                `users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE
                `tagihan`.`status` = 0
			ORDER BY
				`tagihan`.`tahun` ASC, `tagihan`.`angka_bulan` ASC ";
        $tagihan = GenericModel::rawSelect($tagihan);
		$nama = "semua tahun";		
		} 
		if ($bulan == "0" AND $tahun == "0"){
		$tagihan = "
            SELECT
                `tagihan`.`cid`,
                `tagihan`.`bulan`,
				`tagihan`.`tahun`,
				`tagihan`.`nominal` as usnominal,
				`tagihan`.`jumlah`,
				`tagihan`.`metode`,
				`tagihan`.`tanggal_bayar`,
				`users`.`full_name`,
				`users`.`alamat`,
				`users`.`nominal_bulan`
            FROM 
                `tagihan`
			JOIN
                `users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE
                `tagihan`.`status` = 0
			ORDER BY
				`tagihan`.`tahun` ASC, `tagihan`.`angka_bulan` ASC	";
        $tagihan = GenericModel::rawSelect($tagihan);		
		$nama = "semua";
		}
		if ($bulan != "0" AND $tahun == "0"){
		$tagihan = "
            SELECT
                `tagihan`.`cid`,
                `tagihan`.`bulan`,
				`tagihan`.`tahun`,
				`tagihan`.`nominal` as usnominal,
				`tagihan`.`jumlah`,
				`tagihan`.`metode`,
				`tagihan`.`tanggal_bayar`,
				`users`.`full_name`,
				`users`.`alamat`,
				`users`.`nominal_bulan`
            FROM 
                `tagihan`
			JOIN
                `users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE
                `tagihan`.`status` = 0 AND `tagihan`.`bulan` = '$bulan'
			ORDER BY
				`tagihan`.`angka_bulan` ASC";
        $tagihan = GenericModel::rawSelect($tagihan);
		$nama = $bulan;
		}
		if ($bulan == "0" AND $tahun != "0"){
		$tagihan = "
            SELECT
                `tagihan`.`cid`,
                `tagihan`.`bulan`,
				`tagihan`.`tahun`,
				`tagihan`.`nominal` as usnominal,
				`tagihan`.`jumlah`,
				`tagihan`.`metode`,
				`tagihan`.`tanggal_bayar`,
				`users`.`full_name`,
				`users`.`alamat`,
				`users`.`nominal_bulan`
            FROM 
                `tagihan`
			JOIN
                `users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE
                `tagihan`.`status` = 0 AND `tagihan`.`tahun` = '$tahun'
			ORDER BY
				`tagihan`.`tahun` ASC ";
        $tagihan = GenericModel::rawSelect($tagihan);		
		$nama = $tahun;
		}
		if ($bulan = $bulan AND $tahun = $tahun){
		$tagihan = "
            SELECT
                `tagihan`.`cid`,
                `tagihan`.`bulan`,
				`tagihan`.`tahun`,
				`tagihan`.`nominal` as usnominal,
				`tagihan`.`jumlah`,
				`tagihan`.`metode`,
				`tagihan`.`tanggal_bayar`,
				`users`.`full_name`,
				`users`.`alamat`,
				`users`.`nominal_bulan`
            FROM 
                `tagihan`
			JOIN
                `users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE
                `tagihan`.`status` = 0 AND `tagihan`.`tahun` = '$tahun' AND `tagihan`.`bulan` = '$bulan'
			ORDER BY
				`tagihan`.`tahun` ASC, `tagihan`.`angka_bulan` ASC 	";
        $tagihan = GenericModel::rawSelect($tagihan);		
		$nama = $bulan.'-'.$tahun;
		}
	    // export excel        
		$styleArray = [
        'font' => [
            'bold'  =>  true,
            'size'  =>  11,
            'name'  =>  'Calibri'
        ]
		];
		$styleArrayRed = [
        'font' => [
            'bold'  =>  true,
            'size'  =>  11,
            'name'  =>  'Calibri',
			'color' => array('rgb' => 'FF0000')
        ]
		];
		$styleArrayGreen = [
        'font' => [
            'bold'  =>  true,
            'size'  =>  11,
            'name'  =>  'Calibri',
			'color' => array('rgb' => '03C003')
        ]
		];
		$headerfile = strtoupper($nama);
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
		$sheet->getStyle('A1')->applyFromArray($styleArray);
		$sheet->getStyle('A1')->getAlignment()->setHorizontal('center');		
		$sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'DATA PEMBAYARAN TPST MULYOAGUNG - '.$headerfile);
        $sheet->setCellValue('A2', 'NO');
        $sheet->setCellValue('B2', 'NAMA');
		$sheet->setCellValue('C2', 'BULAN');
		$sheet->setCellValue('D2', 'TAHUN');		
		$sheet->setCellValue('E2', 'NOMINAL');
        $sheet->setCellValue('F2', 'JUMLAH BAYAR');
		$sheet->setCellValue('G2', 'METODE');
		$sheet->setCellValue('H2', 'TANGGAL BAYAR');
		
        $no = 3;
		$nomor = 1;
		$totalt = 0;
		$totalb = 0;
		$dua = 2;		
		$petugas = SESSION::get('full_name');
        foreach ($tagihan as $key => $value) {			
            $sheet->setCellValue('A' . $no, $nomor);
            $sheet->setCellValue('B' . $no, $value->full_name.' - '.$value->alamat);
            $sheet->setCellValue('C' . $no, $value->bulan);
            $sheet->setCellValue('D' . $no, $value->tahun);
            $sheet->setCellValue('E' . $no, $value->usnominal);
			$sheet->setCellValue('F' . $no, $value->jumlah);
			$sheet->setCellValue('G' . $no, $value->metode);
			$sheet->setCellValue('H' . $no, $value->tanggal_bayar);
			if ($value->usnominal > $value->jumlah) {
			$sheet->getStyle('F'.$no)->applyFromArray($styleArrayRed);
			} 
			if ($value->usnominal < $value->jumlah) {
			$sheet->getStyle('F'.$no)->applyFromArray($styleArrayGreen);	
			} 	
            $no++;
			$nomor++;
		$totalt = $totalt+$value->usnominal;	
		$totalb = $totalb+$value->jumlah;
		
        }
		$dua = $dua+$no;
		$sheet->setCellValue('A'.$no , 'Total');
		$sheet->setCellValue('E'.$no , $totalt);
		$sheet->setCellValue('F'.$no , $totalb);
		$sheet->mergeCells('A'.$no.':D'.$no);
		$sheet->mergeCells('A'.$dua.':H'.$dua);
		$sheet->getStyle('A'.$no)->getAlignment()->setHorizontal('center');
		$sheet->getStyle('A'.$dua)->getAlignment()->setHorizontal('center');
		$sheet->setCellValue('A'.$dua , 'data update : '.date('d M Y H:i:s').' petugas : '.$petugas);
		$sheet->getStyle('A'.$no)->applyFromArray($styleArray);
		$sheet->getStyle('E'.$no)->applyFromArray($styleArray);
		$sheet->getStyle('F'.$no)->applyFromArray($styleArray);
		ob_end_clean();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="data-pembayaran-tpst-'.$nama.'.xlsx"');
        $writer->save("php://output"); 
		//echo $nama;
    }
	
	
	public function pemasukanPembayaran()
    {
        Auth::isInSession('user_provider_type', 'employee');
		
		$bulan = isset($_GET['bulan']);
		$tahun = isset($_GET['tahun']);
		if(isset($_GET['bulan']) AND isset($_GET['tahun'])) {
			$bulan = Request::get('bulan');
			$tahun = Request::get('tahun');
			$terms = explode("+", trim($bulan));
            $first = true;
            $string_search = '';
            foreach($terms as $term)
                {
                    if($term != '') {
                        if(!$first) $string_search .= " AND ";
                         $string_search .= "`tagihan`.`bulan` LIKE '%".trim($term)."%'";
                          $first = false;
                    }
                }
		$where = $string_search. " AND `tagihan`.`status` = 0 AND `tagihan`.`tahun`= $tahun";		
		$totalpembayaran = "
            SELECT
				`tagihan`.`bulan`,
				`tagihan`.`tahun`,
				SUM(`jumlah`) as `total`				
            FROM 
                `tagihan`			
            WHERE 
				$where
			GROUP by
			`tagihan`.`bulan`, `tagihan`.`tahun`
			ORDER BY 
			`tagihan`.`tahun`, `tagihan`.`bulan` DESC
			 ";	
		$grand_total = "
            SELECT				
				SUM(`jumlah`) as `total`				
            FROM 
                `tagihan`			
            WHERE 
				$where			
			 ";     	
		} 		
		if($bulan != "0" AND $tahun == "0") {		 
        $grand_total = "
            SELECT				
				SUM(`jumlah`) as `total`				
            FROM 
                `tagihan`			
            WHERE 
				`tagihan`.`status` = 0  AND `tagihan`.`bulan`= '$bulan'			
			 ";
		$totalpembayaran = "
            SELECT
				`tagihan`.`bulan`,
				`tagihan`.`tahun`,
				SUM(`jumlah`) as `total`				
            FROM 
                `tagihan`			
            WHERE 
				`tagihan`.`status` = 0  AND `tagihan`.`bulan`= '$bulan'
			GROUP by
			`tagihan`.`bulan`, `tagihan`.`tahun`
			ORDER BY 
			`tagihan`.`tahun` DESC, `tagihan`.`angka_bulan` 
			 "; 
		
		}
		if($bulan == "0" AND $tahun != "0") {		 
        $grand_total = "
            SELECT				
				SUM(`jumlah`) as `total`				
            FROM 
                `tagihan`			
            WHERE 
				`tagihan`.`status` = 0  AND `tagihan`.`tahun`= '$tahun'			
			 ";
		$totalpembayaran = "
            SELECT
				`tagihan`.`bulan`,
				`tagihan`.`tahun`,
				SUM(`jumlah`) as `total`				
            FROM 
                `tagihan`			
            WHERE 
				`tagihan`.`status` = 0  AND `tagihan`.`tahun`= '$tahun'
			GROUP by
			`tagihan`.`bulan`, `tagihan`.`tahun`
			ORDER BY 
			`tagihan`.`tahun` DESC, `tagihan`.`angka_bulan` 
			 ";		
		}
		if($bulan == "0" AND $tahun == "0") {		 
        $grand_total = "
            SELECT				
				SUM(`jumlah`) as `total`				
            FROM 
                `tagihan`			
            WHERE 
				`tagihan`.`status` = 0		
			 ";
		$totalpembayaran = "
            SELECT
				`tagihan`.`bulan`,
				`tagihan`.`tahun`,
				SUM(`jumlah`) as `total`				
            FROM 
                `tagihan`			
            WHERE 
				`tagihan`.`status` = 0 
			GROUP by
			`tagihan`.`bulan`, `tagihan`.`tahun`
			ORDER BY 
			`tagihan`.`tahun` DESC, `tagihan`.`angka_bulan` 
			 ";		
		}	
		$pelanggan = "SELECT `users`.`uid`, `users`.`full_name` FROM `users` WHERE `users`.`user_provider_type` = 'pelanggan' AND `users`.`is_deleted` = 0";
        $data_pelanggan = GenericModel::rawSelect($pelanggan);
        $this->View->render('pelanggan/pemasukan',
              array(               
                'title' => 'Pemasukan',
                'activelink1' => 'Keuangan',
                'activelink2' => 'Pembayaran Tagihan',               	
				//'total_data_pembayaran' => GenericModel::rawSelect($total_data_pembayaran, false),
				'totalpembayaran' => GenericModel::rawSelect($totalpembayaran),
				'grand_total' => GenericModel::rawSelect($grand_total, false)				
				//'bulan' => $bulan,
				//'tahun' => $tahun,
				//'data_pelanggan' => $data_pelanggan
            )
        );
    }
	
	public function transaksi()
    {
       

        // delete these messages (as they are not needed anymore and we want to avoid to show them twice
       
		$pelanggan = "SELECT `users`.`uid`, `users`.`full_name`, `users`.`alamat` FROM `users` WHERE `users`.`user_provider_type` = 'pelanggan' AND `users`.`is_active` = 1";
        $data_pelanggan = GenericModel::rawSelect($pelanggan);
        $this->View->renderFileOnly('pelanggan/transaksi',
              array(               
                'title' => 'Transaksi',
                'activelink1' => 'Keuangan',
                'activelink2' => 'Transaksi',				
				'data_pelanggan' => $data_pelanggan
            )
        );
    }
	
	
	public function datatransaksi()
	{
	
	 
$data = "SELECT
					`tagihan`.`uid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`					
					FROM
					`tagihan`					
					WHERE
					`tagihan`.`status` = 0					
					";
	$data_tagihan = GenericModel::rawSelect($data);					
	$tran             = json_encode($data_tagihan);
	echo $tran;
	}
	
}