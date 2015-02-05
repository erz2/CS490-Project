<?php
	/*
		DeleteExam.php is used by professors to delete exams that have been created.
	*/

	session_start();

	$con=mysql_connect("sql2.njit.edu", "erz2", "o52kZZLAA") or die(mysql_error());
	$db_found = mysql_select_db("erz2");
	
	if(!$db_found){
		echo "Cannot connect to DB";
		return;
	}
	
	if(isset($_POST['course'])){
			$course=$_POST['course'];
		}
	if(isset($_POST['ename'])){
			$ename=$_POST['ename'];
		}
	
	$result = mysql_query("DELETE FROM Exams WHERE CourseID = '$course' AND ExamTitle = '$ename'");
	
	//if the exam is deleted, the grades for the exam are irrelevant
	$students = mysql_query("SELECT StudentID From StudentSchedule WHERE CourseID = '$course'");
	while($row = mysql_fetch_array($students)){
		$id = $row['StudentID'];
		mysql_query("DELETE FROM Grades WHERE StudentID = '$id' AND CourseID = '$course' AND ExamTitle = '$ename'");
	}
	
	if($result){
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<deleted>true</deleted>
				</list>';	
		echo $xml;
	}
	else{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<deleted>false</deleted>
				</list>';	
		echo $xml;
	}

	mysql_close($con);
?>