<?php


class InventarisController extends Controller
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
	
	public function list_barang()
    {
        Auth::isInSession('user_provider_type', 'employee');
		       
        $field = "SELECT * 
					FROM 
					`inventaris`
					WHERE
					`inventaris`.`status` = 1
					ORDER BY
					`inventaris`.`item` DESC";
					
		$total_data_barang = "
            SELECT				
				COUNT(`uid`) as `total_barang`				
            FROM 
                `inventaris`			
            WHERE 
			`inventaris`.`status` = 1";				
        $data = GenericModel::rawSelect($field);
        $this->View->render('inventaris/list_barang',
              array(               
                'title' => 'Data Inventaris Barang',
                'activelink1' => 'pelanggan',
                'activelink2' => 'Pelanggan', 
				'total_data_barang' => GenericModel::rawSelect($total_data_barang, false),	
                'data' => $data
            )
        );
    }
	
	public function TambahDataBaru()
	{
	
	$post_array      = $_POST;
	$harga_p = Request::post('harga_perolehan');
	$harga_perolehan = str_replace('.', '', $harga_p);
	$harga_s = Request::post('harga_sekarang');
	$harga_sekarang = str_replace('.', '', $harga_s);
	
	$custom_array = array(
                        'status'    => 1,
                        'harga_perolehan' => $harga_perolehan,
						'harga_sekarang' => $harga_sekarang,
						'petugas'    => SESSION::get('uid'),
                        'time_input'    => date("Y-m-d H:i:s")
                        );
	$insert = array_merge($post_array, $custom_array);
	GenericModel::insert('inventaris', $insert);
	header('Location: ' . $_SERVER['HTTP_REFERER']);		
	}
	
	public function updateData($uid)
    {
			
	$post_array      = $_POST;
	$harga_p = Request::post('harga_perolehan');
	$harga_perolehan = str_replace('.', '', $harga_p);
	$harga_s = Request::post('harga_sekarang');
	$harga_sekarang = str_replace('.', '', $harga_s);
	$custom_array		= array(
						'harga_perolehan' => $harga_perolehan,
						'harga_sekarang' => $harga_sekarang,
						'petugas'    => SESSION::get('uid'),
						'time_input'    => date("Y-m-d H:i:s")
                         );
	$update = array_merge($post_array, $custom_array);			 
    GenericModel::update('inventaris', $update, "`uid` = '{$uid}'");
		$feedback_positive = Session::get('feedback_positive');
        $feedback_negative = Session::get('feedback_negative');
        // echo out positive messages
        if ($feedback_positive) {  
           Redirect::to('inventaris/list_barang');
		   Session::add('feedback_positive', 'SUKSES, Data Berhasil dperbarui');			
        }
        // echo out negative messages
        if ($feedback_negative) {
             Redirect::to('inventaris/list_barang', Session::add('feedback_negative', 'GAGAL, data tidak dapat diperbarui'));
        }
        // RESET counter feedback to unconfuse user   
	}
	
	 public function uploadPhoto($uid = null)
    {
        if (empty($uid)) {
            Redirect::to('inventaris/list_barang');
            Session::add('feedback_negative', 'GAGAL!, upload file tidak berhasil');
            exit;
        }

        $image_name = 'file_name';
        $image_rename = Request::post('image_name');
        $destination = 'photo';        
        UploadModel::uploadFotoInventaris($image_name, $image_rename, $destination, $uid);			
		Session::add('feedback_positive', 'Sukses!, upload file  berhasil');
        Redirect::to('inventaris/list_barang');	
    }
	
	public static function exportDataInventaris()
    {
	$inventaris = "
            SELECT
			*
            FROM 
			`inventaris`
			WHERE
			`inventaris`.`status` = 1
			ORDER BY
			`inventaris`.`item` DESC";
	$data = GenericModel::rawSelect($inventaris);
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
		
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
		$sheet->getStyle('A1')->applyFromArray($styleArray);
		$sheet->getStyle('A1')->getAlignment()->setHorizontal('center');		
		$sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'DATA INVENTARIS TPST MULYOAGUNG');
        $sheet->setCellValue('A2', 'NO');
        $sheet->setCellValue('B2', 'KODE');
		$sheet->setCellValue('C2', 'ITEM');
		$sheet->setCellValue('D2', 'MEREK');		
		$sheet->setCellValue('E2', 'TAHUN');
		$sheet->setCellValue('F2', 'JUMLAH');
        $sheet->setCellValue('G2', 'MASA');
		$sheet->setCellValue('H2', 'PAKAI');
		$sheet->setCellValue('I2', 'HARGA PEROLEHAN');
		$sheet->setCellValue('J2', 'HARGA SEKARANG');
		$sheet->setCellValue('K2', 'TOTAL PEROLEHAN');
		$sheet->setCellValue('L2', 'TOTAL SEKARANG');
		$sheet->setCellValue('M2', 'PENGGUNA');
		$sheet->setCellValue('N2', 'LOKASI');
		$sheet->setCellValue('O2', 'KONDISI');
		$sheet->setCellValue('P2', 'KETERANGAN');

        $no = 3;
		$nomor = 1;
		$totalt = 0;
		$totalb = 0;
		$dua = 2;				
        foreach ($data as $key => $value) {			
            $sheet->setCellValue('A' . $no, $nomor);
            $sheet->setCellValue('B' . $no, $value->kode);
            $sheet->setCellValue('C' . $no, $value->item);
            $sheet->setCellValue('D' . $no, $value->merek);
            $sheet->setCellValue('E' . $no, $value->tahun_perolehan);
			$sheet->setCellValue('F' . $no, $value->jumlah);
			$sheet->setCellValue('G' . $no, $value->masa_guna);
			$sheet->setCellValue('H' . $no, $value->lama_pakai);
			$sheet->setCellValue('I' . $no, $value->harga_perolehan);
			$sheet->setCellValue('J' . $no, $value->harga_sekarang);
			$sheet->setCellValue('K' . $no, $value->harga_perolehan*$value->jumlah);
			$sheet->setCellValue('L' . $no, $value->harga_sekarang*$value->jumlah);
			$sheet->setCellValue('M' . $no, $value->pemakai);
			$sheet->setCellValue('N' . $no, $value->lokasi);
			$sheet->setCellValue('O' . $no, $value->kondisi);	
			if ($value->kondisi == 'Rusak') {
			$sheet->getStyle('O'.$no)->applyFromArray($styleArrayRed);
			} 
			if ($value->kondisi == 'Baik') {
			$sheet->getStyle('O'.$no)->applyFromArray($styleArrayGreen);	
			}
			$sheet->setCellValue('P' . $no, $value->keterangan);			
            $no++;
			$nomor++;
		$totalt = $totalt+$value->harga_perolehan*$value->jumlah;	
		$totalb = $totalb+$value->harga_sekarang*$value->jumlah;
		
        }
		$dua = $dua+$no;
		$sheet->setCellValue('A'.$no , 'Total');
		$sheet->setCellValue('K'.$no , $totalt);
		$sheet->setCellValue('L'.$no , $totalb);
		$sheet->mergeCells('A'.$no.':J'.$no);		
		$sheet->getStyle('A'.$no)->getAlignment()->setHorizontal('center');
		$sheet->getStyle('A'.$dua)->getAlignment()->setHorizontal('center');		
		$sheet->getStyle('A'.$no)->applyFromArray($styleArray);
		$sheet->getStyle('K'.$no)->applyFromArray($styleArray);
		$sheet->getStyle('L'.$no)->applyFromArray($styleArray);
		ob_end_clean();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="data-inventaris-tpst.xlsx"');
        $writer->save("php://output"); 
	}
}
?>