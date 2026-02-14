<?php


class PelangganController extends Controller
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
	
	public function dataPelanggan($page = 1, $limit = 10000)
    {
        Auth::isInSession('user_provider_type', 'employee');
		       
        $field = '`users`.`uid`,
                    `users`.`phone`,                   
					`users`.`alamat`,
					`users`.`nominal_bulan`,
					`users`.`kategori`,
                    `users`.`work`,
					`users`.`armada`,
					`users`.`tanggal_mulai_pelanggan`,	
                    `users`.`full_name`';
        $table = '`users`';
        $where = "`users`.`user_provider_type` = 'pelanggan' AND `users`.`is_deleted` = 0";
        $contact = GenericModel::getAll($table, $where, $field);
		$total_data_pelanggan = "
            SELECT				
				COUNT(`uid`) as `total_user`,
				SUM(`is_active`) as `total_aktif`
            FROM 
                `users`			
            WHERE
                `users`.`user_provider_type` = 'pelanggan' AND `users`.`is_deleted` = 0";	

        
        $this->View->render('pelanggan/dataPelanggan',
              array(               
                'title' => 'Data Pelanggan',
                'activelink1' => 'pelanggan',
                'activelink2' => 'Pelanggan',
                'kategori' => GenericModel::getAll('kategori'),				
				'total_data_pelanggan' => GenericModel::rawSelect($total_data_pelanggan, false),
                'contact' => $contact
            )
        );
    }
	
	public function dataTagihan($page = 1, $limit = 50)
    {
        Auth::isInSession('user_provider_type', 'employee');
		$offset = ($page > 1) ? ($page - 1) * $limit : 0;
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
		$where = $string_search. " AND `tagihan`.`tahun`= $tahun";		
        $data = "SELECT	
					`tagihan`.`uid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,	
					`users`.`uid` as `userid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`full_name`,
					`users`.`armada`,
					`users`.`is_deleted`,
                    `users`.`alamat`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 1 AND $where
				ORDER BY
					`tagihan`.`tahun` DESC, `tagihan`.`angka_bulan` DESC
				LIMIT
					$offset, $limit
					";
        $total_data_tagihan = "
            SELECT				
				COUNT(`uid`) as `total_user`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 1 AND `tagihan`.`bulan` = '$bulan' AND `tagihan`.`tahun` = '$tahun'";	

        $data_tagihan = GenericModel::rawSelect($data);
		$prev = Config::get('URL') . 'pelanggan/dataTagihan/' . ($page - 1) . '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;
        $next = Config::get('URL') . 'pelanggan/dataTagihan/' . ($page + 1). '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;	
		$like = '?bulan='.$bulan.'&tahun='.$tahun;		
		} 
		if($bulan == "0" AND $tahun == "0" AND !isset($_GET['find'])) {	
		$data = "SELECT	
					`tagihan`.`uid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid` as `userid`,
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`full_name`,
					`users`.`armada`,
                    `users`.`alamat`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 1
				ORDER BY
					`tagihan`.`tahun` DESC, `tagihan`.`angka_bulan` DESC
				LIMIT
					$offset, $limit
				";
        $total_data_tagihan = "
            SELECT				
				COUNT(`uid`) as `total_user`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 1";
		$data_tagihan = GenericModel::rawSelect($data);	
		$prev = Config::get('URL') . 'pelanggan/dataTagihan/' . ($page - 1) . '/' . $limit;
        $next = Config::get('URL') . 'pelanggan/dataTagihan/' . ($page + 1). '/' . $limit;
		$like = '';		
		} else if($bulan == "0" AND $tahun == "0" AND isset($_GET['find'])) {
			$find = strtolower(Request::get('find')); //lower case string to easily (case insensitive) remove unwanted characters
            $terms = explode("+", trim($find));
            $first = true;
            $string_search = '';
            foreach($terms as $term)
                {
                    if($term != '') {
                        if(!$first) $string_search .= " OR ";
                          $string_search .= "`users`.`full_name` LIKE '%".trim($term)."%'";
                          $first = false;
                    }
                }
		$where = $string_search;			
		$data = "SELECT				
					`tagihan`.`uid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,					
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,	
					`users`.`uid` as `userid`,					
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`alamat`,
					`users`.`armada`,
					`users`.`full_name`
					FROM
					`tagihan`
					JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
					WHERE
					`tagihan`.`status` = 1 AND $where
					ORDER BY 
					`tagihan`.`tanggal_bayar` DESC
					LIMIT
					 $offset, $limit
					";
        $total_data_tagihan = "
            SELECT				
				COUNT(`cid`) as `total_user`,
				`users`.`alamat`,
				`users`.`full_name`	
            FROM 
                `tagihan`
			JOIN
				`users` ON `users`.`uid` = `tagihan`.`cid`		
            WHERE 
				`tagihan`.`status` = 1 AND $where";
			$data_tagihan = GenericModel::rawSelect($data);	
		$prev = Config::get('URL') . 'pelanggan/dataTagihan/' . ($page - 1) . '/' . $limit.'?find='.$find;
        $next = Config::get('URL') . 'pelanggan/dataTagihan/' . ($page + 1). '/' . $limit.'?find='.$find;
		$like = '?find='.$find;		
		}		
		if($bulan != "0" AND $tahun == "0") {
		$data = "SELECT	
					`tagihan`.`uid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid` as `userid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`full_name`,
					`users`.`armada`,
                    `users`.`alamat`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 1 AND `tagihan`.`bulan` = '$bulan'
				ORDER BY
					`tagihan`.`tahun` DESC, `tagihan`.`angka_bulan` DESC
				LIMIT
					$offset, $limit	
				";
        $total_data_tagihan = "
            SELECT				
				COUNT(`uid`) as `total_user`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 1 AND `tagihan`.`bulan` = '$bulan'";
		$data_tagihan = GenericModel::rawSelect($data);	
		$prev = Config::get('URL') . 'pelanggan/dataTagihan/' . ($page - 1) . '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;
        $next = Config::get('URL') . 'pelanggan/dataTagihan/' . ($page + 1). '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;	
		$like = '?bulan='.$bulan.'&tahun='.$tahun;	
		}
		if($bulan == "0" AND $tahun != "0") {
		$data = "SELECT	
					`tagihan`.`uid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid` as `userid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`full_name`,
					`users`.`armada`,
                    `users`.`alamat`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 1 AND `tagihan`.`tahun` = '$tahun'
				ORDER BY
					`tagihan`.`tahun` DESC, `tagihan`.`angka_bulan` DESC
				LIMIT
					$offset, $limit
				";
        $total_data_tagihan = "
            SELECT				
				COUNT(`uid`) as `total_user`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 1 AND `tagihan`.`tahun` = '$tahun'";
		$data_tagihan = GenericModel::rawSelect($data);
		$prev = Config::get('URL') . 'pelanggan/dataTagihan/' . ($page - 1) . '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;
        $next = Config::get('URL') . 'pelanggan/dataTagihan/' . ($page + 1). '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;	
		$like = '?bulan='.$bulan.'&tahun='.$tahun;		
		}
		$pelanggan = "SELECT `users`.`uid`, `users`.`full_name`, `users`.`nominal_bulan`, `users`.`alamat` FROM `users` WHERE `users`.`user_provider_type` = 'pelanggan' AND `users`.`is_deleted` = 0";
        
        $this->View->render('pelanggan/dataTagihan',
              array(
				'prev' => $prev,
                'next' => $next,
                'page' => $page,  
				'like' => $like,
				'limit'	=> $limit,
                'title' => 'Data Tagihan',
                'activelink1' => 'pelanggan',
                'activelink2' => 'Tagihan',
                'kategori' => GenericModel::getAll('kategori'),				
				'total_data_tagihan' => GenericModel::rawSelect($total_data_tagihan, false),
                'data_tagihan' => $data_tagihan,				
				'pelanggan' => GenericModel::rawSelect($pelanggan)
            )
        );
    }
	
	public function profil($uid) 
	{
        $pelanggan = "SELECT						
					`users`.`uid`,
					`users`.`full_name`,
					`users`.`nisn`,
					`users`.`alamat`,
					`users`.`phone`,
					`users`.`nominal_bulan`					
					FROM
					`users`						
					WHERE
					`users`.`uid` = '$uid' AND `users`.`is_active` = 1 LIMIT 1
					";		

        $this->View->render('pelanggan/profil', array(
                 'title' => 'Profil Pelanggan TPST 3R',
                'company' => GenericModel::getAll('`system_preference`', "`category` = 'company_identification' ORDER BY `item_name` ASC", '`value`, `item_name`'),
                'pelanggan' => GenericModel::rawSelect($pelanggan, false),
				'tagihan' => GenericModel::getAll('tagihan')	
        ));
        
    }
	
	public function pembayaran($page = 1, $limit = 50)
    {
        $offset = ($page > 1) ? ($page - 1) * $limit : 0;
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
        $data = "SELECT				
					`tagihan`.`uid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`metode`,
					`tagihan`.`petugas`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid` as `userid`,						
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`alamat`,
					`users`.`full_name`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					$where
				ORDER BY 
					`tagihan`.`tanggal_bayar` DESC
				LIMIT
					$offset, $limit
					";
		 $data_tagihan = "SELECT	
					`tagihan`.`uid` as `tid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`metode`,
					`tagihan`.`petugas`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`alamat`,
					`users`.`full_name`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 1
					";			
        $total_data_pembayaran = "
            SELECT				
				COUNT(`cid`) as `total_user`				
            FROM 
                `tagihan`			
            WHERE 
			$where";
		$totalpembayaran = "
            SELECT				
				SUM(`jumlah`) as `total_pembayaran`				
            FROM 
                `tagihan`			
            WHERE 
			$where";	
      $totaltagihan = "
            SELECT				
				SUM(`nominal`) as `total_tagihan`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 0 AND `tagihan`.`tahun`= $tahun AND `tagihan`.`bulan`= '$bulan'";	

        $data_tagihan = GenericModel::rawSelect($data_tagihan);
		$data_pembayaran = GenericModel::rawSelect($data);
		$prev = Config::get('URL') . 'pelanggan/pembayaran/' . ($page - 1) . '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;
        $next = Config::get('URL') . 'pelanggan/pembayaran/' . ($page + 1). '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;	
		$like = '?bulan='.$bulan.'&tahun='.$tahun;
		} 
		if($bulan == "0" AND $tahun == "0" AND !isset($_GET['find'])) {			
		$data = "SELECT				
					`tagihan`.`uid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`metode`,
					`tagihan`.`petugas`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid` as `userid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`alamat`,
					`users`.`full_name`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 0
				ORDER BY 
					`tagihan`.`tanggal_bayar` DESC
				LIMIT
					 $offset, $limit
					";
		 $data_tagihan = "SELECT	
					`tagihan`.`uid` as `tid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`metode`,
					`tagihan`.`petugas`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`alamat`,
					`users`.`full_name`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 1
					";			
        $total_data_pembayaran = "
            SELECT				
				COUNT(`cid`) as `total_user`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 0";
		$totalpembayaran = "
            SELECT				
				SUM(`jumlah`) as `total_pembayaran`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 0";	
      $totaltagihan = "
            SELECT				
				SUM(`nominal`) as `total_tagihan`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 0";	

        $data_tagihan = GenericModel::rawSelect($data_tagihan);
		$data_pembayaran = GenericModel::rawSelect($data);
		$prev = Config::get('URL') . 'pelanggan/pembayaran/' . ($page - 1) . '/' . $limit;
        $next = Config::get('URL') . 'pelanggan/pembayaran/' . ($page + 1). '/' . $limit;
		$like = '';	
		} else if($bulan == "0" AND $tahun == "0" AND isset($_GET['find'])) {
			$find = strtolower(Request::get('find')); //lower case string to easily (case insensitive) remove unwanted characters
            $terms = explode("+", trim($find));
            $first = true;
            $string_search = '';
            foreach($terms as $term)
                {
                    if($term != '') {
                        if(!$first) $string_search .= " OR ";
                          $string_search .= "`users`.`full_name` LIKE '%".trim($term)."%'";
                          $first = false;
                    }
                }
		$where = $string_search;			
		$data = "SELECT				
					`tagihan`.`uid`,
					`tagihan`.`bulan`,
					`tagihan`.`cid`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`metode`,
					`tagihan`.`petugas`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,					
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`uid` as `userid`,
					`users`.`alamat`,
					`users`.`full_name`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					$where AND `tagihan`.`status` = 0
				ORDER BY 
					`tagihan`.`tanggal_bayar` DESC
				LIMIT
					 $offset, $limit
					";
		 $data_tagihan = "SELECT	
					`tagihan`.`uid` as `tid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`metode`,
					`tagihan`.`petugas`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`alamat`,
					`users`.`full_name`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 1
					";			
        $total_data_pembayaran = "
            SELECT				
				COUNT(`cid`) as `total_user`,
				`users`.`alamat`,
				`users`.`full_name`	
            FROM 
                `tagihan`
			JOIN
				`users` ON `users`.`uid` = `tagihan`.`cid`		
            WHERE 
				$where AND`tagihan`.`status` = 0";
		$totalpembayaran = "
            SELECT				
				SUM(`jumlah`) as `total_pembayaran`,
				`users`.`alamat`,
				`users`.`full_name`		
            FROM 
                `tagihan`
			JOIN
				`users` ON `users`.`uid` = `tagihan`.`cid`		
            WHERE 
			$where AND `tagihan`.`status` = 0";	
      $totaltagihan = "
            SELECT				
				SUM(`nominal`) as `total_tagihan`,
				`users`.`alamat`,				
				`users`.`full_name`					
            FROM 
                `tagihan`
			JOIN
				`users` ON `users`.`uid` = `tagihan`.`cid`	
            WHERE 
				$where AND `tagihan`.`status` = 0";	

        $data_tagihan = GenericModel::rawSelect($data_tagihan);
		$data_pembayaran = GenericModel::rawSelect($data);
		$prev = Config::get('URL') . 'pelanggan/pembayaran/' . ($page - 1) . '/' . $limit.'?find='.$find;
        $next = Config::get('URL') . 'pelanggan/pembayaran/' . ($page + 1). '/' . $limit.'?find='.$find;
		$like = '?find='.$find;	
		}
		if($bulan == "0" AND $tahun != "0") {			
		$data = "SELECT				
					`tagihan`.`uid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`metode`,
					`tagihan`.`petugas`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid` as `userid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`alamat`,
					`users`.`full_name`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 0 AND `tagihan`.`tahun` = $tahun
				ORDER BY 
					`tagihan`.`tanggal_bayar` DESC
				LIMIT
					 $offset, $limit
					";
		 $data_tagihan = "SELECT	
					`tagihan`.`uid` as `tid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`metode`,
					`tagihan`.`petugas`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`alamat`,
					`users`.`full_name`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 1 AND `tagihan`.`tahun` = $tahun
					";			
        $total_data_pembayaran = "
            SELECT				
				COUNT(`cid`) as `total_user`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 0 AND `tagihan`.`tahun` = $tahun";
		$totalpembayaran = "
            SELECT				
				SUM(`jumlah`) as `total_pembayaran`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 0 AND `tagihan`.`tahun` = $tahun";	
      $totaltagihan = "
            SELECT				
				SUM(`nominal`) as `total_tagihan`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 0 AND `tagihan`.`tahun` = $tahun";	

        $data_tagihan = GenericModel::rawSelect($data_tagihan);
		$data_pembayaran = GenericModel::rawSelect($data);
		$prev = Config::get('URL') . 'pelanggan/pembayaran/' . ($page - 1) . '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;
        $next = Config::get('URL') . 'pelanggan/pembayaran/' . ($page + 1). '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;	
		$like = '?bulan='.$bulan.'&tahun='.$tahun;
		}
		if($bulan != "0" AND $tahun == "0") {			
		$data = "SELECT				
					`tagihan`.`uid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`metode`,
					`tagihan`.`petugas`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid` as `userid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`alamat`,
					`users`.`full_name`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 0 AND `tagihan`.`bulan` = '$bulan'
				ORDER BY 
					`tagihan`.`tanggal_bayar` DESC
				LIMIT
					 $offset, $limit
					";
		 $data_tagihan = "SELECT	
					`tagihan`.`uid` as `tid`,
					`tagihan`.`cid`,
					`tagihan`.`bulan`,
					`tagihan`.`tahun`,
					`tagihan`.`nominal`,
					`tagihan`.`jumlah`,
					`tagihan`.`metode`,
					`tagihan`.`petugas`,
					`tagihan`.`tanggal_tagihan`,
					`tagihan`.`tanggal_bayar`,
					`tagihan`.`status`,
					`users`.`uid`,	
					`users`.`nominal_bulan` as `usnominal`,
					`users`.`alamat`,
					`users`.`full_name`
				FROM
					`tagihan`
				JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
				WHERE
					`tagihan`.`status` = 1
				LIMIT
					 $offset, $limit
					";			
        $total_data_pembayaran = "
            SELECT				
				COUNT(`cid`) as `total_user`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 0 AND `tagihan`.`bulan` = '$bulan' ";
		$totalpembayaran = "
            SELECT				
				SUM(`jumlah`) as `total_pembayaran`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 0 AND `tagihan`.`bulan` = '$bulan'";	
      $totaltagihan = "
            SELECT				
				SUM(`nominal`) as `total_tagihan`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 0 AND `tagihan`.`bulan` = '$bulan'";	

        $data_tagihan = GenericModel::rawSelect($data_tagihan);
		$data_pembayaran = GenericModel::rawSelect($data);
		$prev = Config::get('URL') . 'pelanggan/pembayaran/' . ($page - 1) . '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;
        $next = Config::get('URL') . 'pelanggan/pembayaran/' . ($page + 1). '/' . $limit.'?bulan='.$bulan.'&tahun='.$tahun;		
		$like = '?bulan='.$bulan.'&tahun='.$tahun;
		}		
		$pelanggan = "SELECT `users`.`uid`, `users`.`full_name` FROM `users` WHERE `users`.`user_provider_type` = 'pelanggan' AND `users`.`is_deleted` = 0";
		$petugas = "SELECT `uid`, `full_name` FROM `users` WHERE user_provider_type = 'employee' ORDER BY `uid`";
        $data_pelanggan = GenericModel::rawSelect($pelanggan);
        $this->View->render('pelanggan/pembayaran',
              array( 
				'prev' => $prev,
                'next' => $next,
                'page' => $page,  
                'title' => 'Data Tagihan',
                'activelink1' => 'pelanggan',
                'activelink2' => 'Tagihan',
                'kategori' => GenericModel::getAll('kategori'),				
				'total_data_pembayaran' => GenericModel::rawSelect($total_data_pembayaran, false),
				'totalpembayaran' => GenericModel::rawSelect($totalpembayaran, false),
                'totaltagihan' => GenericModel::rawSelect($totaltagihan, false),
				'data_pembayaran' => $data_pembayaran,
                'data_tagihan' => $data_tagihan,
				'like' => $like,
				'petugas' => GenericModel::rawSelect($petugas),
				'limit'	=> $limit,				
				'data_pelanggan' => $data_pelanggan
            )
        );
    }

	public function updatePelanggan($uid)
    {
        //Start make log
        $oldData         = GenericModel::getOne('users', "`uid` = '{$uid}'", 'log');
        $post_array      = $_POST; // get all post array
        $post_array = array_filter($post_array);
        $log             = json_encode($_POST); // change to json to easily replaced like string
        $log             = str_replace('","', '<br />', $log);
        $log             = str_replace('":"', ' : ', $log);
        $log             = str_replace('_', ' ', $log);
        $log             = str_replace('{"', '', $log);
        $log             = str_replace('"}', '', $log);
        $log             = '<li><span class="badge badge-grey">' . SESSION::get('user_name') .'</span> edit employee:<br />' . $log . '<br>(' . date("Y-m-d") . ')</li>' . $oldData->log;
		$nom	   = Request::post('nominal_bulan');
		$nominal = str_replace('.', '', $nom);
      
       

        $custom_array = array(
                        'log'    => $log,
                        'modifier_id'    => SESSION::get('uid'),
						'nominal_bulan' => $nominal,
                        'modified_timestamp'    => date("Y-m-d H:i:s")
                        );
        
        $update = array_merge($post_array, $custom_array);
        GenericModel::update('users', $update, "`uid` = '$uid'");
        //Redirect::to('mahasiswa/detailMhs/' . $uid);
		header('Location: ' . $_SERVER['HTTP_REFERER']);
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
	
	public function updateMultiPembayaran()
    {
		$data      = $_POST;
		$petugas   =  SESSION::get('uid');
		$tanggal_bayar = date("Y-m-d");
		$time_detail_payment  = date("Y-m-d H:i:s");						
		$status = 0;	
		
		$database = DatabaseFactory::getFactory()->getConnection();
		
		if (isset($data['uid'], $data['jumlah'])) {        
		$uids = $data['uid'];
		$jumlahs = $data['jumlah'];
		$total = array_sum($jumlahs);
		$jumlah_data = count($uids);	
		
		for ($i = 0; $i < count($uids); $i++) {
		$uid = $uids[$i];
		$jumlah = $jumlahs[$i];
		$query = $database->prepare("UPDATE `tagihan` SET `jumlah` = :jumlah, `petugas` = :petugas, `status` = :status WHERE `uid` = :uid");
		$query->execute(array(':jumlah' => $jumlah, ':uid' => $uid, ':petugas' => $petugas, ':status' => $status));
		}
		$count =  $query->rowCount();
        if ($count == 1) {	
		Session::add('feedback_positive', 'Pilihan Program Studi Pertama sama dengan Pilihan Program Studi Kedua');
		Redirect::to('pelanggan/cetakNota/' . $uid);
            exit;		
        }  
 
		} else {
			
			echo '<script language="javascript">';
		echo 'alert("message successfully sent")';
		echo '</script>';
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		exit;
		}
		       
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
	
	/*public function inputTagihanBaru()
    {
      $uid 			= Request::post['uid'];
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
		
		
		
    }*/
	
	public function inputTagihanBaru() {
	
    $bulan = $_POST['bulan'];
    $tahun = $_POST['tahun'];
    $pelanggan = $_POST['cid'];
	
	$database = DatabaseFactory::getFactory()->getConnection();	
	$count = 0;
	$duplicates = [];
    foreach ($bulan as $b) {		
        switch ($b) {
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
            case 'Mei':
                $angka_bulan = 5;               
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
                break; // Pastikan untuk menambahkan `break` di sini
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
        foreach ($tahun as $t) {
            foreach ($pelanggan as $p) {
				$ambilnama         = GenericModel::getOne('users', "`uid` = '{$p}'", 'full_name');
				$ambilalamat         = GenericModel::getOne('users', "`uid` = '{$p}'", 'alamat');
				$nama = $ambilnama->full_name;
				$alamat = $ambilalamat->alamat;
                // Mengecek apakah data sudah ada
                $stmt_check = $database->prepare("SELECT * FROM tagihan WHERE bulan = :bulan AND tahun = :tahun AND cid = :cid");
                $stmt_check->execute([':bulan' => $b, ':tahun' => $t, ':cid' => $p]);

                if ($stmt_check->rowCount() == 0) {
                    // Jika data belum ada, lakukan insert
                    $stmt_insert = $database->prepare("INSERT INTO tagihan (bulan, angka_bulan, tahun, cid, status) VALUES (:bulan, :angka_bulan, :tahun, :cid, :status)");
                    $stmt_insert->execute([
					':bulan' => $b, 
					':angka_bulan' => $angka_bulan, 
					':tahun' => $t, 
					':cid' => $p,
					':status' => 1
					]);                   
                } else {
                   $duplicates[] = "$nama - $alamat | {$b} | {$t}";
                }
            }
        }
    }
	
	if ($count > 0) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        Session::add('feedback_positive', 'Hore, data berhasil disimpan');
    }

    if (!empty($duplicates)) {        
        $ganda = implode("<br> ", $duplicates);
		Session::add('feedback_duplicate', '<strong>Data Tagihan Sudah ada atau terbayar :</strong> <br>'.$ganda.'<hr> Silahkan cek..!');
        header('Location: ' . $_SERVER['HTTP_REFERER']);   
    } else {
       header('Location: ' . $_SERVER['HTTP_REFERER']);
        Session::add('feedback_positive', 'Hore, data berhasil disimpan');
    }

	}

	
	
	public function add_ajax_tagihan($cid){
		
	 $ambilnominal = GenericModel::getOne('`users`', "`uid` = {$cid}", '`nominal`');
	 $nominal = $ambilnominal->nominal;
	$pilih_tagihan = "SELECT	
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
                    `users`.`alamat`
					FROM
					`tagihan`
					JOIN
					`users` ON `users`.`uid` = `tagihan`.`cid`
					WHERE
					`tagihan`.`status` = 1 AND `tagihan`.`cid` = '$cid'
					ORDER BY
					`tagihan`.`tahun` DESC, `tagihan`.`angka_bulan` DESC
					";
		    
	$jumlah_tagihan = "SELECT				
				COUNT(`cid`) as `jumlah_tagihan`				
            FROM 
                `tagihan`			
            WHERE 
			`tagihan`.`status` = 1 AND `tagihan`.`cid` = '$cid'";			
			 
			$this->View->renderFileOnly('pelanggan/ajax_data_tagihan', array(
                'pilih_tagihan' => GenericModel::rawSelect($pilih_tagihan),
				'jumlah_tagihan' => GenericModel::rawSelect($jumlah_tagihan, false),
				'nominal' => $nominal
        ));
        
	}
	
	
}
?>