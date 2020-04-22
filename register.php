<?php

$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {

	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
	
	exit('Please complete the registration form!');
}

if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
	
	exit('Please complete the registration form');
}
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	exit('Email is not valid!');
}
if (preg_match('/[A-Za-z0-9]+/', $_POST['username']) == 0) {
    exit('Username is not valid!');
}
if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
	exit('Password must be between 5 and 20 characters long!');
}

if ($stmt = $con->prepare('SELECT id, password FROM account WHERE username = ?')) {
	
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	$stmt->store_result();
	
	if ($stmt->num_rows > 0) {
		
		echo 'Username exists, please choose another!';
	} else {
	
                if ($stmt = $con->prepare('INSERT INTO account (username, password, email, activation_code) VALUES (?, ?, ?, ?)')) {
                      
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $uniqid = uniqid();
                        $stmt->bind_param('ssss', $_POST['username'], $password, $_POST['email'], $uniqid);

                        $stmt->execute();
                        $from    = 'noreply@yourdomain.com';
                        $subject = 'Account Activation Required';
                        $headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
                        $activate_link = 'http://yourdomain.com/phplogin/activate.php?email=' . $_POST['email'] . '&code=' . $uniqid;
                        $message = '<p>Please click the following link to activate your account: <a href="' . $activate_link . '">' . $activate_link . '</a></p>';
                        mail($_POST['email'], $subject, $message, $headers);
                        echo 'Please check your email to activate your account!'; //Bernardo: Note to self, must fix domain when uploading to webservice
                } else {
                        // Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
                        echo 'Could not prepare statement!';
                }
	}
	$stmt->close();
} else {
	// Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
	echo 'Something went wrong with the SQL connection, please try again!'; //Bernardo: note to self, fix SQL details (pass and user)
        
}
$con->close();
?>
<html>
<head>
		<meta charset="utf-8">
		<title>Home Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body class="loggedin">
		<nav class="navtop">
			<div>
				<h1>Website Title</h1>
                               
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
                                <a href="home.php"><i class="fas fa-user-circle"></i>Home</a>
                            
			</div>
		</nav>
        </body>
</html>