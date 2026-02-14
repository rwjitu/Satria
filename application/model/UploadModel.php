<?php

class UploadModel
{
    public static function uploadImage($image_name, $image_rename, $destination, $user_id, $note)
    {
        // check avatar folder writing rights, check if upload fits all rules
        if (self::isFolderWritable($destination) AND self::isValidImageFile($image_name) AND self::isValidFileName($image_name)) {

            //replace semua yang bukan angka dan huruf dengan -
            $file_name  = strtolower(preg_replace("/[^A-Za-z0-9]/", '-', $image_rename));
            $file_extension = strtolower(pathinfo($_FILES[$image_name]['name'], PATHINFO_EXTENSION));
            //for value in database
            $file_path = $destination . '/' . $file_name . '-' . $user_id . '.' . $file_extension;
            $target_file_path = Config::get('PATH_UPLOAD') . $file_path;
            //if they DID upload a file...
                if($_FILES[$image_name]['name']) {
                    //if no errors...
                    if(!$_FILES[$image_name]['error']) {
                        //chek if upload success
                        if (move_uploaded_file($_FILES[$image_name]['tmp_name'], $target_file_path)) {
                            self::writeToDatabase($destination, $user_id, $image_rename, $file_path, $note);
                            return true;
                        } else {
                            return false;
                        }
                    } else { //if there is an error...
                        return false;
                    }
                } else {
                    return false;
                }
        } else {
            return false;
        }
        return false; // default return
    }
	
    public static function uploadDocument($inputName, $uid, $kategori, $itemName, $note = '')
    {
        // konfigurasi singkat
        $allowed = ['jpg','jpeg','png','pdf','docx'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $baseDir = rtrim(Config::get('PATH_UPLOAD'), '/'); // contoh: /var/www/html/uploads
        $destDir = $baseDir . '/dokumenPokok';

        // cek file ada
        if (!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) {
            error_log('uploadDocument: no file uploaded');
            return false;
        }

        // cek error upload
        if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            error_log('uploadDocument: upload error code ' . $_FILES[$inputName]['error']);
            return false;
        }

        // cek ukuran
        if ($_FILES[$inputName]['size'] > $maxSize) {
            error_log('uploadDocument: file too large ' . $_FILES[$inputName]['size']);
            return false;
        }

        // cek ekstensi
        $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            error_log('uploadDocument: extension not allowed ' . $ext);
            return false;
        }

        // pastikan folder ada
        if (!is_dir($destDir) && !mkdir($destDir, 0755, true)) {
            error_log('uploadDocument: cannot create dir ' . $destDir);
            return false;
        }

