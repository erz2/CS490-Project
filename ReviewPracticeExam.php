<?php
	/*
		ReviewPracticeExam.php is used for students who want to go over their exam. The function will display
		the questions from the exam, the correct answer, and the answer the student gave. The function will
		also delete the student responses after sending them, allowing the student to try the test again.
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

	$result = mysql_query("SELECT ExamResponses FROM Grades WHERE StudentID = '$user' AND CourseID = '$course' AND ExamTitle = '$ename'");
	$res = mysql_fetch_row($result);
	
	$result2 = mysql_query("SELECT ExamData, QuestionWeight, TotalPoints FROM Exams WHERE CourseID = '$course' AND ExamTitle = '$ename'");
	$res2 = mysql_fetch_row($result2);
	
	//get student's responses
	$responses = array();
	$responses = unserialize($res[0]);
	//get the questions from the exam
	$questionarray = array();
	$questionarray = unserialize($res2[0]);
	//get the question weights
	$weightarray = array();
	$weightarray = unserialize($res2[1]);
	
	$u = mysql_query("SELECT UserType FROM Users WHERE ID = '$user'");
	$uType = mysql_fetch_row($u);
	
	if($result AND $result2){
		$xml =  '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<retrieved>true</retrieved>
					<practice>true</practice>
					<earned>' . $grade . '</earned>
					<possible>' . $res2[2] . '</possible>
					<course>' . $course . '</course>
					<usertype>' . $uType[0] . '</usertype>';		
		$i = 0;
		while($questionarray[$i] != null){
			//get the relevant information for the questions from the exam
			$result3 = mysql_query("SELECT QuestionIndex, QuestionType, Question, Answer1, Answer2, Answer3, Answer4, CorrectAnswer FROM Questions WHERE QuestionIndex = '$questionarray[$i]'");
			$examdata = mysql_fetch_row($result3);
			//similar to ReturnExam, with the student's answer attached
			$xml = $xml . 	'<questionindex index="' . $examdata[0] . '">
								<type>' . $examdata[1] . '</type>
								<question>' . $examdata[2] . '</question>
								<answer1>' . $examdata[3] . '</answer1>
								<answer2>' . $examdata[4] . '</answer2>
								<answer3>' . $examdata[5] . '</answer3>
								<answer4>' . $examdata[6] . '</answer4>
								<correct>' . $examdata[7] . '</correct>
								<weight>' . $weightarray[$i] . '</weight>
								<studentresponse>' . $responses[$i] . '</studentresponse>
							</questionindex>';
			$i++;
			mysql_free_result($result3);
			}
		$xml = $xml . '</list>';
		
		//delete student responses from the Grades table
		mysql_query("DELETE FROM Grades WHERE StudentID = '$user' AND CourseID = '$course' AND ExamTitle = '$ename'");
		
		echo $xml;
	}
	else{
		//error catch
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<retrieved>false</retrieved>
				</list>';
					
		echo $xml;
	}
	
	mysql_close($con);
?>