<?php
	/*
		ViewExam.php is used to allow professors to view exams that they have created, and allows
		students to go over submitted exams after the end of the exam period. 
	*/

	session_start();
	
	date_default_timezone_set('America/New_York');
	$date = date('Y-m-d H:i:s');

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
		
	$u = mysql_query("SELECT UserType FROM Users WHERE ID = '$user'");
	$uType = mysql_fetch_row($u);

	if($uType[0] == "Professor"){
		//professor's ability to view is un-restricted
		$result = mysql_query("SELECT * FROM Exams WHERE ExamTitle = '$ename' AND CourseID = '$course'");
		$res = mysql_fetch_row($result);
		mysql_free_result($result);
		
		if($res[12] == "graded"){
			$practice = "false";
		}
		else{
			$practice = "true";
		}
		
		if($res){
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<retrieved>true</retrieved>
							<numquestions>' . $res[3] . '</numquestions>
							<usertype>' . $uType[0] . '</usertype>
							<practice>' . $practice . '</practice>';
			//question indexes are stored as a serialized entity in the database
			$questionarray = array();
			$questionarray = unserialize($res[4]);
			$weightarray = array();
			$weightarray = unserialize($res[5]);
			$i = 0;
			//loop to return relevant information for each individual question
			while($questionarray[$i] != null){
				$question = mysql_query("SELECT QuestionIndex, QuestionType, Question, Answer1, Answer2, Answer3, Answer4, CorrectAnswer FROM Questions WHERE QuestionIndex = '$questionarray[$i]'");
				$q = mysql_fetch_row($question);
				mysql_free_result($question);
					
				if($q[1] == "CODE"){
					$xml = $xml . '<questionindex indexnumber="' . $q[0] . '">
									<type>' . $q[1] . '</type>
									<question>' . $q[2] . '</question>
									<answer1>' . $q[3] . '</answer1>
									<answer2></answer2>
									<answer3></answer3>
									<answer4></answer4>
									<correct>' . $q[7] . '</correct>
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
									<correct>' . $q[7] . '</correct>
									<weight>' . $weightarray[$i] . '</weight>
							</questionindex>';
				}
				$i++;			
			}

			echo $xml . '</list>';
		}
		else{
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<retrieved>false</retrieved>
							<usertype>' . $uType[0] . '</usertype>
						</list>';	
		
			echo $xml;
		}
	}
	else{
		$result = mysql_query("SELECT ExamResponses, PointsEarned, PointsPossible FROM Grades WHERE StudentID = '$user' AND CourseID = '$course' AND ExamTitle = '$ename'");
		$res = mysql_fetch_row($result);
	
		$result2 = mysql_query("SELECT ExamData, DueDate, DueTime, Type, NumberOfQuestions, QuestionWeight FROM Exams WHERE CourseID = '$course' AND ExamTitle = '$ename'");
		$res2 = mysql_fetch_row($result2);
	
		//get student's responses
		$responses = array();
		$responses = unserialize($res[0]);
		//get the questions from the exam
		$questionarray = array();
		$questionarray = unserialize($res2[0]);
		//get the weights for the questions
		$weightarray = array();
		$weightarray = unserialize($res2[5]);

		if(($res[0] != null) AND $result2){
			if($res2[3] == "graded"){
				$practice = "false";
			}
			else{
				$practice = "true";
			}
		
			$examend = $res2[1] . ' ' . $res2[2];
			//check if the exam period has ended
			if($date > $examend){
				$xml =  '<?xml version="1.0" encoding="ISO-8859-1"?>
							<list>
								<retrieved>true</retrieved>
								<available>true</available>
								<numquestions>' . $res2[4] . '</numquestions>
								<earned>' . $res[1] . '</earned>
								<possible>' . $res[2] . '</possible>
								<usertype>' . $uType[0] . '</usertype>
								<practice>' . $practice . '</practice>';
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

				echo $xml;
			}
		else{
			$xml =  '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<retrieved>true</retrieved>
							<available>false</available>
							<usertype>' . $uType[0] . '</usertype>
						</list>';	
									
			echo $xml;
			}
		}
		else{
			//error catch
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
					<list>
						<retrieved>false</retrieved>
						<usertype>' . $uType[0] . '</usertype>
					</list>';	
			
			echo $xml;
		}
	}
	
	mysql_close($con);
?>