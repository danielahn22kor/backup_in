<?php
/****************************************************************************************************************************************
						2014.07.10 
						검색후 url 파싱 테스트 php 파일
						안계완 만듦.
							
						2014-07-11 오후 5:30:55
						페이지 url 파싱 완료
						페이지에서 게시글 파싱 해오기 완료.
						댓글 게시글 파싱 완료.
						
						2014-07-15 오후 12:13:51
						쿼리 완성
						중복처리 쿼리 처리 불가
						겟방식으로 전달받아서 처리함 

						2014-07-16 
						검색어별 테이블 생성 처리
						자잘한 버그 수정 
***************************************************************************************************************************************/
include_once('parsing_functions.php'); //파싱과 관련된 함수들 
include_once('parser.php'); // open source HTMLparser
include_once('etc_functions.php'); //파싱 외 기타 함수들 
$startDate = date("Y-m-d H:i:s"); //현재시간
echo "\nStart\n".$startDate;
echo "\n";


$connectSql = mysql_connect("localhost","root","metrix") or die("openshit");
mysql_select_db('danAhnDB',$connectSql);
$compareStr = "";

$txt_infoTableName = $_GET["searcher"];
$txt_infoTableName .= "_text_Information";

$contents_TableName = $_GET["searcher"];
$contents_TableName .= "_Contents";

mysql_query("CREATE TABLE `".$txt_infoTableName."` (
	  `text_idx` int(10) unsigned NOT NULL auto_increment,
	  `title` varchar(50) default NULL,
	  `hits` int(5) unsigned zerofill default NULL,
	  `URL` varchar(300) default NULL,
	  PRIMARY KEY  (`text_idx`),
	  UNIQUE KEY `URL` (`text_idx`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='게시글 정보 저장';") ;


mysql_query("CREATE TABLE `".$contents_TableName."` (
	  `idx` int(10) unsigned NOT NULL auto_increment,
	  `inner_contents` text NOT NULL,
	  `reg_date` date default NULL,
	  `writer_id` varchar(100) default NULL,
	  `text_idx` int(10) unsigned NOT NULL,
	  PRIMARY KEY  (`idx`),
	  UNIQUE KEY `idx` (`idx`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='내용저장';") ;


getUrl(array($_GET["startDay"], $_GET["endDay"]),$_GET["searcher"]); //url을 얻어서 파일에 저장(추후 db에 저장)

$qry_result = mysql_query("SELECT URL from danAhnDB.".$txt_infoTableName."", $connectSql);
while($gotUrl = mysql_fetch_array($qry_result))
{
	parsingText($gotUrl["URL"]);
}
$finishDate = date("Y-m-d H:i:s"); //현재시간
?>
<br><br>
<?php
echo "\nFINISH\n".$finishDate;
$q = "SELECT * FROM danAhnDB.".$txt_infoTableName."";
$qry_result = mysql_query($q, $connectSql);
$q_total = mysql_num_rows($qry_result);
$q_total= preg_replace('([^0-9]*)', '', $q_total);
mysql_close();
?>
<p>
<td><a href="./board/contents.php?total=<?php echo $q_total; ?>&cont_path=<?php echo $contents_TableName; ?>&txt_path=<?php echo $txt_infoTableName; ?>&page=1&endDay=<?php echo $_GET["endDay"]?>">View Result </a></td>
</p>
