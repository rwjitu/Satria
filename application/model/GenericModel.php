<?php

/**
 * NoteModel
 * This is basically a simple CRUD (Create/Read/Update/Delete) demonstration.
 */
class GenericModel
{
    public static function getOne($table, $where = 1, $field = '*')
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT $field FROM $table WHERE $where LIMIT 1";
        $sth = $database->prepare($sql);
        $sth->execute();
        return $sth->fetch();
    }

    public static function getSome($table, $where = 1, $limit = 1, $page = 1, $field = '*')
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        // seting page pagination
        $offset = ($page > 1) ? ($page - 1) * $limit : 0;
        $sql = "SELECT $field FROM $table WHERE $where LIMIT $offset, $limit";
        $sth = $database->prepare($sql);
        $sth->execute();
        return $sth->fetchAll();
    }

    public static function getAll($table, $where = 1, $field = '*')
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT $field FROM $table WHERE $where";
        $sth = $database->prepare($sql);
        $sth->execute();
        return $sth->fetchAll();
    }

    public static function showAll($table)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT * FROM `$table`";
        $sth = $database->prepare($sql);
        $sth->execute();
        return $sth->fetchAll();
    }

    public static function rawSelect($query, $fetchAll = true)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sth = $database->prepare($query);
        $sth->execute();
        if ($fetchAll) {
            return $sth->fetchAll();
        } else {
            return $sth->fetch();
        }
    }

    public static function insert($table, $insert)
    {
        $keys=array_keys($insert);
        $value=array_values($insert);
        $value=str_ireplace("'", "\'", $value);

        $field="`".implode('`, `', $keys)."`";
        $values="'".implode('\', \'', $value)."'";
        
        $sql = "INSERT into `$table` ($field) values ($values)";
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare($sql);
        $query->execute();
        $count =  $query->rowcount();
        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', Text::get('INSERT_SUCCESS'));
            return true;
        }

        // default return
        Session::add('feedback_negative', Text::get('INSERT_FAILED'));
        return false;
    }

    public static function update($table, $update, $cond, $feedback=true) {
        foreach($update as $key=>$value){$prepare[]="`$key`= :{$key}";}
        foreach($update as $keys=>$value) {$execute[":{$keys}"] = $value;}
        $prepare = implode(", ", $prepare);
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE `$table` set $prepare where $cond";
        $query = $database->prepare($sql);
        $query->execute($execute);
        if ($query->rowcount() == 1 ) {
          if($feedback) Session::add('feedback_positive', Text::get('UPDATE_SUCCESS'));
          return true;
        } else {
          Session::add('feedback_negative', Text::get('UPDATE_FAILED'));
        }
        // default return
        return false;
  }

  /**
     * Delete a specific row
     * @param int $uid id of the note
     * @return bool feedback (was the note deleted properly ?)
     */
    public static function remove($table, $uid, $value, $feedback = true)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "DELETE FROM `$table` WHERE `$uid` = :value";
        $query = $database->prepare($sql);
        $query->execute(array(':value' => $value));

        if ($feedback == true) {
          if ($query->rowCount() == 1) {
              Session::add('feedback_positive', Text::get('DELETE_SUCCESS'));
              return true;
          }
          
          // default return
          Session::add('feedback_negative', Text::get('DELETE_FAIL'));
        }

        return false;
    }

    /**
     * Checks availability of rows
     *
     * @param $user_name string username
     *
     * @return bool
     */
    public static function isExist($table, $field, $value)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        //echo $oke = "SELECT `$field` FROM `$table` WHERE `$field` = '$value' LIMIT 1";
        $query = $database->prepare("SELECT `$field` FROM `$table` WHERE `$field` = :value LIMIT 1");
        $query->execute(array(':value' => $value));
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * Checks availability of rows
     *
     * @param $user_name string username
     *
     * @return bool
     */
    public static function checkData($sql)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare($sql);
        $query->execute();
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    public static function rowCount($table, $cond, $field = "*") { //better to select INDEXED field only
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT $field FROM $table WHERE $cond";
        $query = $database->prepare($sql);
        $query->execute();
        return $query->rowCount();
    }

    /**
     * Creates a token usable in a form
     * @return string
     */
    public static function guid() {
        // check funtion php exist or not
      if (function_exists('random_bytes')) {
          $token = strtoupper(bin2hex(random_bytes(21)));
      } elseif (function_exists('openssl_random_pseudo_bytes')) {
          $token = strtoupper(bin2hex(openssl_random_pseudo_bytes(21)));
      } else {
          $charid = strtoupper(md5(uniqid(rand(), true)));
          $hyphen = chr(45);// "-"
          $token = substr($charid, 0, 8).$hyphen
              .substr($charid, 8, 4).$hyphen
              .substr($charid,12, 4).$hyphen
              .substr($charid,16, 4).$hyphen
              .substr($charid,20,12);
      }
      return $token;
    }
	
	public static function tanggal_indo($tanggal, $cetak_hari = false)
	{
	$hari = array ( 1 =>    'Senin',
				'Selasa',
				'Rabu',
				'Kamis',
				'Jumat',
				'Sabtu',
				'Minggu'
			);
			
	$bulan = array (1 =>   'Januari',
				'Februari',
				'Maret',
				'April',
				'Mei',
				'Juni',
				'Juli',
				'Agustus',
				'September',
				'Oktober',
				'November',
				'Desember'
			);
	$split 	  = explode('-', $tanggal);
	$tgl_indo = $split[2] . ' ' . $bulan[ (int)$split[1] ] . ' ' . $split[0];
	
	if ($cetak_hari) {
		$num = date('N', strtotime($tanggal));
		return $hari[$num] . ', ' . $tgl_indo;
	}
	return $tgl_indo;
	}
	
	public static function uuid_v4(): string
{
    try {
        $data = random_bytes(16);
    } catch (Exception $e) {
        // fallback non-cryptographic (kurang direkomendasikan)
        $data = openssl_random_pseudo_bytes(16) ?: md5(uniqid((string)mt_rand(), true), true);
    }

    // set versi ke 4
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    // set variant ke RFC 4122
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

}
