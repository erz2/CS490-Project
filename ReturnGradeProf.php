<?php
	/*
		ReturnGradeProf.php is like ReturnGrade.php without the restriction on the time the checking is 
		taking place. 
	*/
	
	session_start();
	
	$con=mysql_connect("sql2.njit.edu", "erz2", "o52kZZLAA") or die(mysql_error());
	$db_found = mysql_select_db("erz2");
	
	date_default_timezone_set('America/New_York');
	$date = date('Y-m-d H:i:s');
	
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
	
	//return all the grades for students who have submitted their exams
	$result = mysql_query("SELECT StudentID, PointsEarned, PointsPossible FROM Grades WHERE CourseID = '$course' AND ExamTitle = '$ename'");
	
	if($result){
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<retrieved>true</retrieved>';
		
		$count = 0;
		while($row = mysql_fetch_array($result)){
			$id = $row['StudentID'];
			$xml = $xml . '<name data="' . $id . '">	
							<earned>' . $row['PointsEarned'] . '</earned>
							<possible>' . $row['PointsPossible'] . '</possible>
						   </name>';
			$count++;
		}
		$xml = $xml . '<gradecount>' . $count . '</gradecount>';

		//if the exam period has ended, we can provide metrics for the exam
		$e = mysql_query("SELECT DueDate, DueTime FROM Exams WHERE CourseID = '$course' AND ExamTitle = '$ename'");
		$end = mysql_fetch_row($e);
		$examenddate = $end[0] . ' ' . $end[1];
		if($date > $examenddate){
			$result2 = mysql_query("SELECT MAX(PointsEarned) AS HighScore, MIN(PointsEarned) AS LowScore, AVG(PointsEarned) AS AverageScore FROM Grades WHERE CourseID = '$course' AND ExamTitle = '$ename'");
			$metrics = mysql_fetch_array($result2);
			
			//get grades in order from greatest to least
			$result3 = mysql_query("SELECT PointsEarned FROM Grades WHERE CourseID = '$course' AND ExamTitle = '$ename' ORDER BY PointsEarned DESC");
			$median = array();
			$i = 0;
			while($m = mysql_fetch_array($result3)){
				$median[$i] = $m[0];
				$i++;
			}
			if($count % 2 == 0){
				$low = $count/2 - 1;
				$high = $count/2;
				$middle = ($median[$high] + $median[$low])/2;
			}
			else{
				$mid = ceil($count/2) - 1;
				$middle = $median[$mid];
			}
			
			//metrics are only relevant at the conclusion of the test period
			$xml = $xml . '<metrics provided="true">
								<max>' . $metrics[0] . '</max>
								<min>' . $metrics[1] . '</min>
								<average>' . $metrics[2] . '</average>
								<median>' . $middle . '</median>
							</metrics>';
		}
		else{
			$xml = $xml . '<metrics provided="false">
								<max></max>
								<min></min>
								<average></average>
								<median></median>
							</metrics>';
		}
		
		$xml = $xml . '</list>';

		echo $xml;
	}
	else{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<retrieved>false</retrieved>
				</list>';	
		echo $xml;
	}
	
	mysql_close($con);
?>