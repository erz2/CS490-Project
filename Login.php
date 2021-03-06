<?php
	/*
		Login.php is used when users are signing into our website using their NJIT UCID with the password they have
		created for our website. NJIT users using their own UCID passwords should first use the Register function, as
		they will be rejected by this login method. After a first time registration, users use the password they created to
		login using this page. Forgotten passwords can be reset using the Password Reset function.
	*/

	session_start();
	
	//get timestamp for login time
	date_default_timezone_set('America/New_York');
	$date = date('Y-m-d H:i:s');
	
	$con=mysql_connect("sql2.njit.edu", "erz2", "o52kZZLAA") or die(mysql_error());
	$db_found = mysql_select_db("erz2");
	
	if(!$db_found){
		echo "Cannot connect to DB";
		return;
	}
	
	//receiving ucid and password from user via the controller
	if(isset($_POST['ucid'])){
			$user=$_POST['ucid'];
		}
	if(isset($_POST['pass'])){
			$pass=$_POST['pass'];
		}
	
	$result = mysql_query("SELECT * FROM Users WHERE ID = '$user' AND Password = '$pass'");
	$res = mysql_fetch_row($result);
	mysql_free_result($result);
	
	if($res){
		//differentiate between Student and Professor users
		if($res[4] == "Student"){
			$name = $res[2]. ' ' .$res[3];
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<validation>true</validation>
							<professor>false</professor>
							<name>'.$name.'</name>
							<courselist>';
			
			//compile course listing
			$course = mysql_query("SELECT CourseID FROM StudentSchedule WHERE StudentID = '$res[0]'");
			while($row = mysql_fetch_array($course)){
				$c = $row['CourseID'];
				$xml = $xml.'<course title="'.$c.'">';
				$exam = mysql_query("SELECT ExamTitle, StartDate, StartTime, DueDate, DueTime FROM Exams WHERE CourseID = '$c' AND Type = 'graded'");
				
				//compile exam listing for course (as well as status for each exam)
				while($row2 = mysql_fetch_array($exam)){
					$e = $row2['ExamTitle'];
					$examend = $row2['DueDate'] . ' ' . $row2['DueTime'];
					$examstart = $row2['StartDate'] . ' ' . $row2['StartTime'];
					$taken = mysql_query("SELECT ExamResponses FROM Grades WHERE StudentID = '$res[0]' AND CourseID = '$c' AND ExamTitle = '$e'");
					$g = mysql_fetch_row($taken);
					if($g[0] != null){
						//output if the test has already been submitted
						$xml = $xml . '<exam submitted="true">
											<examname>' . $row2['ExamTitle'] .  '</examname>
											<available>false</available>
									  </exam>';
					}
					else{
						//output if the test has expired without being taken
						if($date > $examend){
							$xml = $xml . '<exam submitted="true">
												<examname>' . $row2['ExamTitle'] .  '</examname>
												<available>false</available>
										  </exam>';
						}
						//output test name if a test has been posted but the date hasn't started yet
						else if($date < $examstart){
							$xml = $xml . '<exam submitted="false">
												<examname>' . $row2['ExamTitle'] .  '</examname>
												<available>false</available>
										  </exam>';
						}
						//output if test is cleared to be taken
						else if($date >= $examstart AND $date <= $examend){
							$xml = $xml . '<exam submitted="false">
												<examname>' . $row2['ExamTitle'] .  '</examname>
												<available>true</available>
										  </exam>';
						}
					}
				}
			$xml = $xml.'</course>';
			}
			$xml = $xml.'</courselist></list>';

			echo $xml;
		}
		else{
			$name = $res[2].' '.$res[3];
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<validation>true</validation>
							<professor>true</professor>
							<name>'.$name.'</name>
							<courselist>';
			$course = mysql_query("SELECT CourseID FROM Classes WHERE ProfessorID = '$res[0]'");
			while($row = mysql_fetch_array($course)){
				$c = $row['CourseID'];
				$xml = $xml.'<course title="'.$c.'">';
				//professor has no restriction on exam status
				$exam = mysql_query("SELECT ExamTitle, StartDate, StartTime, DueDate, DueTime FROM Exams WHERE CourseID = '$c' AND Type = 'graded'");
				while($row2 = mysql_fetch_array($exam)){
					$xml = $xml.'<exam>'.$row2['ExamTitle'].'</exam>';
				}
				$xml = $xml.'</course>';
			}
			$xml = $xml.'</courselist></list>';
			echo $xml;
		}
	}else{
	//$res variable doesn't exist if ucid/pass pair not in table
		echo '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<validation>false</validation>
				</list>';
	}
	
	mysql_close($con);
?>