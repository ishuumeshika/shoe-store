<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Only allow admin access
requireAdmin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;

// Get filter parameters
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build base query
$query = "SELECT SQL_CALC_FOUND_ROWS id, name, email, phone, role, created_at 
          FROM users";

// Add filters
$where = [];
$params = [];
$types = '';

if($role_filter) {
    $where[] = "role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if($search_query) {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= 'sss';
}

if(!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

// Complete query with sorting and pagination
$query .= " ORDER BY created_at DESC LIMIT $start, $per_page";

// Prepare and execute
$stmt = $conn->prepare($query);

if($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$users = $stmt->get_result();

// Get total count
$total = $conn->query("SELECT FOUND_ROWS() as total")->fetch_assoc()['total'];
$pages = ceil($total / $per_page);
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Users</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-select">
                                <option value="">All Roles</option>
                                <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="customer" <?= $role_filter == 'customer' ? 'selected' : '' ?>>Customer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Name, Email or Phone" value="<?= htmlspecialchars($search_query) ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="manage.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <?php if($users->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= htmlspecialchars($user['name']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= $user['phone'] ? htmlspecialchars($user['phone']) : 'N/A' ?></td>
                                            <td>
                                                <span class="badge <?= $user['role'] == 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger delete-user" 
                                                        data-id="<?= $user['id'] ?>" 
                                                        title="Delete"
                                                        <?= $_SESSION['user_id'] == $user['id'] ? 'disabled' : '' ?>>
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-4">
                                    <?php if($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page-1 ?>&role=<?= $role_filter ?>&search=<?= urlencode($search_query) ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for($i = 1; $i <= $pages; $i++): ?>
                                        <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&role=<?= $role_filter ?>&search=<?= urlencode($search_query) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if($page < $pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page+1 ?>&role=<?= $role_filter ?>&search=<?= urlencode($search_query) ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No Users Found</h5>
                            <p>There are no users matching your criteria.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete user
    $('.delete-user').click(function() {
        const userId = $(this).data('id');
        
        if(confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            $.ajax({
                url: '../../functions/users.php?action=delete',
                method: 'POST',
                data: { id: userId },
                success: function(response) {
                    const result = JSON.parse(response);
                    if(result.success) {
                        location.reload();
                    } else {
                        alert(result.message);
                    }
                }
            });
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>