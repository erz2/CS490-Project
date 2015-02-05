<?php
	/*
		Register.php is used for first time NJIT users. This page is only reached if the user has passed
		the NJIT authentication test done by the controller. The database will be updated with the created
		password from the user. The user will then be directed to use the Login function to actually
		login the website.
	*/
	
	session_start();
	
	$con=mysql_connect("sql2.njit.edu", "erz2", "o52kZZLAA") or die(mysql_error());
	$db_found = mysql_select_db("erz2");
	
	if(!$db_found){
		echo "Cannot connect to DB";
		return;
	}
	
	//receiving ucid and new password from user via the controller
	//users who reach this point must be valid NJIT students, as the controller will reject non-NJIT users
	if(isset($_POST['ucid'])){
			$user=$_POST['ucid'];
		}
	if(isset($_POST['cbpass'])){
			$pass=$_POST['cbpass'];
		}
	
	//make sure the user is in the database
	//valid users not in database have no classes, and thus have no reason to be granted entrance to site
	$result = mysql_query("SELECT * FROM Users WHERE ID = '$user'");
	$res = mysql_fetch_row($result);
	mysql_free_result($result);
	
	//for authorized NJIT students who are in our database, set their new password to be the password they entered on the Registration screen
	if($res){
		//makes sure user is "unactivated" (has the default 'njitstudent' password)
		if($res[1] == 'njitstudent'){
			mysql_query("UPDATE Users SET Password = '$pass' WHERE ID = '$user' AND Password = 'njitstudent'");
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<activated>true</activated>
							<defaultpassword>true</defaultpassword>
						</list>';
			echo $xml;
		}
		//if the user has already reset their password and activated their account, they must use the log-in function, not the registration function
		else{
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<activated>true</activated>
							<defaultpassword>false</defaultpassword>
						</list>';
			echo $xml;
		}
	}
	//for authorized students not in the database
	else{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<activated>false</activated>
							<defaultpassword>false</defaultpassword>
						</list>';
			echo $xml;
	}
	
	mysql_close($con);
?>