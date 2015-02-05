<?php
	/*
		ReturnQuestions.php is called when professors are making an exam. The professor will then
		see a list of created questions that he can add to an exam.
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
	
	//find all questions relevant to a specific course
	//LIKE or = to determine scope (course specific vs. section specific)
	$result = mysql_query("SELECT QuestionIndex, QuestionType, Question, Answer1, Answer2, Answer3, Answer4, CorrectAnswer, QuestionCorrectCount, QuestionAnsweredCount FROM Questions WHERE CourseID = '$course'");
	
	if($result){
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<error>false</error>';			
		//return each question with all the relevant data the professor would need to design his exam
		while($row = mysql_fetch_array($result)){
			$qi = $row['QuestionIndex'];
			$xml = $xml. '<questionindex indexnumber="' . $qi . '">
							<type>' . $row['QuestionType'] . '</type>
							<question>' . $row['Question'] . '</question>
							<answer1>' . $row['Answer1'] . '</answer1>
							<answer2>' . $row['Answer2'] . '</answer2>
							<answer3>' . $row['Answer3'] . '</answer3>
							<answer4>' . $row['Answer4'] . '</answer4>
							<correct>' . $row['CorrectAnswer'] . '</correct>
							<correctcount>' . $row['QuestionCorrectCount'] . '</correctcount>
							<takencount>' . $row['QuestionAnsweredCount'] . '</takencount>
						</questionindex>';
		}
		$xml = $xml . '</list>';

		echo $xml;
	}
	else{
		//handler for if a question bank is empty
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<error>true</error>
				</list>';
				
		echo $xml;
	}
	
	mysql_close($con);
?>