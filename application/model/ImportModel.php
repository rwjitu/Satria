<?php

class ImportModel
{
   public static function importData()
    {
		
	if(isset($_POST['import'])){
	$file_mimes = array('application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	if(isset($_FILES['berkas_excel']['name']) && in_array($_FILES['berkas_excel']['type'], $file_mimes)) {
 
    $arr_file = explode('.', $_FILES['berkas_excel']['name']);
    $extension = end($arr_file);
 
    if('csv' == $extension) {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
    } else {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    }
 
    $spreadsheet = $reader->load($_FILES['berkas_excel']['tmp_name']);     
    $sheetData = $spreadsheet->getActiveSheet()->toArray();	
	self::writeDatabase($sheetData);
	}
 	}	
    }
	
	
	public static function writeDatabase($sheetData)
    {
	
	$pin = rand(100000,999999);
    $user_password_hash = password_hash($pin, PASSWORD_DEFAULT);
	$database = DatabaseFactory::getFactory()->getConnection();		
	
	for ($i = 1;$i < count($sheetData);$i++)
		{
		$jumlah 				= count($sheetData)-1;
		$id_sdm     			= $sheetData[$i]['1'];
        $nama_sdm	  			= $sheetData[$i]['2'];
      	$nidn    				= $sheetData[$i]['3'];
        $nuptk     				= $sheetData[$i]['4'];
        $nip 					= $sheetData[$i]['5'];	
		$nama_status_aktif 		= $sheetData[$i]['6'];
		$nama_status_pegawai	= $sheetData[$i]['7'];
		$jenis_sdm 				= $sheetData[$i]['8'];
		$waktu_data_update 		= $sheetData[$i]['9'];		
		$phone 					= $sheetData[$i]['10'];		
		$email 					= $sheetData[$i]['11'];		
		$fakultas 				= $sheetData[$i]['12'];		
		$department 			= $sheetData[$i]['13'];		
		
		$query = $database->prepare("INSERT INTO 
									`users` (
											`nisn`,
											`user_name`,
											`id_sdm`,
											`nama_sdm`,
                                            `nuptk`,
											`nip`, 
											`nama_status_aktif`,
											`nama_status_pegawai`, 
											`jenis_sdm`,
											`waktu_data_update`,
											`phone`, 	
											`email`, 	
											`fakultas`, 	
											`department`, 	
											`pin`, 	
											`user_password_hash`, 	
											`user_provider_type`,
											`is_active`) 
									VALUES (
											:nisn,
											:user_name, 
											:id_sdm, 
											:nama_sdm,
                                            :nuptk,
											:nip, 
											:nama_status_aktif, 
											:nama_status_pegawai, 
											:jenis_sdm,
											:waktu_data_update,
											:phone,	
											:email,	
											:fakultas,	
											:department,	
											:pin,	
											:user_password_hash,	
											:user_provider_type,
											:is_active)");
		$load = $database->prepare("SELECT uid FROM users WHERE user_name = :user_name LIMIT 1");
        $load->execute(array(':user_name' => $nidn));									
		if ($load->rowCount() == 0)  {
		$query->execute(array(
						':nisn' => $nidn,
						':user_name' => $nidn,
                        ':id_sdm' => $id_sdm,
                        ':nama_sdm' => $nama_sdm,
          				':nuptk' => $nuptk,
                        ':nip' => $nip,
                        ':nama_status_aktif' => $nama_status_aktif,
						':nama_status_pegawai' => $nama_status_pegawai,
						':jenis_sdm' => $jenis_sdm,
						':waktu_data_update' => $waktu_data_update,
						':phone' => $phone,
						':email' => $email,
						':fakultas' => $fakultas,
						':department' => $department,						
						':pin' => $pin,						
						':user_password_hash' => $user_password_hash,						
						':user_provider_type' => 'pegawai',
						':is_active' => 1							
						));
		Session::add('feedback_positive', $jumlah.' Data Berhasil Dimport');					
										} else {			
		Session::add('feedback_negative', 'Terdapat Data Ganda');}											
		}    						
	}   
	
   public static function isValidDocumentFile($file_name)
    {
        if (!isset($_FILES[$file_name]) AND !empty($_FILES[$file_name])) {
            Session::add('feedback_negative', 'GAGAL!, tipe file tidak dipilih');
            return false;
        }

        // if input file too big (>5MB)
        if ($_FILES[$file_name]['size'] > 3000000) {
            Session::add('feedback_negative', 'GAGAL!, Ukuran file (file size) tidak boleh melebihi 3MB');
            return false;
        }


        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES[$file_name]['tmp_name']);
        $ok = false;
        switch ($mime) {
            case 'application/pdf':
                $ok = true;
                break;
            case 'application/msword': //.doc
                $ok = true;
                break;
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': //.docx
                $ok = true;
                break;
            case 'application/vnd.ms-powerpoint': //.ppt
                $ok = true;
                break;
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation': //.pptx
                $ok = true;
                break;
            case 'application/vnd.ms-excel': //.xls
                $ok = true;
                break;
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': //.xlsx
                $ok = true;
                break;
           default:
               $ok = false;
        }

        // if file type is not jpg, gif or png
        if (!$ok) {
            Session::add('feedback_negative', 'GAGAL!, tipe file tidak sesuai');
            return false;
        }

        return true;
    }

    /**
    * Check $_FILES[][name]
    *
    * @param (string) $filename - Uploaded file name.
    * @author Yousef Ismaeil Cliprz
    */
    public static function isValidFileName($file_name)
    {
        //make sure that the file name not bigger than 250 characters.
        if (mb_strlen($file_name,"UTF-8") > 225) {
            Session::add('feedback_negative', 'GAGAL!, nama file photo yang diupload lebih dari 250 karakter');
            return false;
        }
        return true;
    }


    /**
     * Writes marker to database, saying user has an avatar now
     *
     * @param $uid
     */
   
}
