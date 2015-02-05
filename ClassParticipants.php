<?php

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
		
	$result = mysql_query("SELECT ProfessorID From Classes WHERE CourseID = '$course'");
	$res = mysql_fetch_row($result);
	
	if($result){
		$p = mysql_query("SELECT ID, FirstName, LastName, UserType FROM Users WHERE ID = '$res[0]'");
		$professor = mysql_fetch_row($p);
		$xml = '<list>
					<user professor="true">
						<name>' . $professor[1] . ' ' . $professor[2] . '</name>
						<email>' . $professor[0] . '@njit.edu' . '</email>
					</user>';
					
		$result2 = mysql_query("SELECT StudentID FROM StudentSchedule WHERE CourseID = '$course'");
		while($res2 = mysql_fetch_array($result2)){
			$s = mysql_query("SELECT ID, FirstName, LastName, UserType FROM Users WHERE ID = '$res2[0]'");
			$student = mysql_fetch_row($s);
			$xml = $xml . '<user professor="false">
								<name>' . $student[1] . ' ' . $student[2] . '</name>
								<email>' . $student[0] . '@njit.edu' . '</email>
						   </user>';
		}
		$xml = $xml . '</list>';
		echo $xml;
	}
	else{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<list>
					<retrieved>false</retrieved>
				</list>';
		echo $xml;
	}
	
	mysql_close($con);
?>