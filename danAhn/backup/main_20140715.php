<?php
include_once('parsing_functions.php');
include_once('parser.php');
include_once('etc_functions.php');

$connectSql = mysql_connect("localhost","root","metrix") or die("openshit");
mysql_select_db('danAhnDB',$connectSql);

$compareStr = "";
$searchRange = array('20140701', '20140707');
$searcher = '갤럭시s5';

getUrl($searchRange, $searcher); //url을 얻어서 파일에 저장(추후 db에 저장)

$qry_result = mysql_query("SELECT URL from danAhnDB.text_Information", $connectSql);
while($gotUrl = mysql_fetch_array($qry_result))
{
	parsingText($gotUrl["URL"]);
}

$date = date("Y-m-d H:i:s"); //현재시간
echo "FINISH".$date;

mysql_close();
?>