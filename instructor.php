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
$current_instructor = null;

// Create or Update Instructor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructor_id = $_POST['instructor_id'] ?? null;
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $department_id = (int)$_POST['department_id'];
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $message = '<div class="alert alert-danger">Please fill all required fields</div>';
    } else {
        try {
            if (empty($instructor_id)) {
                // Create new instructor
                $stmt = $pdo->prepare("INSERT INTO instructors (first_name, last_name, email, department_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$first_name, $last_name, $email, $department_id]);
                $message = '<div class="alert alert-success">Instructor created successfully</div>';
            } else {
                // Update existing instructor
                $stmt = $pdo->prepare("UPDATE instructors SET first_name = ?, last_name = ?, email = ?, department_id = ? WHERE instructor_id = ?");
                $stmt->execute([$first_name, $last_name, $email, $department_id, $instructor_id]);
                $message = '<div class="alert alert-success">Instructor updated successfully</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Delete Instructor
if (isset($_GET['delete'])) {
    $instructor_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM instructors WHERE instructor_id = ?");
        $stmt->execute([$instructor_id]);
        $message = '<div class="alert alert-success">Instructor deleted successfully</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Edit Instructor - Fetch data
if (isset($_GET['edit'])) {
    $instructor_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM instructors WHERE instructor_id = ?");
    $stmt->execute([$instructor_id]);
    $current_instructor = $stmt->fetch();
}

// Fetch all instructors
$stmt = $pdo->query("SELECT i.*, d.name as department_name FROM instructors i LEFT JOIN departments d ON i.department_id = d.department_id ORDER BY i.first_name, i.last_name");
$instructors = $stmt->fetchAll();

// Fetch departments for dropdown
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Management | Academic Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Custom Styles */
        body {
            background-color: #f8f9fc;
            color: #5a5c69;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
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
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-primary">
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=random" alt="User Profile">
                    <h5><?= htmlspecialchars($user_name) ?></h5>
                    <p class="text-muted">Admin</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php">
                            <i class="bi bi-book"></i> Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="departments.php">
                            <i class="bi bi-building"></i> Departments
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="instructor.php">
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
                    <h1 class="h2">Instructor Management</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#instructorModal">
                        <i class="bi bi-plus-circle"></i> Add New Instructor
                    </button>
                </div>

                <?= $message ?>

                <!-- Instructor Form Modal -->
                <div class="modal fade" id="instructorModal" tabindex="-1" aria-labelledby="instructorModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="instructorModalLabel">
                                    <?= $current_instructor ? 'Edit Instructor' : 'Add New Instructor' ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="instructors.php">
                                <div class="modal-body">
                                    <input type="hidden" name="instructor_id" value="<?= $current_instructor['instructor_id'] ?? '' ?>">
                                    
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?= htmlspecialchars($current_instructor['first_name'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?= htmlspecialchars($current_instructor['last_name'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($current_instructor['email'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="department_id" class="form-label">Department</label>
                                        <select class="form-select" id="department_id" name="department_id">
                                            <?php foreach ($departments as $department): ?>
                                                <option value="<?= $department['department_id'] ?>" 
                                                        <?= isset($current_instructor) && $current_instructor['department_id'] == $department['department_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($department['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary"><?= $current_instructor ? 'Save Changes' : 'Add Instructor' ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Instructor Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($instructors as $instructor): ?>
                                <tr>
                                    <td><?= htmlspecialchars($instructor['first_name']) ?></td>
                                    <td><?= htmlspecialchars($instructor['last_name']) ?></td>
                                    <td><?= htmlspecialchars($instructor['email']) ?></td>
                                    <td><?= htmlspecialchars($instructor['department_name']) ?></td>
                                    <td class="action-btns">
                                        <a href="instructors.php?edit=<?= $instructor['instructor_id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="instructors.php?delete=<?= $instructor['instructor_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this instructor?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
