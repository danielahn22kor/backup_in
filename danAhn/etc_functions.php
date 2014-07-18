<?php
//검색의 기본 url 생성
/***************************************************************
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
****************************************************************/
include_once('parser.php');
include_once('etc_functions.php');

function makeBasicUrl($searcher, $searchType)
{
	$basicUrl = 'search.daum.net/search?w=';
	$basicUrl .= $searchType;
	$basicUrl .= '&q=';
	$basicUrl .= $searcher;
	$basicUrl .= '&m=board&ASearchType=1&lpp=10&rlang=0&period=u&p=';
	return $basicUrl; 
}


//입력받은 기간 설정
function makeDateUrl($searchRange)
{
	$searchDate = '&sd=';
	$searchDate .= $searchRange[0];
	$searchDate .= '000000&ed=';
	$searchDate .= $searchRange[1];
	$searchDate .= '235959&page=1';
	return $searchDate;
}



//최종 url 조합
function makeUrl($basicUrl, $page, $backUrl)
{
	$url = $basicUrl;
	$url .= $page;
	$url .= $backUrl;
	return $url;
}


function makeCommentsPagesUrl($url, $totalPages, $curPage)
{
	$url .= "?prev_page=";
	$url .= $totalPages;
	$url .= "&mode=regular&cdepth=0000i00000&page=";
	$url .= $curPage;
	return $url;
}



function calcPage($url)
{
	$totalPages = 0;
	//총 게시글 건수를 얻기 위한 패턴 추가
	$patterns = array('/<span[^>]*class=\"f_nb f_l\">1-10 \/[ 약 ]*(.*)건/');
	//파싱
	$result = parsing($url, $patterns);
	$result[0][0][0]= preg_replace('([^0-9]*)', '', $result[0][0][0]);
	
	return ($result[0][0][0] / 10) + 1;
}



//태그, 여백 제거
function remvTags($target)
{
	//<br>빼고 모든 태그 제거
	$target = strip_tags($target, '<br>');
	//<br>과 개행문자가 같이있는것들 제거
	$target = preg_replace('/(\s)*(<br>)*\r\n(\r\n)*|(\s)*(<br>)*\r(\r)*|(\s)*(<br>)*\n(\n)*/i','',$target);
	//연속되는 <br>하나로 통합
	//$target = preg_replace('/<br[^>]*>(<br[^>]*>)+/is','<br>',$taget);
	//br style의 이상한것도 삭제
	$target = preg_replace('/<BR style(.*)<br/is','' ,$target);
	//이상하게 남는거 삭제		
	$target = preg_replace('/body \{ background-image(.*): transparent; \}/','',$target);
	//nbsp도 삭제
	$target = preg_replace('/style(.*)>/is','' ,$target);
	$target = preg_replace('/&nbsp;/','' ,$target);	
	//공백2개 이상이면 하나로 통합
	$target = preg_replace('/[\(\)]/','' ,$target);
	//따옴표 이스케이프
	$target = preg_replace('/[\'\"]/','' ,$target);
	$target = preg_replace('/[ ]{2,}/', ' ', $target); 
	return $target;
}

function overlapPage($url)
{
	//전역변수 사용
	global $compareStr;
	//값이 같으면 true
	if($url == $compareStr)
	{
		return true;
	}
	else
	{
		//다르면 url로 기존것을 덮고 false 반환
		$compareStr = $url;
		return false;
	}
}
?>