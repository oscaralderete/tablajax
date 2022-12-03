<?php

//searcher
$where='';
if(isset($_GET['s']) && trim($_GET['s']) !== ''){
	$where = ' AND id=' . $_GET['s'];
}

$table_rows = 20;

$navigationMemory = true;

$labels = [
	['title' => 'Country code', 'align' => 'center', 'sorter' => 'text'],
	['title' => 'Name', 'align' => '', 'sorter' => ''],
	['title' => 'District', 'align' => '', 'sorter' => 'text'],
	['title' => 'Population', 'align' => 'center', 'sorter' => 'number'],
	['title' => 'Action', 'align' => 'center', 'sorter' => ''],
	['title' => 'Action', 'align' => 'center', 'sorter' => ''],
];

$mainQuery = '
SELECT
	CountryCode,
	Name,
	District,
	Population,
	CONCAT("<button data-name=\"", Name, "\" data-id=\"", ID, "\" onclick=\"App.tryEdit(this)\">Edit</button>") AS act01,
	CONCAT("<button data-name=\"", Name, "\" data-id=\"", ID, "\" onclick=\"App.tryDelete(this)\">Delete</button>") AS act02
FROM
	city
WHERE
	1
ORDER BY
	Name ASC
';

$totalQuery = '
SELECT
	COUNT(*) total
FROM
	city
WHERE
	1
';

$fixedNumberRows = false;

$targetID = 'cities';

$noRecordsMsg = '<b>NO RECORDS FOUND!</b>';

include '../tablajax.php';