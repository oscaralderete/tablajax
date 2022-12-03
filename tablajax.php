<?php

use OscarAlderete\Tablajax;

require 'autoload.php';

require 'common/settings.php';

$tablajax = new Tablajax();
$tablajax->init_([
	'table_rows' => $table_rows,
	'navigation_memory' => $navigationMemory,
	'labels' => $labels,
	'sql' => $mainQuery,
	'sql_total' => $totalQuery,
	'fixed_number_rows' => $fixedNumberRows, // deprecated
	'no_records_msg' => $noRecordsMsg,
	'targetID' => isset($targetID) ? $targetID : 'tablajax',
	'callback' => isset($callback) ? $callback : ''
]);