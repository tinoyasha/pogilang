<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user info
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$linked_id = null;

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if ($user) {
    $linked_id = $user['linked_id'];
    // Safely get first_name and last_name with null checks
    $user_name = $user['username'];
}

// Role-based data fetching
$data = [];
switch ($role) {
    case 'admin':
        $stmt = $pdo->query("SELECT * FROM departments");
        $data['departments'] = $stmt->fetchAll();

        $stmt = $pdo->query("SELECT * FROM instructors");
        $data['instructors'] = $stmt->fetchAll();

        $stmt = $pdo->query("SELECT * FROM students");
        $data['students'] = $stmt->fetchAll();

        $stmt = $pdo->query("SELECT * FROM courses");
        $data['courses'] = $stmt->fetchAll();
        break;

    case 'instructor':
        $stmt = $pdo->prepare("
            SELECT c.* FROM courses c 
            WHERE c.instructor_id = ?
        ");
        $stmt->execute([$linked_id]);
        $data['courses'] = $stmt->fetchAll();
        
        // Get instructor info for the dashboard
        $stmt = $pdo->prepare("SELECT * FROM instructors WHERE instructor_id = ?");
        $stmt->execute([$linked_id]);
        $data['instructor_info'] = $stmt->fetch();
        break;

    case 'student':
        $stmt = $pdo->prepare("
            SELECT c.course_name, s.name as semester, e.grade 
            FROM enrollments e
            JOIN courses c ON e.course_id = c.course_id
            JOIN semesters s ON e.semester_id = s.semester_id
            WHERE e.student_id = ?
        ");
        $stmt->execute([$linked_id]);
        $data['enrollments'] = $stmt->fetchAll();
        
        // Get student info for the dashboard
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$linked_id]);
        $data['student_info'] = $stmt->fetch();
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Academic Portal</title>
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
        
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            border-radius: 0.35rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .stat-card {
            border-left: 0.25rem solid var(--primary-color);
        }
        
        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .grade-A { background-color: #e6f7e6; }
        .grade-B { background-color: #f0f7e6; }
        .grade-C { background-color: #f7f3e6; }
        .grade-D { background-color: #f7e6e6; }
        .grade-F { background-color: #f7d6d6; }
        
        .badge-grade {
            padding: 0.35em 0.65em;
            font-weight: 600;
            border-radius: 0.25rem;
        }
        
        .badge-A { background-color: #28a745; color: white; }
        .badge-B { background-color: #17a2b8; color: white; }
        .badge-C { background-color: #ffc107; color: #212529; }
        .badge-D { background-color: #fd7e14; color: white; }
        .badge-F { background-color: #dc3545; color: white; }
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
                    <p class="text-muted"><?= ucfirst($role) ?></p>
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
                        <a class="nav-link" href="departments.php">
                            <i class="bi bi-building"></i> Departments
                        </a>
                    </li>
                    <li class="nav-item">
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-calendar"></i> <?= date('F j, Y') ?>
                        </button>
                    </div>
                </div>

                <!-- Welcome Card -->
                <div class="welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2>Welcome back, <?= htmlspecialchars($user_name) ?>!</h2>
                            <p class="mb-0">Here's what's happening in your academic portal today.</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <i class="bi bi-mortarboard" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>

                <?php if ($role === 'admin'): ?>
                    <!-- Admin Dashboard -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted">Departments</h6>
                                    <div class="stat-value"><?= count($data['departments']) ?></div>
                                    <i class="bi bi-building float-end" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted">Instructors</h6>
                                    <div class="stat-value"><?= count($data['instructors']) ?></div>
                                    <i class="bi bi-person-badge float-end" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted">Students</h6>
                                    <div class="stat-value"><?= count($data['students']) ?></div>
                                    <i class="bi bi-people float-end" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted">Courses</h6>
                                    <div class="stat-value"><?= count($data['courses']) ?></div>
                                    <i class="bi bi-book float-end" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold">Departments</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Name</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data['departments'] as $d): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($d['code']) ?></td>
                                                    <td><?= htmlspecialchars($d['name']) ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></button>
                                                        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold">Recent Courses</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Name</th>
                                                    <th>Credits</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($data['courses'], 0, 5) as $c): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($c['course_code']) ?></td>
                                                    <td><?= htmlspecialchars($c['course_name']) ?></td>
                                                    <td><?= htmlspecialchars($c['credit_hours']) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($role === 'instructor'): ?>
                    <!-- Instructor Dashboard -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted">Courses Teaching</h6>
                                    <div class="stat-value"><?= count($data['courses']) ?></div>
                                    <i class="bi bi-book float-end" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted">Department</h6>
                                    <div class="stat-value"><?= htmlspecialchars($data['instructor_info']['department'] ?? 'N/A') ?></div>
                                    <i class="bi bi-building float-end" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted">Email</h6>
                                    <div class="stat-value" style="font-size: 1rem;"><?= htmlspecialchars($data['instructor_info']['email'] ?? 'N/A') ?></div>
                                    <i class="bi bi-envelope float-end" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">Your Courses</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Credits</th>
                                            <th>Schedule</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['courses'] as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['course_code']) ?></td>
                                            <td><?= htmlspecialchars($c['course_name']) ?></td>
                                            <td><?= htmlspecialchars($c['credit_hours']) ?></td>
                                            <td><?= htmlspecialchars($c['schedule'] ?? 'TBA') ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-people"></i> Roster</button>
                                                <button class="btn btn-sm btn-outline-success"><i class="bi bi-clipboard-data"></i> Grades</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php elseif ($role === 'student'): ?>
                    <!-- Student Dashboard -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted">Current Courses</h6>
                                    <div class="stat-value"><?= count($data['enrollments']) ?></div>
                                    <i class="bi bi-book float-end" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted">Major</h6>
                                    <div class="stat-value"><?= htmlspecialchars($data['student_info']['major'] ?? 'Undeclared') ?></div>
                                    <i class="bi bi-mortarboard float-end" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted">GPA</h6>
                                    <div class="stat-value"><?= $data['student_info']['gpa'] ?? 'N/A' ?></div>
                                    <i class="bi bi-graph-up float-end" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">Your Grades</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Semester</th>
                                            <th>Grade</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['enrollments'] as $e): 
                                            $grade_class = isset($e['grade']) ? 'grade-' . $e['grade'] : '';
                                            $badge_class = isset($e['grade']) ? 'badge-' . $e['grade'] : 'badge-secondary';
                                        ?>
                                        <tr class="<?= $grade_class ?>">
                                            <td><?= htmlspecialchars($e['course_name']) ?></td>
                                            <td><?= htmlspecialchars($e['semester']) ?></td>
                                            <td>
                                                <span class="badge <?= $badge_class ?> badge-grade">
                                                    <?= $e['grade'] ?? 'In Progress' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (isset($e['grade'])): ?>
                                                    <span class="text-success">Completed</span>
                                                <?php else: ?>
                                                    <span class="text-warning">In Progress</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <footer class="pt-3 mt-4 text-muted border-top">
                    &copy; <?= date('Y') ?> Academic Portal
                </footer>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple script to highlight current nav item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop() || 'index.php';
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
                
                link.addEventListener('click', function() {
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>