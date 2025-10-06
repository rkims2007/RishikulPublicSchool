<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../common/config.php';

// Pagination settings
$limit = 20; // students per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Filters
$class_filter = $_GET['class'] ?? '';
$section_filter = $_GET['section'] ?? '';
$search_query = $_GET['search'] ?? '';

// Build SQL with filters
$sql_where = "WHERE 1";
$params = [];
$types = '';

if ($class_filter !== '') {
    $sql_where .= " AND class_name = ?";
    $params[] = $class_filter;
    $types .= 's';
}

if ($section_filter !== '') {
    $sql_where .= " AND section = ?";
    $params[] = strtoupper($section_filter);
    $types .= 's';
}

if ($search_query !== '') {
    $sql_where .= " AND (first_name LIKE ? OR last_name LIKE ? OR id LIKE ? OR admission_no LIKE ?)";
    $search_term = "%$search_query%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    $types .= 'ssss';
}

// Count total records
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM students $sql_where");
if ($params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Fetch paginated records
$sql = "SELECT * FROM students $sql_where ORDER BY id DESC LIMIT ? OFFSET ?";
$params_paginated = $params;
$types_paginated = $types . 'ii';
$params_paginated[] = $limit;
$params_paginated[] = $offset;

$stmt = $conn->prepare($sql);
$stmt->bind_param($types_paginated, ...$params_paginated);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Students - Admin</title>
<link href="../css/style.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.table-img { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
.card { box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 12px; }
.card-header { background-color: #0d6efd; color: #fff; font-size: 1.5rem; font-weight: 500; border-top-left-radius: 12px; border-top-right-radius: 12px; }
.pagination .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; }
</style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>

<div class="container my-5">
    <div class="card">
        <div class="card-header text-center">
            <i class="bi bi-people-fill"></i> All Students
            <a href="add_student.php" class="btn btn-light btn-sm float-end"><i class="bi bi-plus-circle"></i> Add Student</a>
        </div>
        <div class="card-body">
            <?php if(isset($_GET['added'])): ?>
                <div class="alert alert-success">Student added successfully!</div>
            <?php endif; ?>

            <!-- Filter & Search Form -->
            <form class="row g-3 mb-4" method="GET">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, ID or admission" value="<?= htmlspecialchars($search_query) ?>">
                </div>
                <div class="col-md-3">
                    <select name="class" class="form-select">
                        <option value="">All Classes</option>
                        <option value="PG" <?= $class_filter=='PG'?'selected':'' ?>>PG</option>
                        <option value="LKG" <?= $class_filter=='LKG'?'selected':'' ?>>LKG</option>
                        <option value="UKG" <?= $class_filter=='UKG'?'selected':'' ?>>UKG</option>
                        <?php for($i=1;$i<=10;$i++): ?>
                            <option value="<?= $i ?>" <?= $class_filter==$i?'selected':'' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="section" class="form-control" placeholder="Section" maxlength="1" value="<?= htmlspecialchars($section_filter) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="show_student.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>Photo</th>
                            <th>Student ID</th>
                            <th>Admission No</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>DOB</th>
                            <th>Gender</th>
                            <th>Guardian</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows>0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if($row['photo'] && file_exists("../images/students/".$row['photo'])): ?>
                                            <img src="../images/students/<?= $row['photo'] ?>" class="table-img" alt="Photo">
                                        <?php else: ?>
                                            <img src="../images/logo.png" class="table-img" alt="No Photo">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['admission_no']) ?></td>
                                    <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>
                                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                                    <td><?= htmlspecialchars($row['section']) ?></td>
                                    <td><?= htmlspecialchars($row['dob']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($row['gender'])) ?></td>
                                    <td><?= htmlspecialchars($row['guardian_name']) ?></td>
                                    <td><?= htmlspecialchars($row['guardian_phone']) ?></td>
                                    <td><?= htmlspecialchars($row['address']) ?></td>
                                    <td>
                                        <a href="edit_student.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
                                        <a href="delete_student.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this student?');"><i class="bi bi-trash"></i></a>
                                        <a href="print_student_id.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-info"><i class="bi bi-printer"></i> Print ID</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="text-center">No students found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for($i=1;$i<=$total_pages;$i++): ?>
                        <?php
                        $query_params = $_GET;
                        $query_params['page'] = $i;
                        $url = "show_student.php?" . http_build_query($query_params);
                        ?>
                        <li class="page-item <?= $i==$page?'active':'' ?>">
                            <a class="page-link" href="<?= $url ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>
