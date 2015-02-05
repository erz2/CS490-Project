<?php
	/*
		RequestExamHistory.php will return the list of all created exams (graded and practice) for professors. 
		For students, it will show taken tests at the conclusion of the exam period as well as any
		practice exams that have been created.
	*/
	
	session_start();
	
	$con=mysql_connect("sql2.njit.edu", "erz2", "o52kZZLAA") or die(mysql_error());
	$db_found = mysql_select_db("erz2");
	
	//get current timestamp
	date_default_timezone_set('America/New_York');
	$date = date('Y-m-d H:i:s');
	
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
	
	$result = mysql_query("SELECT UserType FROM Users WHERE ID = '$user'");
	$res = mysql_fetch_row($result);
	
	if($res[0] == "Student"){
		$result2 = mysql_query("SELECT ExamTitle, Type, DueDate, DueTime FROM Exams WHERE CourseID = '$course'");
	
		if($result2){
			$xml = '<list>
						<found>true</found>';
			while($res2 = mysql_fetch_array($result2)){
				$title = $res2['ExamTitle'];
				$type = $res2['Type'];
				$examend = $res2['DueDate'] . ' ' . $res2['DueTime'];
				
				//check if exam is graded or practice
				if($type == "graded"){
					//check if exam period has ended
					if($date > $examend){
						$xml = $xml . '<exam title="' . $title . '">
										<practice>false</practice>
										<course>' . $course . '</course>
									</exam>';
					}
					else{}
				}
				else{
					$xml = $xml . '<exam title="' . $title . '">
										<practice>true</practice>
										<course>' . $course . '</course>
									</exam>';
				}
			}
			$xml = $xml . '</list>';

			echo $xml;
		}
		else{
			//error catch
			$xml = '<list>
						<found>false</found>
					</list>';	
			echo $xml;
		}
	}
	//professor can view any exam, without having to consider the date
	else{
		$result2 = mysql_query("SELECT ExamTitle, Type FROM Exams WHERE CourseID = '$course'");
	
		if($result2){
			$xml = '<list>
						<found>true</found>';
			while($res2 = mysql_fetch_array($result2)){
				$title = $res2['ExamTitle'];
				$type = $res2['Type'];
				//differentiate between graded exams and practice exams
				if($type == "graded"){
					$xml = $xml . '<exam title="' . $title . '">
										<practice>false</practice>
										<course>' . $course . '</course>
									</exam>';
				}
				else{
					$xml = $xml . '<exam title="' . $title . '">
										<practice>true</practice>
										<course>' . $course . '</course>
									</exam>';
				}
			}
			$xml = $xml . '</list>';
			
			echo $xml;
		}
		else{
			//error catch
			$xml = '<list>
						<found>false</found>
					</list>';	
			echo $xml;
		}
	}
?>