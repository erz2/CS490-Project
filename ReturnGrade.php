<?php
	/*
		ReturnGrade.php returns the grade for one specific exam. If the exam period has not expired yet, 
		the <available> tag will be set to false and thus will not be displayed.
	*/

	session_start();
	
	$con=mysql_connect("sql2.njit.edu", "erz2", "o52kZZLAA") or die(mysql_error());
	$db_found = mysql_select_db("erz2");
	
	//get timestamp for current access attempt
	//MySQL time and date formats are Date: 'YYYY-mm-dd' and Time: 'HH:mm:ss'
	//PHP equivalency is 'Y-m-d' and 'H:i:s'
	date_default_timezone_set('America/New_York');
	$date = time();
	$date = date('Y-m-d H:i:s');
	
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
	
	//this function only returns one specific grade
	$result = mysql_query("SELECT PointsEarned, PointsPossible FROM Grades WHERE StudentID = '$user' AND CourseID = '$course' AND ExamTitle = '$ename'");
	$res = mysql_fetch_row($result);
	mysql_free_result($result);
	
	if($result){
		$d = mysql_query("SELECT DueDate, DueTime FROM Exams WHERE CourseID = '$course' AND ExamTitle = '$ename'");
		$row = mysql_fetch_row($d);
		//concat the due date and due time
		$duedate = $row[0] . ' ' . $row[1];
		if($date > $duedate){
			//if the duedate has passed, it is ok to release the grades
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<exists>true</exists>
					<available>true</available>
					<earned>' . $res[0] . '</earned>
					<possible>' . $res[1] . '</possible>
				</list>';
			echo $xml;
			}
		else{
			//if the duedate has not passed, do not show the grade
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<exists>true</exists>
					<available>false</available>
				</list>';
			echo $xml;
		}
	}
	else{
		//exam hasn't been submitted yet (should never occur/exists as an error catch)
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<exists>false</exists>
				</list>';
		
		echo $xml;
	}
	
	mysql_close($con);
?>