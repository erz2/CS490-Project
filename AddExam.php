<?php
	/*
		AddExam.php submits an exam into the Exams table in the database. When an exam is created, if the
		exam is marked as "graded," all students in the class will be inserted into the grade table with 
		a default grade of 0 points earned. 
	*/

	session_start();
	
	$con=mysql_connect("sql2.njit.edu", "erz2", "o52kZZLAA") or die(mysql_error());
	$db_found = mysql_select_db("erz2");
	
	if(!$db_found){
		echo "Cannot connect to DB";
		return;
	}
	
	if(isset($_POST['ename'])){
			$examtitle=$_POST['ename'];
		}
	if(isset($_POST['sdate'])){
			$startdate=$_POST['sdate'];
		}
	if(isset($_POST['stime'])){
			$starttime=$_POST['stime'];
		}
	if(isset($_POST['edate'])){
			$duedate=$_POST['edate'];
		}
	if(isset($_POST['etime'])){
			$duetime=$_POST['etime'];
		}
	if(isset($_POST['tlimit'])){
			$timelimit=$_POST['tlimit'];
		}
	if(isset($_POST['course'])){
			$course=$_POST['course'];
		}
	if(isset($_POST['type'])){
			$type=$_POST['type'];
		}
	
	//question indexes are sent in the form "q1=x&q2=y&q3=z...."
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
	//serialize the question indexes to place into once cell in database
	$questions = serialize($data);
	//get the exam specific weights for the questions
	$data2 = array();
	$j = 0;
	$totalPoints = 0;
	while(true){
		if(isset($_POST['w'.(string)$j.''])){
			$data2[$j] = $_POST['w'.(string)$j.''];
			$totalPoints = $totalPoints + (int)$data2[$j];
			$j++;
		}
		else{
			break;
		}
	}
	$weights = serialize($data2);
	//the amount of while loop iterations = number of questons in the exam
	$numquestions = $i;
	
	//questions marked for deletion will be processed here
	$data3 = array();
	$k = 0;
	while(true){
		if(isset($_POST['d'.(string)$k.''])){
			$data3[$k] = $_POST['d'.(string)$k.''];
			$k++;
		}
		else{
			break;
		}
	}
	$d = 0;
	while($data3[$d] != null){
		$deleteIndex = $data3[$d];
		$delete = mysql_query("DELETE FROM Questions WHERE QuestionIndex = '$deleteIndex'");
		$d++;
	}
	
	$result = mysql_query("INSERT INTO Exams VALUES(null, '$examtitle', '$course', '$numquestions', '$questions', '$weights', '$totalPoints', '$startdate', '$starttime', '$duedate', '$duetime', '$timelimit', '$type')");
	
	if($result){
		//if the exam is graded, add all students in the class to the Grades table with a default grade of 0
		if($type == "graded"){
			$result2 = mysql_query("SELECT StudentID FROM StudentSchedule WHERE CourseID = '$course'");
			while($res2 = mysql_fetch_array($result2)){
				$si = $res2['StudentID'];
				mysql_query("INSERT INTO Grades VALUES('$si', '$course', '$examtitle', '0', '$totalPoints', null)");
			}
		}
		//result value is true or false (mysql_query for INSERT returns a boolean value)
		//if result is true, echo back that the query ran successfully and the exam has been added to the table
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