        // buat nama file unik: slug + uid + timestamp
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($itemName ?: pathinfo($_FILES[$inputName]['name'], PATHINFO_FILENAME)));
        $slug = trim($slug, '-');
        $filename = $slug . '-' . $uid . '-' . time() . '.' . $ext;
        $relativePath = 'dokumenPokok/' . $filename;
        $target = $destDir . '/' . $filename;

        // pindahkan file
        if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $target)) {
            error_log('uploadDocument: move_uploaded_file failed to ' . $target);
            return false;
        }

        // simpan ke DB
        $db = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO dokumen_pokok (kategori, uid_sdm, id_dokumen, item_name, value, note, creator_id, created_at)
                VALUES (:kategori, :uid_sdm, :id_dokumen, :item_name, :value, :note, :creator_id, NOW())";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute([
            ':kategori'   => $kategori,
            ':uid_sdm'    => $uid,
            ':id_dokumen' => GenericModel::uuid_v4(),
            ':item_name'  => $itemName,
            ':value'      => $relativePath,
            ':note'       => $note,
            ':creator_id' => Session::get('uid'),
        ]);

        if (!$ok) {
            // jika gagal simpan DB, hapus file yang sudah diupload
            @unlink($target);
            error_log('uploadDocument: DB insert failed: ' . print_r($stmt->errorInfo(), true));
            return false;
        }

        return true;
    }

	public static function replaceDokumenPokok($id_dokumen, $uid_sdm, $fileArray, $kategori, $item_name, $note)
{
    $db = DatabaseFactory::getFactory()->getConnection();
    $uploadBase = rtrim(Config::get('PATH_UPLOAD'), '/'); // mis: /var/www/html/uploads
    $destDir = $uploadBase . '/dokumenPokok';
    $allowed = ['jpg','jpeg','png','pdf','docx'];
    $maxSize = 5 * 1024 * 1024;

    try {
        $db->beginTransaction();

        // ambil record lama
        $stmt = $db->prepare("SELECT value FROM dokumen_pokok WHERE id_dokumen = :id_dokumen AND uid_sdm = :uid_sdm LIMIT 1");
        $stmt->execute([':id_dokumen' => $id_dokumen, ':uid_sdm' => $uid_sdm]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$old) {
            $db->rollBack();
            error_log('replaceDokumenPokok: record not found ' . $id_dokumen);
            return false;
        }
        $oldPath = $old['value']; // relatif, mis: dokumenPokok/xxx.pdf
        $oldFull = $uploadBase . '/' . $oldPath;

        // jika ada file baru
        if ($fileArray && !empty($fileArray['name'])) {
            if ($fileArray['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Upload error code ' . $fileArray['error']);
            }
            if ($fileArray['size'] > $maxSize) {
                throw new Exception('File terlalu besar');
            }
            $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                throw new Exception('Tipe file tidak diizinkan');
            }

            // pastikan folder ada
            if (!is_dir($destDir) && !mkdir($destDir, 0755, true)) {
                throw new Exception('Gagal membuat folder upload');
            }

            // nama file baru unik
            $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($item_name ?: pathinfo($fileArray['name'], PATHINFO_FILENAME)));
            $slug = trim($slug, '-');
            $newFilename = $slug . '-' . $uid_sdm . '-' . time() . '.' . $ext;
            $relativeNew = 'dokumenPokok/' . $newFilename;
            $target = $destDir . '/' . $newFilename;

            if (!move_uploaded_file($fileArray['tmp_name'], $target)) {
                throw new Exception('Gagal memindahkan file upload');
            }

            // update DB: value + metadata
            $updateSql = "UPDATE dokumen_pokok SET kategori = :kategori, item_name = :item_name, value = :value, note = :note, updated_at = NOW() WHERE id_dokumen = :id_dokumen";
            $uStmt = $db->prepare($updateSql);
            $uOk = $uStmt->execute([
                ':kategori' => $kategori,
                ':item_name' => $item_name,
                ':value' => $relativeNew,
                ':note' => $note,
                ':id_dokumen' => $id_dokumen
            ]);
            if (!$uOk) {
                // hapus file baru jika gagal update DB
                @unlink($target);
                throw new Exception('Gagal update DB');
            }

            // hapus file lama (jika ada dan file berbeda)
            if (!empty($oldFull) && file_exists($oldFull) && realpath($oldFull) !== realpath($target)) {
                @unlink($oldFull);
            }
        } else {
            // tidak ada file baru: update metadata saja
            $updateSql = "UPDATE dokumen_pokok SET kategori = :kategori, item_name = :item_name, note = :note, updated_at = NOW() WHERE id_dokumen = :id_dokumen";
            $uStmt = $db->prepare($updateSql);
            $uOk = $uStmt->execute([
                ':kategori' => $kategori,
                ':item_name' => $item_name,
                ':note' => $note,
                ':id_dokumen' => $id_dokumen
            ]);
            if (!$uOk) throw new Exception('Gagal update metadata');
        }

        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('replaceDokumenPokok error: ' . $e->getMessage());
        return false;
    }
}
	
	public static function deleteDokumenPokok($id_dokumen, $uid_sdm)
{
    $db = DatabaseFactory::getFactory()->getConnection();
    $uploadBase = rtrim(Config::get('PATH_UPLOAD'), '/'); // contoh: /var/www/html/uploads
    $archiveBase = $uploadBase . '/archive';
    try {
        $db->beginTransaction();

        // ambil record
        $stmt = $db->prepare("SELECT value FROM dokumen_pokok WHERE id_dokumen = :id_dokumen AND uid_sdm = :uid_sdm LIMIT 1");
        $stmt->execute([':id_dokumen' => $id_dokumen, ':uid_sdm' => $uid_sdm]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $db->rollBack();
            error_log("deleteDokumenPokok: record not found id_dokumen={$id_dokumen}");
            return false;
        }

        $relativePath = $row['value']; // mis: dokumenPokok/xxx.pdf
        $fullPath = $uploadBase . '/' . ltrim($relativePath, '/');

        // safety check: pastikan file berada di dalam uploadBase
        $realBase = realpath($uploadBase);
        $realFile = realpath($fullPath);
        if (!$realFile || strpos($realFile, $realBase) !== 0) {
            $db->rollBack();
            error_log("deleteDokumenPokok: file path invalid or outside upload dir: {$fullPath}");
            return false;
        }

        // buat folder archive per tahun/bulan
        $year = date('Y');
        $month = date('m');
        $archiveDir = $archiveBase . '/' . $year . '/' . $month;
        if (!is_dir($archiveDir) && !mkdir($archiveDir, 0755, true)) {
            $db->rollBack();
            error_log("deleteDokumenPokok: cannot create archive dir {$archiveDir}");
            return false;
        }

        // nama file archive: originalname + id_dokumen + timestamp
        $origName = basename($realFile);
        $safeOrig = preg_replace('/[^A-Za-z0-9\-\._]/', '_', $origName);
        $archiveName = pathinfo($safeOrig, PATHINFO_FILENAME) . '-' . $id_dokumen . '-' . time() . '.' . pathinfo($safeOrig, PATHINFO_EXTENSION);
        $archiveFull = $archiveDir . '/' . $archiveName;

        // pindahkan file ke archive
        if (!@rename($realFile, $archiveFull)) {
            // jika rename gagal, coba copy + unlink
            if (!@copy($realFile, $archiveFull) || !@unlink($realFile)) {
                $db->rollBack();
                error_log("deleteDokumenPokok: failed to move file to archive {$realFile} -> {$archiveFull}");
                return false;
            }
        }

        // update DB: hapus record
        $del = $db->prepare("DELETE FROM dokumen_pokok WHERE id_dokumen = :id_dokumen AND uid_sdm = :uid_sdm");
        $delOk = $del->execute([':id_dokumen' => $id_dokumen, ':uid_sdm' => $uid_sdm]);
        if (!$delOk) {
            // rollback and try to restore file (move back)
            // restore only if archive exists
            if (file_exists($archiveFull)) {
                @rename($archiveFull, $realFile);
            }
            $db->rollBack();
            error_log("deleteDokumenPokok: failed to delete DB record id_dokumen={$id_dokumen} error=" . print_r($del->errorInfo(), true));
            return false;
        }

        // optional: simpan log archive ke tabel audit (tidak wajib)
        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log("deleteDokumenPokok exception: " . $e->getMessage());
        return false;
    }
}

