<!DOCTYPE html>
<html lang = 'en'>
<head>
	<title> Log in </title>
	<meta name = 'viewport' content = 'width = device-width, initial-scale = 1.0'>
	<link rel = 'stylesheet' href = '../assets/css/bootstrap-css/bootstrap.min.css'>
</head>

<body class = 'bg-secondary p-3 text-white'>
	<div class = 'container p-5 my-5 border border-3 rounded bg-dark'>
			<h1> Log in </h1>
			<form method = 'post' action = '../actions/login_user.php'>
				<div>
					<label for = 'uname' class = 'form-label'> Username </label>
					<input type = 'text' class = 'form-control' name = 'uname' placeholder = 'Enter username, like apple123' minlength = 1 required>
				</div>

				<div>
					<label for = 'passw' class = 'form-label'> Password </label>
					<input type = 'password' class = 'form-control' name = 'passw' placeholder = 'Enter password' minlength = 8 required>
				</div>

				<br>
				
				<a href = './signup.php' class = 'btn btn-secondary mx-auto d-block'>Sign up if you don't have an account</a>

				<br>

				<button class = 'btn btn-light mx-auto d-block' name = 'login'> Log in </button>
			</form>
	</div>
</body>

</html>