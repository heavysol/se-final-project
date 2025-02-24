<!DOCTYPE html>
<html lang = 'en'>
<head>
	<title> Sign up </title>
	<meta name = 'viewport' content = 'width = device-width, initial-scale = 1.0'>
	<link rel = 'stylesheet' href = '../assets/css/bootstrap-css/bootstrap.min.css'>
</head>

<body class = 'bg-secondary p-3 text-white'>
	<div class = 'container p-5 my-5 border border-3 rounded bg-dark'>
			<h1> Sign up </h1>
			<form method = 'post' action = '../actions/register_user.php'>
				<div>
					<label for = 'uname' class = 'form-label'> Username </label>
					<input type = 'text' class = 'form-control' name = 'uname' placeholder = 'Enter username, like apple123' minlength = 1  required>
				</div>

				<div>
					<label for = 'passw' class = 'form-label'> Password </label>
					<input type = 'password' class = 'form-control' name = 'passw' placeholder = 'Enter password' minlength = 8 required>
				</div>

				<div>
					<label for = 'passw-con' class = 'form-label'> Confirm password </label>
					<input type = 'password' class = 'form-control' name = 'passw-con' placeholder = 'Confirm password' minlength = 8 required>
				</div>

				<div>
					<label for = 'email' class = 'form-label'> Email </label>
					<input type = 'email' class = 'form-control' name = 'email' placeholder = 'Enter email address, like jj@gmail.com' required>
				</div> 

				<br>
				
				<a href = './login.php' class = 'btn btn-secondary mx-auto d-block'>Log in if you already have an account</a>

				<br>

				<button class = 'btn btn-light mx-auto d-block' name = 'signup'> Sign up </button>
			</form>
	</div>
</body>

</html>