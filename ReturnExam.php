<?php
	/*
		ReturnExam.php is used when a student is requesting an exam. The page will generate a timestamp
		to see if the request time is within the exam time period. Also, if the time remaining is less
		than the time limit, the returned time limit will be altered to display exactly how much time
		is left in the exam period. 
	*/

	session_start();
	
	$con=mysql_connect("sql2.njit.edu", "erz2", "o52kZZLAA") or die(mysql_error());
	$db_found = mysql_select_db("erz2");
	
	//get current timestamp
	date_default_timezone_set('America/New_York');
	$date = date('Y-m-d H:i:s');
	$currentdate = date('Y-m-d', strtotime($date));
	$currenttime = date('H:i:s', strtotime($date));

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
		$result2 = mysql_query("UPDATE Grades SET ExamResponses = '' WHERE StudentID = '$user' AND CourseID = '$course' AND ExamTitle = '$examtitle'");
		
		$examstart = $res[7] . ' ' . $res[8];
		$examend = $res[9] . ' ' . $res[10];	
		//to take an exam, the current date and time must be between the start and end dates and times
		if($date > $examstart AND $date < $examend){
			if($currentdate == $res[9]){
				$compareTime = strtotime($res[10]) - strtotime($currenttime);
				if($compareTime < strtotime($res[11])){
					//recalculate the time limit if the time left until the exam ends is less than the time limit
					if($compareTime >= strtotime('01:00:00')){
						$timelimit = '01:' . date('i:s', $compareTime);
					}
					else{
						$timelimit = '00:' . date('i:s', $compareTime);
					}
				}
				else{
					$timelimit = $res[11];
				}
			}
			else{
				$timelimit = $res[11];
			}
			
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<exists>true</exists>
							<available>true</available>
							<numquestions>' . $res[3] . '</numquestions>
							<examstartdate>' . $res[7] . '</examstartdate>
							<examstarttime>' . $res[8] . '</examstarttime>
							<examduedate>' . $res[9] . '</examduedate>
							<examduetime>' . $res[10] . '</examduetime>
							<examlength>' . $timelimit . '</examlength>';
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

			echo $xml . '</list>';
		}
		else{
			//exam period hasn't begun yet
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<exists>true</exists>
							<available>false</available>
						</list>';	
			echo $xml;
		}
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