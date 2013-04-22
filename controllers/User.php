<?php
require_once('DB.php');

function getPublicObjectVars($obj) {
  return get_object_vars($obj);
}

class User {
	private $db_conn;

	public $user_id;
	public $user_first;
	public $user_last;
	public $user_name;
	public $user_name_rev;
	public $user_type;
	public $canCreateCase;
	public $canArchiveCase;
	public $canDeleteCase;
	
	public function __construct($uid=0) {
		$this->user_id = $uid;
		if ($uid > 0) {
			$this->fetchUser();
			
		}
	}

	private function fetchUser() {
		$this->db_conn = DB::connCMS();
		$stmt = $this->db_conn->query('
			select t_user.*, IFNULL(sub_p.total, 0) open_cases
			FROM t_user 
			LEFT JOIN ( SELECT   COUNT(*) total, user_id
            FROM     t_case
            WHERE t_case.case_archived = 0
            GROUP BY user_id
          ) sub_p ON (sub_p.user_id = t_user.user_id)

			WHERE t_user.user_id='.$this->user_id.' LIMIT 1;
		');
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			foreach ($row as $k => $v) {
			    $this->$k = $v;
			}
			$this->makeFullNames();
		}
	}

	private function makeFullNames() {
			$this->user_name = $this->user_first . " " . $this->user_last;
			$this->user_name_rev = $this->user_last . ", " . $this->user_first;
	}

	private function deleteUser($uid) {
		$this->db_conn = DB::connCMS();
		$sth = $this->db_conn->prepare('UPDATE `t_user` SET user_type=9999 WHERE user_id = :uid');
		$sth->execute(array(':uid'=>$uid));
		return array('user_id' => $uid);
	}

	public function destroyUser($uid=0) {
		if ($uid == 0) { $uid = $this->user_id; }
		$data = $this->deleteUser($uid);
		$output = array(
			'status'		=> 'success',
		    'data' 			=> $data
		);		
		return json_encode($output);
	}

	public function updateUser($userJson) {
		$userData = (json_decode($userJson,true));
		$updateSeparator = "";
		foreach ($userData as $k => $v) {
			$this->$k = $v;
		    $cols[] = $k;
		    $vals[] = addslashes($v);
		    $updateValues .= $updateSeparator . "`".$k."`='".addslashes($v)."'";
		    $updateSeparator = ",";
    	}
    	$colnames = "`".implode("`, `", $cols)."`";
    	$colvals = "'".implode("', '", $vals)."'";
		$this->makeFullNames();
		$this->db_conn = DB::connCMS();
		$sth = $this->db_conn->prepare("INSERT INTO `t_user` ($colnames) VALUES ($colvals) ON DUPLICATE KEY UPDATE " . $updateValues);
		$sth->execute();
		if (!($this->user_id)) { $this->user_id = $this->db_conn->lastInsertId(); }
		$this->fetchUser();
	}

	public function getAllPublicProperties() {
        return getPublicObjectVars($this);
    }

    public function getJson() {
    	$data = ($this->getAllPublicProperties());
		$output = array(
			'status'		=> 'success',
		    'data' 			=> $data
		);		
		return json_encode($output);
    }

    public function getJsonList($limit=0,$start=0) {
    	$limitClause = $limit ? " LIMIT " . $limit : "";
    	$offsetClause = $start ? " OFFSET " . ($start-1)*$limit : "";
		$this->db_conn = DB::connCMS();
		$sth = $this->db_conn->query('
			SELECT 
				SQL_CALC_FOUND_ROWS
				`t_user`.`user_id`,
				`user_last`,
				`user_first`,
				`user_email`,
				`user_type`,
				`user_sub`,
				`canCreateCase`,
				`canArchiveCase`,
				`canDeleteCase`,
				`user_login`,
				`user_password`,
				IFNULL(sub_p.total, 0) open_cases
			FROM `t_user`
			LEFT JOIN ( SELECT   COUNT(*) total, user_id
            FROM     t_case
            WHERE t_case.case_archived = 0
            GROUP BY user_id
          ) sub_p ON (sub_p.user_id = t_user.user_id)
			  '. $limitClause .
			' '. $offsetClause
			);
		$sth->execute();
		$totalrows = ($this->db_conn->query('SELECT FOUND_ROWS();')->fetch(PDO::FETCH_COLUMN));
		$currentrows = ($sth->rowCount());
		$dataList = array();
		$i=0;
		while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
			$dataList[$i] = $result;
			$i++;
		}
		$output = array(
			'status'		=> 'success',
		    'totalrows' 	=> $totalrows,
		    'currentrows' 	=> $currentrows,
		    'data' 			=> $dataList
		);
		
		return json_encode($output);

    }
}
?>