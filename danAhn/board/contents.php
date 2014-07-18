<?php
include_once('header.php');
require_once('preset.php');
?>
<?php
$cont_path =$_GET["cont_path"];
$txt_path = $_GET["txt_path"];
$total = $_GET["total"];
$now_page = $_GET["page"];

$record_per_page = 15;
$start_record = $record_per_page*($now_page-1);
$record_to_get = $record_per_page;

if( $start_record+$record_to_get > $total) 
{
  $record_to_get = $total - $start_record;
}

$q = "SELECT * FROM danAhnDB.".$txt_path." WHERE 1 ORDER BY text_idx LIMIT ".$start_record.", ".$record_to_get."";
$q_result=mysql_query($q,$connectSql);

?>

<table class="table">
	<thead>
		<th>글번호</th>
		<th>제목</th>
		<th>조회수</th>
	</thead>
	<?php 

		while($row=mysql_fetch_array($q_result))
		{
			$text_index = $row['text_idx'];
			if($text_index == NULL)
				break;
	?>
	<tr>
		<td><?php echo $row['text_idx'] ?> </td>
		<td><a href="view.php?text_idx=<?php echo $row['text_idx']; ?>&title=<?php echo $row['title']; ?>&cont_path=<?php echo $cont_path; ?>" ><?php echo $row['title']?></a></td>
		<td><?php echo $row['hits'] ?> </td>
<?php 
		}
		$page_per_block = 10;
		$now_block = ceil($now_page/$page_per_block);
		$total_page = ceil($total / $record_per_page);
		$total_blcok = ceil($total_page / $page_per_block); 
?>

	<div class="pagination">
	    <ul>
<?php

	if(1<$now_block ) {
	  $pre_page = ($now_block-1)*$page_per_block;
	  echo '<a href=contents.php?total='.$total.'&cont_path'.$cont_path.'&txt_path='.$txt_path.'&page='.$pre_page.'">이전</a>';

	}

	$start_page = ($now_block-1)*$page_per_block+1;

	$end_page = $now_block*$page_per_block;

	if($end_page>$total_page)
	 {
	  $end_page = $total_page;
	}

?>
	    
	<?php for($i=$start_page;$i<=$end_page;$i++) :?>
	    <li><a href="contents.php?total=<?php echo $total; ?>&cont_path=<?php echo $cont_path; ?>&txt_path=<?php echo $txt_path; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
	<?php endfor?>
	</ul>
	<?php
	if($now_block < $total_block) 
	{
	  $post_page = ($now_block)*$page_per_block+1;
	  echo '<a href="contents.php?total='.$total.'&cont_path'.$cont_path.'&txt_path='.$txt_path.'&page='.$post_page.'">다음</a>';
	}

	?>
	</div><!-- .pagination --> 


<?php
include_once('footer.php');
?>