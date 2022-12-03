<?php
/*
@author Oscar Alderete <oscaralderete@gmail.com>
@website http://oscaralderete.com
*/
namespace OscarAlderete;

use OscarAlderete\Db;

class Tablajax{

	private $db;
	private $info = [];
	private $charset = 'utf-8';
	private $searchMaxResults = 200;
	private $logErrors = !false;
	private $totalColumns = 0;
	private $actionsMenu = false;
	private $labelTotalRecordsFound = 'Total records found:';
	private $labelLimitedSearchResults = '(showing just the first %u results)';
	private $svgIconMenu = '<svg class="tablajax-menu" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>';

	function __construct(){
		$this->db = new Db(DB_DATABASE, DB_USERNAME, DB_PASSWORD);
		$this->info = [
			'total_pages' => 0,
			'current_page' => 0,
			'total_records' =>0,
			'records' => [],
			'sql_error' => 'No error'
		];
	}

//page
	public function init_($array){
		$this->db->dbOpen();
		$this->getPageInfo($array);
		$this->renderTable($array);
	}

//privates
	private function getPageInfo($array){
		$this->info['total_columns'] = count($array['labels']);
		$aux = Explode('/', $_SERVER['SCRIPT_NAME']);
		$filename = $aux[count($aux) - 1];
		$cookieName = 'tablajaxPage_' . substr($filename, 0, strlen($filename) - 4);
		$this->info['url'] = $filename;
		$this->info['cookie_name'] = $cookieName;
		$sqlBegins = 0;
		$currentPage = 1;
		if($array['navigation_memory'] && isset($_COOKIE[$cookieName]) && 1 * $_COOKIE[$cookieName] > 0 && !isset($_GET['s'])){
			$sqlBegins = $array['table_rows'] * (1 * $_COOKIE[$cookieName] - 1);
			$currentPage = 1 * $_COOKIE[$cookieName];
		}
		if(isset($_POST['paginator']) && $_POST['paginator'] === 'true' && 1 * $_POST['page'] > 0){
			//for pagination/navigation
			$sqlBegins = $array['table_rows'] * (1 * $_POST['page'] - 1);
			$currentPage = 1 * $_POST['page'];
			if($array['navigation_memory']){
				if(isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] !== ''){
					$_COOKIE[$cookieName] = $_POST['page'];
					@setcookie($cookieName, $_POST['page']);
				}
				else{
					@setcookie($cookieName, $_POST['page'], time() + 30 * 60 * 24);
				}
			}
		}
		else{
			//on init
			if(trim($array['sql_total']) === ''){
				$array['sql_total'] = preg_replace('!SELECT.*?FROM!s', 'SELECT COUNT(*) AS total FROM', $array['sql']);
			}
			$r = $this->db->dbGetSingle($array['sql_total']);
			if($r !== false && 1 * $r['total'] > 0){
				$this->info['total_pages'] = ceil($r['total'] / $array['table_rows']);
				$this->info['total_records'] = 1 * $r['total'];
			}
		}
		$r = $this->db->dbGetArray($array['sql'] . ' LIMIT ' . $sqlBegins . ', ' . (isset($_GET['s']) && trim($_GET['s']) !== '' ? $this->searchMaxResults : $array['table_rows']));
		if($r['result'] && 1 * $r['rows'] > 0){
			$this->info['records'] = $r['array'];
			$this->info['current_page'] = $currentPage;
		}
		else{
			if($this->logErrors && !$r['result']){
				@error_log('[' . date('Y-m-d H:i:s') . '] ' . $this->db->sql ."\n", 3, '../../logs/tracer.log');
			}
		}
	}
	private function renderTable($array){
		header('Content-Type: text/javascript; charset=' . $this->charset);
		if(isset($_POST['paginator']) && $_POST['paginator'] === 'true' && 1 * $_POST['page'] > 0){
			echo $this->getBody($array);
		}
		else{
			$s = "jQuery(function($){";
			$s .= "$('#" . $array['targetID'] . "').empty().html('<table class=\"tablajax\"><thead>" . $this->getHeader($array) . "</thead><tbody>" . $this->getBody($array) . "</tbody><tfoot>" . $this->getFooter($array) . "</tfoot></table>');";
			if(isset($_GET['s'])){
				$s .= "$('#loader').hide();";
				$totalPages = '1';
				$currentPage = '1';
				$init = 'searcher_';
			}
			else{
				$totalPages = $this->info['total_pages'];
				$currentPage = $this->info['current_page'];
				$init = '';
			}
			$s .= "var my_" . $array['targetID'] . " = new tablajax({";
			$s .= "targetID: '" . $array['targetID']."',";
			$s .= "url: '" . $this->info['url']."',";
			$s .= "total_pages: " . $totalPages.",";
			$s .= "current_page: " . $currentPage;
			$s .= "});";
			$s .= "my_" . $array['targetID'] . "." . $init . "init();";
			//run custom callback function
			if(!empty($array['callback'])){
				$s .= "try{" . $array['callback'] . "();}";
				$s .= "catch(e){console.log(e)}";
			}
			$s .= "});";
			echo $s;
		}
	}
	private function getHeader($array){
		$s = "<tr>";
		$j = 0;
		$actions = 0;
		foreach($array['labels'] as $i){
			if(isset($i['is_submenu']) && $i['is_submenu'] === true){
				$actions++;
			}
			else{
				$s .= "<th class=\"sorter_" . ($i['sorter'] !== '' ? 'on' : 'off') . "\" data-column=\"" . $j . "\" data-type=\"" . $i['sorter'] . "\">";
				$s .= $i['title'];
				$s .= "</th>";
				$j++;
			}
		}
		if($actions>0){
			$s .= '<th class="action">&nbsp;</th>';
			$this->totalColumns = $j + 1;
			$this->actionsMenu = true;
		}
		else{
			$this->totalColumns = $j;
		}
		$s .= "</tr>";
		return $s;
	}
	private function getBody($array){
		$s = "";
		if(count($this->info['records']) > 0){
			foreach($this->info['records'] as $i){
				$s .= "<tr>";
				$menu = "";
				$j = 0;
				foreach($i as $k => $v){
					$line = str_replace(["'", "\r\n", "\r", "\n"], ['&apos;', '\\n', '\\n', '\\n'], $v);
					/*
					mapping contents IF DECLARED
					*/
					if(isset($array['labels'][$j]['map']) && is_array($array['labels'][$j]['map']) && count($array['labels'][$j]['map']) > 0){
						$line = $array['labels'][$j]['map'][$line];
					}
					if(isset($array['labels'][$j]['is_submenu']) && $array['labels'][$j]['is_submenu'] === true){
						$menu .= "<li>";
						$menu .= $line;
						$menu .= "</li>";
					}
					else{
						$s .= "<td class=\"" . $array['labels'][$j]['align'] . "\">";
						$s .= $line;
						$s .= "</td>";
					}
					$j++;
				}
				//actions
				if($menu !== ""){
					$s .= "<td class=\"action\">";
					$s .= "<a>" . $this->svgIconMenu . "</a>";
					$s .= "<ul>";
					$s .= $menu;
					$s .= "</ul>";
					$s .= "</td>";
				}
				$s .= "</tr>";
			}
		}
		else{
			$s .= "<tr>";
			$s .= "<td colspan=\"" . $this->totalColumns . "\" class=\"center\">" . (isset($_GET['s']) && trim($_GET['s']) !== '' && $_GET['s'] !== 'extended_filters' ? 'La búsqueda de: <b>' . $_GET['s'] . '</b>, no produjo resultados..' : $array['no_records_msg']) . "</td>";
			$s .= "</tr>";
		}
		return $s;
	}
	private function getFooter($array){
		$s = "<tr>";
		$s .= "<td colspan=\"" . $this->totalColumns . "\">";
		$s .= "<div  class=\"tdNavigation\">";
		if($this->info['total_pages'] > 1 && !isset($_GET['s'])){
			$s .= "<div class=\"tablajax_totals\">Total records: " . $this->info['total_records'] . "</div>";
			$options = "";
			$currentPage = 1;
			if($array['navigation_memory'] && isset($_COOKIE[$this->info['cookie_name']]) && 1 * $_COOKIE[$this->info['cookie_name']] > 0){
				$currentPage = 1 * $_COOKIE[$this->info['cookie_name']];
			}
			for($i = 1; $i <= $this->info['total_pages']; $i++){
				$selected = ($i === $currentPage) ? ' selected' : '';
				$options .= "<option value=\"" . $i . "\"" . $selected . ">" . $i . "</option>";
			}
			$s .= "<div class=\"navigationCommands\">";
			$s .= "<span name=\"goFirstPage\" class=\"goFirst icoNavigation icoFirst firstOnes\"></span>";
			$s .= "<span name=\"goPreviousPage\" class=\"goPrevious icoNavigation icoPrevious firstOnes\"></span>";
			$s .= "<select>" . $options . "</select>";
			$s .= "<span name=\"goNextPage\" class=\"goNext icoNavigation icoNext lastOnes\"></span>";
			$s .= "<span name=\"goLasttPage\" class=\"goLast icoNavigation icoLast lastOnes\"></span>";
			$s .= "</div>";
		}
		else{
			$s .= "<span class=\"tablajax_totals\">";
			$s .= "<b>" . $this->labelTotalRecordsFound . " " . $this->info['total_records'] . ($this->info['total_records'] > $this->searchMaxResults ? ' ' . str_replace(['%n'], [$this->searchMaxResults], $this->labelLimitedSearchResults) : '') . "</b>";
			$s .= "</span>";
		}
		$s .= "</div>";
		$s .= "</td>";
		$s .= "</tr>";
		return $s;
	}

}