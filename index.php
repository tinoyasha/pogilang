<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* Global body styles */
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f7f7f7;
        }

        /* Center container */
        .container {
            max-width: 600px;
        }

        /* Card Style */
        .card {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            border: none;
            background: #fff;
        }

        .card-body {
            padding: 30px;
        }

        /* Tab navigation */
        .nav-tabs {
            border-bottom: 2px solid #6c5ce7;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c5ce7;
            font-weight: 600;
        }

        .nav-tabs .nav-link.active {
            background-color: #6c5ce7;
            color: #fff;
            border-radius: 5px 5px 0 0;
        }

        /* Form Inputs */
        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 40px; /* Added padding for icons */
            margin-top: 10px;
            border: 1px solid #ddd;
            height: 50px; /* Set height for inputs to align with icons */
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 8px rgba(108, 92, 231, 0.3);
        }

        /* Button Styles */
        .btn {
            border-radius: 50px;
            font-weight: 600;
            padding: 12px;
        }

        .btn-primary {
            background-color: #6c5ce7;
            border: none;
        }

        .btn-primary:hover {
            background-color: #5a4eec;
        }

        .btn-success {
            background-color: #00b894;
            border: none;
        }

        .btn-success:hover {
            background-color: #00a482;
        }

        /* Adjusting label styles */
        .form-label {
            font-weight: 600;
            color: #333;
        }

        /* Form Header */
        .card-header {
            background: #6c5ce7;
            color: white;
            font-size: 20px;
            font-weight: 600;
            border-radius: 12px 12px 0 0;
            padding: 15px;
            text-align: center;
        }

        /* Icon styling */
        .input-icon {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #6c5ce7;
            font-size: 18px; /* Slightly adjust icon size */
        }

        .form-group {
            position: relative;
        }

        /* Styling for active tab content */
        .tab-pane {
            padding-top: 20px;
        }

        /* Margin for form elements */
        .form-group.mb-3 {
            margin-bottom: 20px;
        }

        /* Clean up some minor styling */
        .btn {
            font-size: 16px;
            padding: 12px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>Login & Register</h3>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="authTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                            Login
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                            Register
                        </button>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="authTabContent">
                    <!-- Login Tab -->
                    <div class="tab-pane fade show active" id="login" role="tabpanel">
                        <form action="login.php" method="POST">
                            <div class="form-group mb-3">
                                <i class="fas fa-user input-icon"></i>
                                <label for="loginUsername" class="form-label">Username</label>
                                <input type="text" class="form-control" id="loginUsername" name="username" required>
                            </div>
                            <div class="form-group mb-3">
                                <i class="fas fa-lock input-icon"></i>
                                <label for="loginPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="loginPassword" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>

                    <!-- Register Tab -->
                    <div class="tab-pane fade" id="register" role="tabpanel">
                        <form action="register.php" method="POST">
                            <div class="form-group mb-3">
                                <i class="fas fa-user input-icon"></i>
                                <label for="regUsername" class="form-label">Username</label>
                                <input type="text" class="form-control" id="regUsername" name="username" required>
                            </div>
                            <div class="form-group mb-3">
                                <i class="fas fa-lock input-icon"></i>
                                <label for="regPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="regPassword" name="password" required>
                            </div>
                            <div class="form-group mb-3">
                                <i class="fas fa-users input-icon"></i>
                                <label for="regRole" class="form-label">Role</label>
                                <select class="form-select" id="regRole" name="role" required>
                                    <option value="">Select role</option>
                                    <option value="admin">Admin</option>
                                    <option value="instructor">Instructor</option>
                                    <option value="student">Student</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <i class="fas fa-id-card input-icon"></i>
                                <label for="linkedId" class="form-label">Linked ID</label>
                                <input type="number" class="form-control" id="linkedId" name="linked_id" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
