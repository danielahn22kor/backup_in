<?php
include_once('header.php');
require_once('preset.php');
?>
<?php
$text_idx = $_GET["text_idx"];
$title = $_GET["title"];
$q = "SELECT * FROM danAhnDB.아이폰5s_Contents WHERE text_idx = ".$text_idx."";
$q_result=mysql_query($q,$connectSql);
$data=mysql_fetch_array($q_result);
?>
	<table>
		<tr>
			<td>
				제목
			</td>
			<td>
				<?php echo $title; ?>
			</td>
		</tr>
		<tr>
			<td>
				작성자
			</td>
			<td>
				<?php echo $data['writer_id']; ?>
			</td>
		</tr>
		<tr>
			<td>
				등록일
			</td>
			<td>
				<?php echo $data['reg_date']; ?>
			</td>
		</tr>
		<tr>
			<td>
				내용
			</td>
			<td>
				<?php echo $data['inner_contents']; ?>
			</td>
		</tr>
	</table>
	<br><br><br><br>
	<thead>
		<th>댓글 작성자</th>
		<th>댓글 내용</th>
		<th>댓글 등록일</th>
	</thead>
	<table>
		<tr>
		<?php 
			while($data=mysql_fetch_array($q_result))
			{ ?>

			<td>
			<?php 
			if($data['writer_id'] == NULL)
			{
				echo 'none';
			}
			else
			{
				echo $data['writer_id']; 
			}
			?></td>
			<td><?php echo $data['inner_contents']; ?></td>
			<td><?php echo $data['reg_date']; ?></td></tr>
		<?php }?>
	</table>

<?php
include_once('footer.php');
?>