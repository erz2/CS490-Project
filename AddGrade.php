<?php
	/*
		AddGrade.php is used to store the grade sent back by the controller after he has graded the exam.
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
	if(isset($_POST['grade'])){
			$grade=$_POST['grade'];
		}
	//0 is answered wrong / 1 if answered right
	$data = array();
	$i = 0;
	while(true){
		if(isset($_POST['q'.(string)$i.''])){
			$data[$i] = $_POST['q'.(string)$i.''];
			$i++;
		}
		else{
			break;
		}
	}
	
	$q = mysql_query("SELECT ExamData FROM Exams WHERE CourseID = '$course' AND ExamTitle = '$ename'");
	$quest = mysql_fetch_row($q);
	$questions = array();
	$questions = unserialize($quest[0]);
	
	//used to update question difficulty %
	$j = 0;
	while($questions[$j] != null){
		$index = $questions[$j];
		if($data[$j] == "1"){
			mysql_query("UPDATE Questions SET QuestionCorrectCount = QuestionCorrectCount + 1 WHERE QuestionIndex = '$index'");
			mysql_query("UPDATE Questions SET QuestionAnsweredCount = QuestionAnsweredCount + 1 WHERE QuestionIndex = '$index'");
		}
		else if($data[$j] == "0"){
			mysql_query("UPDATE Questions SET QuestionAnsweredCount = QuestionAnsweredCount + 1 WHERE QuestionIndex = '$index'");
		}
		$j++;
	}
	
	//add grade for a specific exam
	//happens at submittal, but grade cannot be viewed until the due date and due time have been reached
	$result = mysql_query("UPDATE Grades SET PointsEarned = '$grade' WHERE StudentID = '$user' AND CourseID = '$course' AND ExamTitle = '$ename'");
	
	$result2 = mysql_query("SELECT Type FROM Exams WHERE CourseID = '$course' AND ExamTitle = '$ename'");
	$res2 = mysql_fetch_row($result2);
	
	if($res2[0] == "graded"){
		$practice = "false";
	}
	else{
		$practice = "true";
	}
	
	if($result){
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<inserted>true</inserted>
					<practice>' . $practice . '</practice>
				</list>';
		echo $xml;
	}
	else{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<inserted>false</inserted>
					<practice>' . $practice . '</practice>
				</list>';
		echo $xml;
	}
	
	mysql_close($con);
?>