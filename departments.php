<?php
session_start();
require 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

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
$current_department = null;

// Create or Update Department
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_id = $_POST['department_id'] ?? null;
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    
    // Validation
    if (empty($name) || empty($code)) {
        $message = '<div class="alert alert-danger">Please fill all required fields</div>';
    } else {
        try {
            if (empty($department_id)) {
                // Create new department
                $stmt = $pdo->prepare("INSERT INTO departments (name, code) VALUES (?, ?)");
                $stmt->execute([$name, $code]);
                $message = '<div class="alert alert-success">Department created successfully</div>';
            } else {
                // Update existing department
                $stmt = $pdo->prepare("UPDATE departments SET name = ?, code = ? WHERE department_id = ?");
                $stmt->execute([$name, $code, $department_id]);
                $message = '<div class="alert alert-success">Department updated successfully</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Delete Department
if (isset($_GET['delete'])) {
    $department_id = (int)$_GET['delete'];
    try {
        // First check if any courses are assigned to this department
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE department_id = ?");
        $stmt->execute([$department_id]);
        $course_count = $stmt->fetchColumn();
        
        if ($course_count > 0) {
            $message = '<div class="alert alert-danger">Cannot delete department - there are courses assigned to it</div>';
        } else {
            $stmt = $pdo->prepare("DELETE FROM departments WHERE department_id = ?");
            $stmt->execute([$department_id]);
            $message = '<div class="alert alert-success">Department deleted successfully</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Edit Department - Fetch data
if (isset($_GET['edit'])) {
    $department_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE department_id = ?");
    $stmt->execute([$department_id]);
    $current_department = $stmt->fetch();
}

// Fetch all departments
$stmt = $pdo->query("SELECT d.*, 
                    (SELECT COUNT(*) FROM courses WHERE department_id = d.department_id) as course_count
                    FROM departments d
                    ORDER BY d.name");
$departments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management | Academic Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
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
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.35rem;
            font-weight: 600;
        }
        
        .table {
            color: var(--text-dark);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--text-light);
        }
        
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
        
        .course-count-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-primary">
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=random" alt="User Profile">
                    <h5><?= htmlspecialchars($user_name) ?></h5>
                    <p class="text-muted">Admin</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php">
                            <i class="bi bi-book"></i> Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="departments.php">
                            <i class="bi bi-building"></i> Departments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-people"></i> Instructors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Department Management</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#departmentModal">
                        <i class="bi bi-plus-circle"></i> Add New Department
                    </button>
                </div>

                <?= $message ?>

                <!-- Department Form Modal -->
                <div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="departmentModalLabel">
                                    <?= $current_department ? 'Edit Department' : 'Add New Department' ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="departments.php">
                                <div class="modal-body">
                                    <input type="hidden" name="department_id" value="<?= $current_department['department_id'] ?? '' ?>">
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Department Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?= htmlspecialchars($current_department['name'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Department Code *</label>
                                        <input type="text" class="form-control" id="code" name="code" 
                                               value="<?= htmlspecialchars($current_department['code'] ?? '') ?>" required>
                                        <small class="text-muted">Short code/abbreviation for the department</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">
                                        <?= $current_department ? 'Update Department' : 'Add Department' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Departments Table -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">All Departments</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Courses</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departments as $department): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($department['code']) ?></td>
                                        <td><?= htmlspecialchars($department['name']) ?></td>
                                        <td>
                                            <span class="badge bg-primary course-count-badge">
                                                <?= $department['course_count'] ?> course(s)
                                            </span>
                                        </td>
                                        <td class="action-btns">
                                            <a href="departments.php?edit=<?= $department['department_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="departments.php?delete=<?= $department['department_id'] ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this department?')">
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
        <?php if ($current_department): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var departmentModal = new bootstrap.Modal(document.getElementById('departmentModal'));
            departmentModal.show();
        });
        <?php endif; ?>
    </script>
</body>
</html>