<?php

//searcher
$where='';
if(isset($_GET['s']) && trim($_GET['s']) !== ''){
	$where = ' AND id=' . $_GET['s'];
}

$table_rows = 10;

$navigationMemory = true;

$labels = [
	['title' => 'Code', 'align' => 'center', 'sorter' => ''],
	['title' => 'Name', 'align' => '', 'sorter' => ''],
	['title' => 'Continent', 'align' => '', 'sorter' => 'text'],
	['title' => 'Population', 'align' => 'right', 'sorter' => 'number'],
	['title' => '', 'align' => 'center', 'sorter' => '', 'is_submenu' => true],
	['title' => '', 'align' => 'center', 'sorter' => '', 'is_submenu' => true],
	['title' => '', 'align' => 'center', 'sorter' => '', 'is_submenu' => true],
];

$mainQuery = '
SELECT
	Code,
	Name,
	Continent,
	Population,
	CONCAT("<a data-name=\"", Name, "\" data-id=\"", Code, "\" onclick=\"App.tryDetails(this)\">Check details</a>") AS act01,
	CONCAT("<a data-name=\"", Name, "\" data-id=\"", Code, "\" onclick=\"App.tryEdit(this)\">Edit record</a>") AS act02,
	CONCAT("<a data-name=\"", Name, "\" data-code=\"", Code, "\" onclick=\"App.tryDelete(this)\">Delete record</a>") AS act03
FROM
	country
WHERE
	Continent LIKE "%America"
ORDER BY
	Name ASC
';

$totalQuery = '
SELECT
	COUNT(*) total
FROM
	country
WHERE
	Continent LIKE "%America"
';

$fixedNumberRows = false;

$noRecordsMsg = '<b>NO RECORDS FOUND!</b>';

include '../tablajax.php';