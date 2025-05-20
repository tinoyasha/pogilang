<?php
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name      = trim($_POST['first_name']);
    $last_name       = trim($_POST['last_name']);
    $email           = trim($_POST['email']);
    $dob             = $_POST['date_of_birth'];
    $gender          = $_POST['gender'];
    $department_id   = (int)$_POST['department_id'];
    $enrollment_year = (int)$_POST['enrollment_year'];

    if (!$first_name || !$last_name || !$email || !$dob || !$gender || !$department_id || !$enrollment_year) {
        $message = '<div class="alert alert-danger text-center">⚠️ All fields are required.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO students 
                (first_name, last_name, email, date_of_birth, gender, department_id, enrollment_year)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $first_name, $last_name, $email, $dob, $gender, $department_id, $enrollment_year
            ]);
            $message = '<div class="alert alert-success text-center">✅ Student enrolled successfully!</div>';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = '<div class="alert alert-warning text-center">⚠️ Email already exists.</div>';
            } else {
                $message = '<div class="alert alert-danger text-center">❌ Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    }
}

$departments = $pdo->query("SELECT department_id, name FROM departments")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Enrollment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            font-family: 'Roboto', sans-serif;
            color: #fff;
        }
        .form-container {
            max-width: 700px;
            margin: 60px auto;
            background: #1e1e1e;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .form-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #00e5ff;
            text-align: center;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 6px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border-color: #333;
            background-color: #333;
            color: #fff;
        }
        .form-control:focus, .form-select:focus {
            border-color: #00e5ff;
            box-shadow: 0 0 0 0.2rem rgba(0, 229, 255, 0.25);
        }
        .btn-primary {
            background-color: #00e5ff;
            border: none;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #00b8cc;
        }
    </style>
</head>
<body>

<div class="form-container">
    <div class="form-title">⚙️ Student Enrollment Form</div>

    <?= $message ?>

    <form method="POST" action="">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" required />
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required />
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select" required>
                    <option value="" selected disabled>Choose gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select" required>
                    <option value="" disabled selected>Select department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Enrollment Year</label>
                <input type="number" name="enrollment_year" class="form-control" min="2000" max="2099" required />
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Enroll Now</button>
        </div>
    </form>
</div>

</body>
</html>
