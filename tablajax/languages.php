<?php

//searcher
$where='1';
if(isset($_GET['s']) && trim($_GET['s']) !== ''){
	$where = ' Name LIKE "%' . $_GET['s'] . '%" OR Language LIKE "%' . $_GET['s'] . '%"';
}

$table_rows = 20;

$navigationMemory = true;

$labels = [
	['title' => 'Country code', 'align' => 'center', 'sorter' => ''],
	['title' => 'Country', 'align' => '', 'sorter' => ''],
	['title' => 'Language', 'align' => '', 'sorter' => 'text'],
	['title' => 'Official language', 'align' => 'center', 'sorter' => 'text'],
	['title' => 'Spoken by (%)', 'align' => 'right', 'sorter' => 'number'],
	['title' => 'Action', 'align' => 'center', 'sorter' => ''],
];

$mainQuery = '
SELECT
	CountryCode,
	Name,
	Language,
	IF(IsOfficial = "T", "True", "False") as is_official,
	Percentage,
	CONCAT("<a target=\"_blank\" href=\"https://countrycode.dev/api/countries/iso3/", Code, "\">JSON info</a>") AS act01
FROM
	country c INNER JOIN countrylanguage cl ON c.Code = cl.CountryCode
WHERE
	' . $where . '
ORDER BY
	Name ASC
';

$totalQuery = '
SELECT
	COUNT(*) total
FROM
	country c INNER JOIN countrylanguage cl ON c.Code = cl.CountryCode
WHERE
	' . $where;

$fixedNumberRows = true;



$noRecordsMsg = '<b>NO RECORDS FOUND!</b>';

include '../tablajax.php';