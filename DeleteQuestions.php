<?php
	/*
		DeleteQuestion.php is used by a professor to remove questions from their question bank.
	*/

	session_start();
	
	$con=mysql_connect("sql2.njit.edu", "erz2", "o52kZZLAA") or die(mysql_error());
	$db_found = mysql_select_db("erz2");
	
	if(!$db_found){
		echo "Cannot connect to DB";
		return;
	}
	
	//questions marked for deletion will be processed here
	$data = array();
	$i = 0;
	while(true){
		if(isset($_POST['d'.(string)$i.''])){
			$data[$i] = $_POST['d'.(string)$i.''];
			$i++;
		}
		else{
			break;
		}
	}
	$d = 0;
	while($data[$d] != null){
		$deleteIndex = $data[$d];
		$delete = mysql_query("DELETE FROM Questions WHERE QuestionIndex = '$deleteIndex'");
		$d++;
	}
	
	$xml = '<list>
				<deleted>true</deleted>
			</list>';
	echo $xml;
	
	mysql_close($con);
?>