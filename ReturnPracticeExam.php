<?php
	/*
		ReturnPracticeExam.php works similarly to ReturnExam.php without the time checking. Also, as there
		is no default entry in the Grades table, a temporary value is placed there. As this is a practice exam
		that can be retaken at will, the results will be wiped from the table after the exam has been graded
		and the results have been returned
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
	if(isset($_POST['ename'])){
			$examtitle=$_POST['ename'];
		}
	if(isset($_POST['course'])){
			$course=$_POST['course'];
		}
	
	$result = mysql_query("SELECT * FROM Exams WHERE ExamTitle = '$examtitle' AND CourseID = '$course'");
	$res = mysql_fetch_row($result);
	mysql_free_result($result);
	
	if($res){
		$result2 = mysql_query("INSERT INTO Grades VALUES('$user', '$course', '$examtitle', '0', '$res[6]', null)");	
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
					<list>
						<exists>true</exists>
						<available>true</available>
						<numquestions>' . $res[3] . '</numquestions>';
		//question indexes are stored as a serialized entity in the database
		$questionarray = array();
		$questionarray = unserialize($res[4]);
		$weightarray = array();
		$weightarray = unserialize($res[5]);
		$i = 0;
		//loop to return relevant information for each individual question
		while($questionarray[$i] != null){
			$question = mysql_query("SELECT QuestionIndex, QuestionType, Question, Answer1, Answer2, Answer3, Answer4 FROM Questions WHERE QuestionIndex = '$questionarray[$i]'");
			$q = mysql_fetch_row($question);
			mysql_free_result($question);
				
			//coding questions have the unit test stored in Answer1 field. For security purposes, it is not sent back
			//the unit tests will be sent to the grader only
			if($q[1] == "CODE"){
				$xml = $xml . '<questionindex indexnumber="' . $q[0] . '">
								<type>' . $q[1] . '</type>
								<question>' . $q[2] . '</question>
								<answer1>' . $q[3] . '</answer1>
								<answer2></answer2>
								<answer3></answer3>
								<answer4></answer4>
								<weight>' . $weightarray[$i] . '</weight>
						</questionindex>';
			}
			else{
				$xml = $xml . '<questionindex indexnumber="' . $q[0] . '">
								<type>' . $q[1] . '</type>
								<question>' . $q[2] . '</question>
								<answer1>' . $q[3] . '</answer1>
								<answer2>' . $q[4] . '</answer2>
								<answer3>' . $q[5] . '</answer3>
								<answer4>' . $q[6] . '</answer4>
								<weight>' . $weightarray[$i] . '</weight>
						</questionindex>';
			}
			$i++;
		}
		$xml = $xml . '</list>';
		
		echo $xml;
	}
	else{
		//exam doesn't exist/used for error catching
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<exists>false</exists>
						</list>';
		echo $xml;
	}
	
	mysql_close($con);
?>