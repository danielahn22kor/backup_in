<?php
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


function parsing($url, $pattern)
{
	if($url != '')
	{
		$iup = new HTMLParser();
		$iup->setUrl($url);
		
		foreach($pattern as $upgPatterns)
		{
			$iup->addPattern($upgPatterns);
		}
		$result = $iup->getResult();

	}
	else
	{
		$result = false;
	}
	
	return $result;
}




function parsingPage($url)
{
	//패이징 파싱 패턴
	$patterns = array('/<a[^>]*class=\"f_link_bu f_l\" href=\"http:\/\/(.*)\" onclick/');
	$result = parsing($url, $patterns);
	
	return $result;
}



//가장 초기에 실행되는 함수 url들을 다 얻어옴
function getUrl($searchRange, $searcher)
{
	
	//basicUrl을 만듦
	$basicUrl = makeBasicUrl($searcher,'cafe');
	//dateUrl을 만듦
	$dateUrl = makeDateUrl($searchRange);
	//writeUrl 파일에 씀(추후 db에 구현)
	writeUrl($basicUrl, $dateUrl, $searcher);
}



//url을 파일에 쓰는 함수
function writeUrl($basicUrl, $dateUrl, $searcher)
{
	GLOBAL $connectSql;
	//초기 url 생성
	$url = makeUrl($basicUrl, 1, $dateUrl); //1페이지를 기준으로 생성
	

	//총 페이지 계산
	$totalPage = calcPage($url);
	//file경로 생성 추후 db에 구현
	
	for($page = "1"; $page < $totalPage + "1"; $page++)
	{
		$url = makeUrl($basicUrl, $page, $dateUrl);

		//url을 얻어옴
		$result = parsingPage($url);
		
		//중복 방지를 위한 비교
		if(!overlapPage($result[0][0][0]))
		{
			//result들을 쓰기 시작
			for($resultCnt = 0; $resultCnt < 10; $resultCnt++)
			{
				if($result[0][0][$resultCnt] == NULL)
					break;
				mysql_query("INSERT INTO danAhnDB.text_Information(URL) VALUES ('".$result[0][0][$resultCnt]."') ",$connectSql);
			}
			
			}
			else
			{
				break;
			}
	
	}

}




//게시글 파싱하여 데이터를 모두 얻어옴
function parsingText($url)
{
	GLOBAL $connectSql;
	$mobileUrl = 'm.';
	$mobileUrl .= $url;
	if($mobileUrl != '')
	{

		$patterns = array(
											'/htmlEntityDecode\(\'(.*)\'/', //타이틀 패턴 [0][0][0] 에 저장
											'/class=\"txt_owner\">(.*)</',  //글쓴이 패턴 [1][0][0] 에 저장
											'/class=\"num_info\">(.*)</',   //날짜,조회수 패턴 [2][0][0]에 날짜 [2][0][1]에 조회수 저장
											'/<div id=\"article\">[[:space:]]*(.*)[[:space:]][[:blank:]]var arrAtta/is', //내용 패턴 [3][0][0]에 저장
											'/<a href=\"(.*)\" class=\"link_txt\">댓/i',//댓글 링크 패턴 [4][0][0] 저장
											'/<span class=\"num\">\((.*)\)</'  //댓글 갯수 패턴 [5][0][0]
											);
		
		$result = parsing($mobileUrl, $patterns);
		
		$result[3][0][0] = remvTags($result[3][0][0]);
		
		$qry_pIndexResult = mysql_query("SELECT text_Idx from danAhnDB.text_Information WHERE URL = '".$url."'",$connectSql);
		$text_Index = mysql_fetch_row($qry_pIndexResult);

		mysql_query("UPDATE danAhnDB.text_Information SET title = '".$result[0][0][0]."', hits ='".$result[2][0][1]."' WHERE text_idx = '".$text_Index[0]."'",$connectSql);
		mysql_query("INSERT INTO danAhnDB.Contents (inner_contents, reg_date, writer_id, text_idx) VALUES ('".$result[3][0][0]."', '".$result[2][0][0]."', '".$result[1][0][0]."', '".$text_Index[0]."')",$connectSql);
		
		$commentsUrl = makeUrl('m.cafe.daum.net', '', $result[4][0][0]);
		if(!($$commentsUrl == 'm.cafe.daum.net'));
		{
			$totPages = $result[5][0][0]/20 + 1;
			parsingComments($commentsUrl, $totPages, $text_Index[0]);
		}
		
		
	}
}



//코멘트 파싱하여 모두 얻어옴
function parsingComments($url, $totalPages, $text_Index)
{	
	GLOBAL $connectSql;
	$baseUrl = $url;
	if($url != '')
	{
		$patterns = array(//<span class=\"mentionNickname\">.*<\/span>[[:space:]]*(.*)[[:space:]]* 닉네임 뒤에 추출하는 정규식
												'/(<div class=\"article_tit\">[[:space:]]*(.*)[[:space:]]*<\/div>)|(<span class=\"mentionNickname\">.*<\/span>[[:space:]]*(.*)[[:space:]]*)/i', //내용 패턴 [0][0][0~]
												'/class=\"txt_owner\">(.*)</', //작성자 패턴 [1][0][1~]
												'/class=\"num_info\">(.*)</' //날짜 패턴 [2][0][2~]
											 );
		
		for($pages = 1; $pages <= $totalPages; $pages++)
		{
			$url = makeCommentsPagesUrl($baseUrl, $totalPages, $pages);
			$result = parsing($url, $patterns);
			
			$idx = 0;
			
			foreach($result[0][1] as $Contents)
			{
				if($Contents == NULL)
				{
					$Contents = $result[0][3][$idx];
				}
				$Contents = remvTags($Contents);
				mysql_query("INSERT INTO danAhnDB.Contents (inner_contents, reg_date, writer_id, text_idx) VALUES ('".$Contents."', '".$result[2][0][2+$idx]."', '".$result[1][0][1+$idx]."', '".$text_Index."')",$connectSql);
				$idx++;			
			}
		}
	}

}