public static function replaceDokumenPegawai($uid, $uid_sdm, $fileArray, $kategori, $item_name, $note, $link)
{
    $db = DatabaseFactory::getFactory()->getConnection();
    $uploadBase = rtrim(Config::get('PATH_UPLOAD'), '/'); // mis: /var/www/html/uploads
    $destDir = $uploadBase . '/dokumenPegawai/' . $link;
    $allowed = ['jpg','jpeg','png','pdf','docx'];
    $maxSize = 5 * 1024 * 1024;

    try {
        $db->beginTransaction();

        // ambil record lama
        $stmt = $db->prepare("SELECT value FROM dokumen_pegawai WHERE uid = :uid AND uid_sdm = :uid_sdm LIMIT 1");
        $stmt->execute([':uid' => $uid, ':uid_sdm' => $uid_sdm]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$old) {
            $db->rollBack();
            error_log('replaceDokumenPokok: record not found ' . $uid);
            return false;
        }
        $oldPath = $old['value']; // relatif, mis: dokumenPokok/xxx.pdf
        $oldFull = $uploadBase . '/' . $oldPath;

        // jika ada file baru
        if ($fileArray && !empty($fileArray['name'])) {
            if ($fileArray['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Upload error code ' . $fileArray['error']);
            }
            if ($fileArray['size'] > $maxSize) {
                throw new Exception('File terlalu besar');
            }
            $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                throw new Exception('Tipe file tidak diizinkan');
            }

            // pastikan folder ada
            if (!is_dir($destDir) && !mkdir($destDir, 0755, true)) {
                throw new Exception('Gagal membuat folder upload');
            }

            // nama file baru unik
            $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($item_name ?: pathinfo($fileArray['name'], PATHINFO_FILENAME)));
            $slug = trim($slug, '-');
            $newFilename = $slug . '-' . $uid_sdm . '-' . time() . '.' . $ext;
            $relativeNew = 'dokumenPegawai/' . $link . '/' . $newFilename;
            $target = $destDir . '/' . $newFilename;

            if (!move_uploaded_file($fileArray['tmp_name'], $target)) {
                throw new Exception('Gagal memindahkan file upload');
            }

            // update DB: value + metadata
            $updateSql = "UPDATE dokumen_pegawai SET kategori = :kategori, item_name = :item_name, value = :value, note = :note, updated_at = NOW() WHERE uid = :uid";
            $uStmt = $db->prepare($updateSql);
            $uOk = $uStmt->execute([
                ':kategori' => $kategori,
                ':item_name' => $item_name,
                ':value' => $relativeNew,
                ':note' => $note,
                ':uid' => $uid
            ]);
            if (!$uOk) {
                // hapus file baru jika gagal update DB
                @unlink($target);
                throw new Exception('Gagal update DB');
            }

            // hapus file lama (jika ada dan file berbeda)
            if (!empty($oldFull) && file_exists($oldFull) && realpath($oldFull) !== realpath($target)) {
                @unlink($oldFull);
            }
        } else {
            // tidak ada file baru: update metadata saja
            $updateSql = "UPDATE dokumen_pegawai SET kategori = :kategori, item_name = :item_name, note = :note, updated_at = NOW() WHERE uid = :uid";
            $uStmt = $db->prepare($updateSql);
            $uOk = $uStmt->execute([
                ':kategori' => $kategori,
                ':item_name' => $item_name,
                ':note' => $note,
                ':uid' => $uid
            ]);
            if (!$uOk) throw new Exception('Gagal update metadata');
        }

        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('replaceDokumenzPegawai error: ' . $e->getMessage());
        return false;
    }
}

	public static function uploadDocumentPegawai($inputName, $id_dokumen, $kategori, $itemName, $uid_sdm, $note = '', $link)
    {
        // konfigurasi singkat
        $allowed = ['jpg','jpeg','png','pdf','docx'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $baseDir = rtrim(Config::get('PATH_UPLOAD'), '/'); // contoh: /var/www/html/uploads
        $destDir = $baseDir . '/dokumenPegawai/'. $link;

        // cek file ada
        if (!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) {
            error_log('uploadDocumentPegawai: no file uploaded');
            return false;
        }

        // cek error upload
        if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            error_log('uploadDocumentPegawai: upload error code ' . $_FILES[$inputName]['error']);
            return false;
        }

        // cek ukuran
        if ($_FILES[$inputName]['size'] > $maxSize) {
            error_log('uploadDocumentPegawai: file too large ' . $_FILES[$inputName]['size']);
            return false;
        }

        // cek ekstensi
        $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            error_log('uploadDocumentPegawai: extension not allowed ' . $ext);
            return false;
        }

        // pastikan folder ada
        if (!is_dir($destDir) && !mkdir($destDir, 0755, true)) {
            error_log('uploadDocumentPegawai: cannot create dir ' . $destDir);
            return false;
        }

        // buat nama file unik: slug + uid + timestamp
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($itemName ?: pathinfo($_FILES[$inputName]['name'], PATHINFO_FILENAME)));
        $slug = trim($slug, '-');
        $filename = $slug . '-' . $uid_sdm . '-' . time() . '.' . $ext;
        $relativePath = 'dokumenPegawai/' .$link . '/' . $filename;
        $target = $destDir . '/' . $filename;

        // pindahkan file
        if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $target)) {
            error_log('uploadDocument: move_uploaded_file failed to ' . $target);
            return false;
        }

        // simpan ke DB
        $db = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO dokumen_pegawai (uid, kategori, uid_sdm, id_dokumen, item_name, value, note, creator_id, created_at, asal_data)
                VALUES (:uid, :kategori, :uid_sdm, :id_dokumen, :item_name, :value, :note, :creator_id, NOW(), :asal_data)";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute([
            ':uid'    => GenericModel::uuid_v4(),
			':kategori'   => $kategori,
            ':uid_sdm'    => $uid_sdm,
            ':id_dokumen' => $id_dokumen,
            ':item_name'  => $itemName,
            ':value'      => $relativePath,
            ':note'       => $note,            
            ':asal_data'       => $link,            
            ':creator_id' => Session::get('uid'),
        ]);

        if (!$ok) {
            // jika gagal simpan DB, hapus file yang sudah diupload
            @unlink($target);
            error_log('uploadDocument: DB insert failed: ' . print_r($stmt->errorInfo(), true));
            return false;
        }

        return true;
    }
	
	public static function deleteDokumenPegawai($uid, $uid_sdm)
{
    $db = DatabaseFactory::getFactory()->getConnection();
    $uploadBase = rtrim(Config::get('PATH_UPLOAD'), '/'); // contoh: /var/www/html/uploads
    $archiveBase = $uploadBase . '/archive';
    try {
        $db->beginTransaction();

        // ambil record
        $stmt = $db->prepare("SELECT value FROM dokumen_pegawai WHERE uid = :uid AND uid_sdm = :uid_sdm LIMIT 1");
        $stmt->execute([':uid' => $uid, ':uid_sdm' => $uid_sdm]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $db->rollBack();
            error_log("deleteDokumenPegawai: record not found uid={$uid}");
            return false;
        }

        $relativePath = $row['value']; // mis: dokumenPokok/xxx.pdf
        $fullPath = $uploadBase . '/' . ltrim($relativePath, '/');

        // safety check: pastikan file berada di dalam uploadBase
        $realBase = realpath($uploadBase);
        $realFile = realpath($fullPath);
        if (!$realFile || strpos($realFile, $realBase) !== 0) {
            $db->rollBack();
            error_log("deleteDokumenPegawai: file path invalid or outside upload dir: {$fullPath}");
            return false;
        }

        // buat folder archive per tahun/bulan
        $year = date('Y');
        $month = date('m');
        $archiveDir = $archiveBase . '/' . $year . '/' . $month;
        if (!is_dir($archiveDir) && !mkdir($archiveDir, 0755, true)) {
            $db->rollBack();
            error_log("deleteDokumenPokok: cannot create archive dir {$archiveDir}");
            return false;
        }

        // nama file archive: originalname + id_dokumen + timestamp
        $origName = basename($realFile);
        $safeOrig = preg_replace('/[^A-Za-z0-9\-\._]/', '_', $origName);
        $archiveName = pathinfo($safeOrig, PATHINFO_FILENAME) . '-' . $uid_sdm . '-' . time() . '.' . pathinfo($safeOrig, PATHINFO_EXTENSION);
        $archiveFull = $archiveDir . '/' . $archiveName;

        // pindahkan file ke archive
        if (!@rename($realFile, $archiveFull)) {
            // jika rename gagal, coba copy + unlink
            if (!@copy($realFile, $archiveFull) || !@unlink($realFile)) {
                $db->rollBack();
                error_log("deleteDokumenPpegawau: failed to move file to archive {$realFile} -> {$archiveFull}");
                return false;
            }
        }

        // update DB: hapus record
        $del = $db->prepare("DELETE FROM dokumen_pegawai WHERE uid = :uid AND uid_sdm = :uid_sdm");
        $delOk = $del->execute([':uid' => $uid, ':uid_sdm' => $uid_sdm]);
        if (!$delOk) {
            // rollback and try to restore file (move back)
            // restore only if archive exists
            if (file_exists($archiveFull)) {
                @rename($archiveFull, $realFile);
            }
            $db->rollBack();
            error_log("deleteDokumenPegawai: failed to delete DB record uid={$udi} error=" . print_r($del->errorInfo(), true));
            return false;
        }

        // optional: simpan log archive ke tabel audit (tidak wajib)
        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log("deleteDokumenPegawai exception: " . $e->getMessage());
        return false;
    }
}
	
	 public static function uploadHerregistrasi($image_name, $image_rename, $destination, $user_id, $note, $tgl_bayar, $nominal)
    {
        // check avatar folder writing rights, check if upload fits all rules
        if (self::isFolderWritable($destination) OR self::isValidImageFile($image_name) OR self::isValidDocumentFile($image_name) AND self::isValidFileName($image_name)) {

            //replace semua yang bukan angka dan huruf dengan -
            $file_name  = strtolower(preg_replace("/[^A-Za-z0-9]/", '-', $image_rename));
            $file_extension = strtolower(pathinfo($_FILES[$image_name]['name'], PATHINFO_EXTENSION));
            //for value in database
            $file_path = $destination . '/' . $file_name . '-' . $user_id . '.' . $file_extension;
            $target_file_path = Config::get('PATH_UPLOAD') . $file_path;
            //if they DID upload a file...
                if($_FILES[$image_name]['name']) {
                    //if no errors...
                    if(!$_FILES[$image_name]['error']) {
                        //chek if upload success
                        if (move_uploaded_file($_FILES[$image_name]['tmp_name'], $target_file_path)) {
                            self::writeToHerregistrasiDatabase($destination, $user_id, $image_rename, $file_path, $note, $tgl_bayar, $nominal);
                            return true;
                        } else {
                            return false;
                        }
                    } else { //if there is an error...
                        return false;
                    }
                } else {
                    return false;
                }
        } else {
            return false;
        }
        return false; // default return
    }
	
	public static function uploadFoto($image_name, $image_rename, $destination, $user_id, $note)
    {
        // check avatar folder writing rights, check if upload fits all rules
        if (self::isFolderWritable($destination) AND self::isValidImageFile($image_name) AND self::isValidFileName($image_name)) {

            //replace semua yang bukan angka dan huruf dengan -
            $file_name  = strtolower(preg_replace("/[^A-Za-z0-9]/", ' ', $image_rename));
            $file_extension = strtolower(pathinfo($_FILES[$image_name]['name'], PATHINFO_EXTENSION));
            //for value in database
            $file_path = $destination . '/' . $file_name . '.' . $file_extension;
            $target_file_path = Config::get('PATH_UPLOAD') . $file_path;
            //if they DID upload a file...
                if($_FILES[$image_name]['name']) {
                    //if no errors...
                    if(!$_FILES[$image_name]['error']) {
                        //chek if upload success
                        if (move_uploaded_file($_FILES[$image_name]['tmp_name'], $target_file_path)) {
                            self::writeToDatabase($destination, $user_id, $image_rename, $file_path, $note);
                            return true;
                        } else {
                            return false;
                        }
                    } else { //if there is an error...
                        return false;
                    }
                } else {
                    return false;
                }
        } else {
            return false;
        }
        return false; // default return
    }
	
	public static function uploadFotoInventaris($image_name, $image_rename, $destination, $user_id)
    {
        // check avatar folder writing rights, check if upload fits all rules
        if (self::isFolderWritable($destination) AND self::isValidImageFile($image_name) AND self::isValidFileName($image_name)) {

            //replace semua yang bukan angka dan huruf dengan -
            $file_name  = strtolower(preg_replace("/[^A-Za-z0-9]/", '-', $image_rename));
            $file_extension = strtolower(pathinfo($_FILES[$image_name]['name'], PATHINFO_EXTENSION));
            //for value in database
            $file_path = $destination . '/' . $file_name . '.' . $file_extension;
			$update = array (
						'lokasi_file' => $file_path,
						'photo' => 1	
                         );
            $target_file_path = Config::get('PATH_UPLOAD') . $file_path;
            //if they DID upload a file...
                if($_FILES[$image_name]['name']) {
                    //if no errors...
                    if(!$_FILES[$image_name]['error']) {
                        //chek if upload success
                        if (move_uploaded_file($_FILES[$image_name]['tmp_name'], $target_file_path)) {
                             GenericModel::update('inventaris', $update, "`uid` = '{$user_id}'");
                            return true;
                        } else {
                            return false;
                        }
                    } else { //if there is an error...
                        return false;
                    }
                } else {
                    return false;
                }
        } else {
            return false;
        }
        return false; // default return
    }

   
	/**
     * Checks if the avatar folder exists and is writable
     *
     * @return bool success status
     */
    public static function isFolderWritable($destination)
    {
        if (is_dir(Config::get('PATH_UPLOAD') . $destination) AND is_writable(Config::get('PATH_UPLOAD') . $destination)) {
            return true;
        }

        Session::add('feedback_negative', "ERROR!, $destination folder tidak ada atau tidak writable");
        return false;
    }

    /**
     * Validates the image
     * Only accepts gif, jpg, png types
     * @see http://php.net/manual/en/function.image-type-to-mime-type.php
     *
     * @return bool
     */
    public static function isValidImageFile($image_name)
    {
        if (!isset($_FILES[$image_name]) AND !empty($_FILES[$image_name])) {
            Session::add('feedback_negative', 'GAGAL!, tipe file tidak dipilih');
            return false;
        }

        // if input file too big (>5MB)
        if ($_FILES[$image_name]['size'] > 3000000) {
            Session::add('feedback_negative', 'GAGAL!, Ukuran file (file size) tidak boleh melebihi 3MB');
            return false;
        }

        // get the image width, height and mime type
        $image_proportions = getimagesize($_FILES[$image_name]['tmp_name']);

        // if input file too small, [0] is the width, [1] is the height
        if ($image_proportions[0] < 100 OR $image_proportions[1] < 100) {
            Session::add('feedback_negative', 'GAGAL!, dimensi file (lebar x panjang) terlalu kecil, minimum 100 x 100 pixel');
            return false;
        }

        // if file type is not jpg, gif or png
        if (!in_array($image_proportions['mime'], array('image/jpeg', 'image/gif', 'image/png'))) {
            Session::add('feedback_negative', 'GAGAL!, tipe file tidak diijinkan, hanya gunakan JPG dan PNG');
            return false;
        }

        return true;
    }

    /**
     * Validates the image
     * Only accepts gif, jpg, png types
     * @see http://php.net/manual/en/function.image-type-to-mime-type.php
     *
     * @return bool
     */
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
    public static function writeToDatabase($category, $item_id, $item_name, $value, $note)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("INSERT INTO `upload_list` (`uid`, `category`, `item_id`, `item_name`, `value`, `note`, `creator_id`) VALUES (:uid, :category, :item_id, :item_name, :value, :note, :creator_id)");
        $query->execute(array(
                            ':uid' => GenericModel::guid(),
                            ':category' => $category,
                            ':item_id' => $item_id,
                            ':item_name' => $item_name,
                            ':value' => $value,
                            ':note' => $note,
                            ':creator_id' => SESSION::get('uid'),
                        ));
    }	
	
	public static function writeTodokumenPokok($kategori, $uid_sdm, $item_name, $value, $note)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("INSERT INTO `dokumen_pokok` (`kategori`, `uid_sdm`, `id_dokumen`, `item_name`, `value`, `note`, `creator_id`) VALUES (:kategori, :uid_sdm, :id_dokumen, :item_name, :value, :note, :creator_id)");
        $query->execute(array(                            
                            ':kategori' => $kategori,
                            ':uid_sdm' => $uid_sdm,
                            ':id_dokumen' => GenericModel::uuid_v4(),
							':item_name' => $item_name,
                            ':value' => $value,
                            ':note' => $note,
                            ':creator_id' => SESSION::get('uid'),
                        ));
    }	
	
	public static function writeToHerregistrasiDatabase($category, $item_id, $item_name, $value, $note, $tgl_bayar, $nominal)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("INSERT INTO `herregistrasi` (`uid`, `category`, `item_id`, `item_name`, `value`, `note`, `tgl_bayar`, `nominal`, `creator_id`) VALUES (:uid, :category, :item_id, :item_name, :value, :note, :tgl_bayar, :nominal, :creator_id)");
        $query->execute(array(
                            ':uid' => GenericModel::guid(),
                            ':category' => $category,
                            ':item_id' => $item_id,
                            ':item_name' => 'Herregistrasi',
                            ':value' => $value,
                            ':note' => $note,
							':tgl_bayar' => $tgl_bayar,
							':nominal' => $nominal,								
                            ':creator_id' => SESSION::get('uid'),
                        ));
    }

}
