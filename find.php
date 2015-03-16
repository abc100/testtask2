<?php

	header('Content-Type: text/html; charset=utf-8');

	error_reporting(E_ALL);
	
	define('DB_HOST','localhost');
	define('DB_USER', 'root');
	define('DB_PASSWORD', '');
	define('DB_NAME', 'estate');
	
	class DB {		
		private static $instance;
		
		private function __clone() {}
		private function __wakeup() {}
		
		public static function getInstance() {
			if (empty(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		private function __construct() {
			$this->connect = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('Unable connect to database: '.mysql_error());
			mysql_select_db(DB_NAME, $this->connect) or die('Unable to select database: '.mysql_error());
			$this->query('SET names "utf8"');
		}
		
		public static function query($sql) {
			$obj = self::$instance;
		
			if (isset($obj->connect)) {
				$result = mysql_query($sql) or die('SQL error: '.mysql_error());
				
				return $result;
			}
		}
		
		public static function fetch_array($res) {
			return mysql_fetch_array($res,MYSQL_ASSOC);
		}
		
		public static function fetch_object($res) {
			return mysql_fetch_object($res);
		}
		
		public static function num_rows($res) {
			return mysql_num_rows($res);
		}
			
		public static function escape_string($val) {
			return mysql_escape_string($val);
		}
	}
	
	DB::getInstance();
	
	function getCities() {
		$query = "SELECT * FROM cities";
		$result = DB::query($query);
		
		$arr = array();
		while($row = DB::fetch_array($result)) {
			$arr[] = $row;
		}
		
		$json_str = json_encode($arr); 
		
		echo $json_str;
	}
	
	function getApartments() {
		$json_str = '';
		$id_city = '';
		$id_trans_type = '';
		$square = '';
		$price = '';
		
		$query = "SELECT * FROM apartments 
					LEFT JOIN trans_types ON apartments.id_trans_type = trans_types.id 
					LEFT JOIN cities ON apartments.id_city = cities.id";
		
		if (isset($_POST['search'])) {
			$json_arr = json_decode($_POST['search'],true);		
			$query .= " WHERE ";
		}	
		
		if (isset($json_arr['id_city'])) {
			$id_city = strip_tags(substr($json_arr['id_city'],0, 100));
			$id_city = DB::escape_string($id_city);
			$query .= "id_city = $id_city";
		}
		
		if (isset($json_arr['id_trans_type'])) {
			$id_trans_type = strip_tags(substr($json_arr['id_trans_type'],0, 100));
			$id_trans_type = DB::escape_string($id_trans_type);
			$query .= " AND id_trans_type = $id_trans_type";
		}
		
		if (isset($json_arr['square'])) {
			$square = strip_tags(substr($json_arr['square'],0, 100));
			$square = DB::escape_string($square);
			$square = (int)$square;
			$squareError = $square/2;
			$square1 = $square - $squareError;
			$square2 = $square + $squareError;
			$query .= " AND (square BETWEEN $square1 AND $square2)";
		}
		
		if (isset($json_arr['price'])) {
			$price = strip_tags(substr($json_arr['price'],0, 100));
			$price = DB::escape_string($price);
			$price = (int)$price;
			$priceError = $price/2;
			$price1 = $price - $priceError;
			$price2 = $price + $priceError;
			$query .= " AND (price BETWEEN $price1 AND $price2)";			
		}
				
		$result = DB::query($query);	
		
		$arr = array();
		while($row = DB::fetch_array($result)) {
			$arr[] = $row;
		}
		
		$json_str = json_encode($arr); 
		
		echo $json_str;
	}
	
	if (isset($_POST['select'])) {
		getCities();
	}
	
	if (isset($_POST['search'])) {
		getApartments();
	}
	
?>