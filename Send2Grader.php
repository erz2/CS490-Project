<?php
	/*
		Send2Grader.php takes the correct answers and student answers for each question in a given exam and
		sends it to the controller for grading purposes.
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
	
	//acquire list of questions that are on the exam and the weights for those questions
	$result = mysql_query("SELECT ExamData, QuestionWeight FROM Exams WHERE CourseID = '$course' AND ExamTitle = '$ename'");
	$res = mysql_fetch_row($result);
	$questions = array();
	$questions = unserialize($res[0]);
	$weights = array();
	$weights = unserialize($res[1]);
	//acquire students' responses to the exam questions
	$result2 = mysql_query("SELECT ExamResponses FROM Grades WHERE StudentID = '$user' AND CourseID = '$course' AND ExamTitle = '$ename'");
	$res2 = mysql_fetch_row($result2);
	$answer = array();
	$answer = unserialize($res2[0]);
	
	$numquestions = mysql_query("SELECT NumberOfQuestions, Type FROM Exams WHERE CourseID = '$course' AND ExamTitle = '$ename'");
	$num = mysql_fetch_row($numquestions);
	
	if($result && $result2){
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<error>false</error>
					<examtype>' . $num[1] . '</examtype>
					<numquestions>' . $num[0] . '</numquestions>';
		$i = 0;
		while($questions[$i] != null){
			$c = mysql_query("SELECT QuestionIndex, CorrectAnswer, QuestionType FROM Questions WHERE QuestionIndex = '$questions[$i]'");
			$correct = mysql_fetch_array($c);
			$index = $correct['QuestionIndex'];
			//for each question, send the student response and the correct answer with the question weight
			//type is sent so if the question index is a code question, the appropriate JUnit file can be retrieved
			//JUnit files stored by question index #
			$xml = $xml . '<questionindex indexnumber="' . $index . '">
								<type>' . $correct['QuestionType'] . '</type>
								<correct>' . $correct['CorrectAnswer'] . '</correct>
								<studentresponse>' . $answer[$i] . '</studentresponse>
								<weight>' . $weights[$i] . '</weight>
						   </questionindex>';
			$i++;
			mysql_free_result($c);
			mysql_free_result($correct);
		}
		$xml = $xml . '</list>';
		
		echo $xml;
	}
	else{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<error>true</error>
				</list>';
		echo $xml;
	}
	
	mysql_close($con);
?>