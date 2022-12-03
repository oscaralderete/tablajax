<?php
/*
@author oscaralderete@gmail.com - http://oscaralderete.com
emplea la clase MySQLi
*/
namespace OscarAlderete;
class Db{

	private $s;
	private $u;
	private $p;
	private $db;
	private $charsetQuery;
	private $charset = 'utf8mb4';
	private $e1 = 'ERROR: No database connection';
	public $error = 'ERROR : No database connection';
	public $port = 3306;
	public $sql = '';
	public $conn = null;

	function __construct($db, $u, $p, $s = 'localhost'){
		$this->charsetQuery = 'SET NAMES "' . $this->charset . '"';
		$this->s = $s;
		$this->u = $u;
		$this->p = $p;
		$this->db = $db;
	}
	function dbOpen(){
		if($this->conn === null){
			$this->conn = new \mysqli($this->s, $this->u, $this->p, $this->db, $this->port);
			$this->conn->query($this->charsetQuery);
			$this->conn->set_charset($this->charset);
		}
	}
	function dbClose(){
		if($this->conn !== null){
			$this->conn->close();
			$this->conn=null;
		}
	}
	function dbGetSingle($sql){
		$this->sql = $sql;
		if ($r = $this->conn->query($sql)) {
			$s = $r->fetch_array(MYSQLI_ASSOC);
			return $s;
		} else {
			$this->conn->error;
			return false;
		}
	}
	function dbGetArray($sql){
		$this->sql = $sql;
		$s = [
			'result' => false,
			'error' => $this->e1,
			'rows' => 0,
			'array' => []
		];
		if($r = $this->conn->query($sql)){
			$i=[];
			while($a = $r->fetch_array(MYSQLI_ASSOC))
				$i[]=$a;
			$s=[
				'result' => true,
				'rows' => (int) $r->num_rows,
				'error' => 'No error',
				'array' => $i
			];
		}
		else
			$s['error'] = $this->conn->error;
		return $s;
	}
	function dbGetInsert($sql){
		$this->sql = $sql;
		$s = [
			'result' => false,
			'id' => 0,
			'error' => $this->e1
		];
		if($r = $this->conn->query($sql)){
			if($this->conn->affected_rows === 1 && $this->conn->insert_id > 0){
				$s['result'] = true;
				$s['id'] = (int) $this->conn->insert_id;
				$s['error'] = 'Insertion OK';
			}
		}
		else
			$s['error'] = $this->conn->error;
		return $s;
	}
	function dbGetUpdate($sql){
		$this->sql = $sql;
		$s=[
			'result' => false,
			'error' => $this->e1,
			'affected_rows' => 0
		];
		if($r = $this->conn->query($sql)){
			$info = $this->conn->info;
			$rows = (int) $this->conn->affected_rows;
			$s['affected_rows'] = $rows;
			preg_match('/Rows matched:([0-9]*)/i', $info, $r_matched);
			$resultado = ($rows < 1) ? ($r_matched[1] ? $r_matched[1] : 0) : $rows;
			if($resultado === 1 || $this->conn->affected_rows >= 1){
				$s['result'] = true;
				$s['error'] = 'Updating was OK';
			}
		}
		else
			$s['error'] = $this->conn->error;
			return $s;
	}
	function dbGetQuery($sql){
		$this->sql = $sql;
		$s = [
			'result' => false,
			'error' => $this->e1,
			'rows' => 0,
			'affected_rows' =>0
		];
		$sq = explode(';', $sql);
		for($i = 0; $i < count($sq); $i++){
			$sql = preg_replace("/\r|\n|\t/", '', $sq[$i]);
			if($sql !== ''){
				if($r = $this->conn->query($sql)){
					$s['result'] = true;
					$s['error'] = 'No error';
					$s['rows'] = (int) $this->conn->affected_rows;
					$s['affected_rows'] = (int) $s['rows'];
				}
				else{
					$j = $i + 1;
					$s['result'] = false;
					$s['error'] = 'ERROR [' . $j . ']: Query gets no results/' . $this->conn->error;
					break;
				}
			}
		}
		return $s;
	}
}