<?php
	/*
		PasswordReset.php is used when users forget their created password. They must provide their UCID and password
		again to be re-authenticated by the NJIT system. After re-authentication is successful, they will be able to 
		create a new password for use on our website. The user will then be directed to login again.
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
	if(isset($_POST['cbpass'])){
			$pass=$_POST['cbpass'];
		}
	
	//find user in the database
	$result = mysql_query("SELECT * FROM Users WHERE ID = '$user'");
	$res = mysql_fetch_row($result);
	mysql_free_result($result);
	
	if($res){
		//if the default password is still set, they must use the register function
		if($res[1] == 'njitstudent'){
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<newuser>true</newuser>
							<updated>false</updated>
						</list>';
			echo $xml;
		}
		//if the user has already registered, allow them to change their password
		else{
			mysql_query("UPDATE Users SET Password = '$pass' WHERE ID = '$user'");
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
						<list>
							<newuser>false</newuser>
							<updated>true</updated>
						</list>';
			echo $xml;
		}
	}
	//if user doesn't exist in the database, we can disregard them
	else{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
					<list>
						<newuser>false</newuser>
						<updated>false</updated>
					<list>';
		echo $xml;
	}
	
	mysql_close($con);
?>