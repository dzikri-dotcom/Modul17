<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-primary d-flex justify-content-center align-items-center" style="height: 100vh;">


    <div class="card shadow" style="width: 400px; border-radius: 15px;">
        <div class="card-body text-center p-5">

            <div class="mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="#007bff" viewBox="0 0 100 100">
                    <polygon points="50,5 90,25 90,75 50,95 10,75 10,25"/>
                </svg>
            </div>


            <h1 class="h5 mb-4 fw-bold">Employee Data Master</h1>

            <form action="{{ route('login.authenticate') }}" method="POST">
                @csrf
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="email" placeholder="Enter Your Email" required>
                    <label for="email">Enter Your Email</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control" id="password" placeholder="Enter Your Password" required>
                    <label for="password">Enter Your Password</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Log In
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
