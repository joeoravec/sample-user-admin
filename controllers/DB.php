<?php
class DB {
	public static function connCMS() {
		try {
    		$db_conn = new PDO('mysql:host=localhost;dbname=databasename','databaseuser', 'databasepw');
		} catch (PDOException $e) {
			$db_conn = "";
			echo "Cound not connect to database";
		}
		return $db_conn;		
	}
}
?>