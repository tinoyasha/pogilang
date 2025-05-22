<?php
session_start();
require 'db.php';

// Fetch user info
$user_id = $_SESSION['user_id'];
$user_name = 'Admin'; // Default name
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if ($user) {
    $user_name = $user['username'];
}

// CRUD Operations
$message = '';
$current_course = null;

// Create or Update Course
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? null;
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $credit_hours = (int)$_POST['credit_hours'];
    $department_id = (int)$_POST['department_id'];
    $instructor_id = (int)$_POST['instructor_id'];
    
    // Validation
    if (empty($course_code) || empty($course_name) || $credit_hours <= 0) {
        $message = '<div class="alert alert-danger">Please fill all required fields</div>';
    } else {
        try {
            if (empty($course_id)) {
                // Create new course
                $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_name, credit_hours, department_id, instructor_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$course_code, $course_name, $credit_hours, $department_id, $instructor_id]);
                $message = '<div class="alert alert-success">Course created successfully</div>';
            } else {
                // Update existing course
                $stmt = $pdo->prepare("UPDATE courses SET course_code = ?, course_name = ?, credit_hours = ?, department_id = ?, instructor_id = ? WHERE course_id = ?");
                $stmt->execute([$course_code, $course_name, $credit_hours, $department_id, $instructor_id, $course_id]);
                $message = '<div class="alert alert-success">Course updated successfully</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Delete Course
if (isset($_GET['delete'])) {
    $course_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE course_id = ?");
        $stmt->execute([$course_id]);
        $message = '<div class="alert alert-success">Course deleted successfully</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Edit Course - Fetch data
if (isset($_GET['edit'])) {
    $course_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $current_course = $stmt->fetch();
}

// Fetch all courses
$stmt = $pdo->query("SELECT c.*, d.name as department_name, CONCAT(i.first_name, ' ', i.last_name) as instructor_name 
                     FROM courses c
                     LEFT JOIN departments d ON c.department_id = d.department_id
                     LEFT JOIN instructors i ON c.instructor_id = i.instructor_id
                     ORDER BY c.course_code");
$courses = $stmt->fetchAll();

// Fetch departments for dropdown
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

// Fetch instructors for dropdown
$instructors = $pdo->query("SELECT * FROM instructors ORDER BY first_name, last_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management | Academic Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Your existing styles from dashboard */
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --text-dark: #5a5c69;
            --text-light: #858796;
        }
        
        body {
            background-color: var(--secondary-color);
            color: var(--text-dark);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            min-height: 100vh;
            color: white;
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
            border-radius: 0.35rem;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        /* ... include all your existing styles ... */
        .user-profile {
            text-align: center;
            padding: 2rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .user-profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 1rem;
        }
        .form-container {
            background-color: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .action-btns .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (same as your dashboard) -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-primary">
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=random" alt="User Profile">
                    <h5><?= htmlspecialchars($user_name) ?></h5>
                    <p class="text-muted">Admin</p>
                </div>
                <ul class="nav flex-column ">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="home.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="courses.php">
                            <i class="bi bi-book"></i> Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="departments.php">
                            <i class="bi bi-building"></i> Departments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="instructor.php">
                            <i class="bi bi-people"></i> Instructors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Course Management</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                        <i class="bi bi-plus-circle"></i> Add New Course
                    </button>
                </div>

                <?= $message ?>

                <!-- Course Form Modal -->
                <div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="courseModalLabel">
                                    <?= $current_course ? 'Edit Course' : 'Add New Course' ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="courses.php">
                                <div class="modal-body">
                                    <input type="hidden" name="course_id" value="<?= $current_course['course_id'] ?? '' ?>">
                                    
                                    <div class="mb-3">
                                        <label for="course_code" class="form-label">Course Code *</label>
                                        <input type="text" class="form-control" id="course_code" name="course_code" 
                                               value="<?= htmlspecialchars($current_course['course_code'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="course_name" class="form-label">Course Name *</label>
                                        <input type="text" class="form-control" id="course_name" name="course_name" 
                                               value="<?= htmlspecialchars($current_course['course_name'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="credit_hours" class="form-label">Credit Hours *</label>
                                        <input type="number" class="form-control" id="credit_hours" name="credit_hours" 
                                               value="<?= htmlspecialchars($current_course['credit_hours'] ?? '') ?>" min="1" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="department_id" class="form-label">Department</label>
                                        <select class="form-select" id="department_id" name="department_id">
                                            <option value="">Select Department</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?= $dept['department_id'] ?>" 
                                                    <?= ($current_course['department_id'] ?? '') == $dept['department_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($dept['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="instructor_id" class="form-label">Instructor</label>
                                        <select class="form-select" id="instructor_id" name="instructor_id">
                                            <option value="">Select Instructor</option>
                                            <?php foreach ($instructors as $inst): ?>
                                                <option value="<?= $inst['instructor_id'] ?>" 
                                                    <?= ($current_course['instructor_id'] ?? '') == $inst['instructor_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($inst['first_name'] . ' ' . $inst['last_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">
                                        <?= $current_course ? 'Update Course' : 'Add Course' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Courses Table -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">All Courses</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Credits</th>
                                        <th>Department</th>
                                        <th>Instructor</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($course['course_code']) ?></td>
                                        <td><?= htmlspecialchars($course['course_name']) ?></td>
                                        <td><?= htmlspecialchars($course['credit_hours']) ?></td>
                                        <td><?= htmlspecialchars($course['department_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($course['instructor_name'] ?? 'N/A') ?></td>
                                        <td class="action-btns">
                                            <a href="courses.php?edit=<?= $course['course_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="courses.php?delete=<?= $course['course_id'] ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this course?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Open modal if editing
        <?php if ($current_course): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var courseModal = new bootstrap.Modal(document.getElementById('courseModal'));
            courseModal.show();
        });
        <?php endif; ?>
    </script>
</body>
</html>