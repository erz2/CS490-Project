<?php
	/*
		SubmitExam.php is used when a student submits their exam. The Grades table is appended to store
		the student's responses for the Review Exam functionality. 
	*/

	session_start();
	
	$con=mysql_connect("sql2.njit.edu", "erz2", "o52kZZLAA") or die(mysql_error());
	$db_found = mysql_select_db("erz2");

	if(!$db_found){
		echo "Cannot connect to DB";
		return;
	}
	
	if(isset($_POST['ucid'])){
			$user=$_POST['ucid'];
		}
	if(isset($_POST['course'])){
			$course=$_POST['course'];
		}
	if(isset($_POST['ename'])){
			$ename=$_POST['ename'];
		}
	//store student responses in serialized array
	$data = array();
	$i = 0;
	while(true){
		if(isset($_POST['a'.(string)$i.''])){
			$data[$i] = $_POST['a'.(string)$i.''];
			$i++;
		}
		else{
			break;
		}
	}
	//answers are serialized and stored to be recalled later for "Review Exam" feature
	$answers = serialize($data);
	
	$result = mysql_query("SELECT ExamResponses FROM Grades WHERE StudentID = '$user' AND CourseID = '$course' AND ExamTitle = '$ename'");
	$res = mysql_fetch_row($result);
	
	if($res[0] == null){
		//grade is set to null for now
		$result2 = mysql_query("UPDATE Grades SET ExamResponses = '$answers' WHERE StudentID = '$user' AND CourseID = '$course' AND ExamTitle = '$ename'");
		
		if($result2){
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
					<list>
						<received>true</received>
						<again>false</again>
					</list>';
			echo $xml;
		}
		else{
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
					<list>
						<received>false</received>
						<again>false</again>
					</list>';
			echo $xml;
		}
	}
	else{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
					<list>
						<received>false</received>
						<again>true</again>
					</list>';
		echo $xml;
	}
	
	mysql_close($con);
?>