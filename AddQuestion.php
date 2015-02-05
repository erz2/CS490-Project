<?php
	/*
		AddQuestion.php is used by professors to add questions to their question bank. The question banks 
		are simply views from the Questions table in the database. The added questions keep a record of 
		what course the professor is adding them for, so when the professor is creating an exam, he will
		get the questions relevant to his course. At this time, we have chosen to only allow the professor
		to see questions he himself has added and no questions from other sections of the same course taught
		by different professors. 
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
	if(isset($_POST['type'])){
			$type=$_POST['type'];
		}
	if(isset($_POST['question'])){
			$question=$_POST['question'];
		}
	if(isset($_POST['answer1'])){
			$a1=$_POST['answer1'];
		}
	if(isset($_POST['answer2'])){
			$a2=$_POST['answer2'];
		}
	if(isset($_POST['answer3'])){
			$a3=$_POST['answer3'];
		}
	if(isset($_POST['answer4'])){
			$a4=$_POST['answer4'];
		}
	if(isset($_POST['correctanswer'])){
			$correct=$_POST['correctanswer'];
		}
		
	//special POST variables purely for use with Code questions
	if(isset($_POST['snippet'])){
			$snippet=$_POST['snippet'];
		}
	$input = array();
	$i = 1;
	while(true){
		if(isset($_POST['i'.(string)$i.''])){
			$input[$i] = $_POST['i'.(string)$i.''];
			$i++;
		}
		else{
			break;
		}
	}
	$output = array();
	$j = 1;
	while(true){
		if(isset($_POST['o'.(string)$j.''])){
			$output[$j] = $_POST['o'.(string)$j.''];
			$j++;
		}
		else{
			break;
		}
	}
		
	$inputarray = serialize($input);
	$outputarray = serialize($output);	
		
	//for CODE questions, the correct answer is a file path to the JUnit test case stored by the controller
	//adding CODE questions requires 2 calls to AddQuestion
	//on the first call, add the question with a dummy variable stored as the correct answer
	//send auto-generated question index back to the controller so that he can name the JUnit file with the question index
	//second call to AddQuestion overwrites the dummy and adds the real file path
	if(isset($_POST['questionindex'])){
		$qi=$_POST['questionindex'];
		if($qi == "null"){
			//add question to question bank using relevant data
			$result1 = mysql_query("INSERT INTO Questions VALUES(null, '$course', '$type', '$question', '$snippet', '', '$inputarray', '$outputarray', '$correct', 0, 0)");
		}
		else{
			//set the right file path
			$result2 = mysql_query("UPDATE Questions SET CorrectAnswer = '$correct' WHERE QuestionIndex = '$qi'");
		}
	}
	else{
		//add question to question bank using relevant data
		$result1 = mysql_query("INSERT INTO Questions VALUES(null, '$course', '$type', '$question', '$a1', '$a2', '$a3', '$a4', '$correct', 0, 0)");
	}

	if($result1){
		//result value is true or false (mysql_query for INSERT returns a boolean value)
		//if result is true, echo back that the query ran successfully and the question has been added to the table
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<inserted>true</inserted>';
		
		//if this is the first call for a CODE question, send the index back
		if($type = "CODE" AND $qi == "null"){
			$result3 = mysql_query("SELECT QuestionIndex From Questions WHERE Question = '$question' AND Answer1 = '$snippet' AND Answer3 = '$inputarray' AND Answer4 = '$outputarray'");
			$res3 = mysql_fetch_row($result3);
			if($res3){
				$xml = $xml . '<questionindex>' . $res3[0] . '</questionindex>';
			}
		}
		$xml = $xml . '</list>';
		
		echo $xml;
	}
	else if($result2){
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<inserted>true</inserted>
				</list>';
		echo $xml;
	}
	else{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<inserted>false</inserted>
				</list>';		
		echo $xml;
	}
	
	mysql_close($con);
?>