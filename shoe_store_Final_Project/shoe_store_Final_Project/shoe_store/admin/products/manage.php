<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

// Only allow admin access
requireAdmin();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = $search ? "WHERE p.name LIKE '%$search%' OR b.name LIKE '%$search%' OR c.name LIKE '%$search%'" : '';

// Get total products
$total_query = "SELECT COUNT(*) as total FROM products p JOIN brands b ON p.brand_id = b.id JOIN categories c ON p.category_id = c.id $where";
$total = $conn->query($total_query)->fetch_assoc()['total'];
$pages = ceil($total / $per_page);

// Get products with pagination
$query = "SELECT p.*, b.name as brand_name, c.name as category_name 
          FROM products p
          JOIN brands b ON p.brand_id = b.id
          JOIN categories c ON p.category_id = c.id
          $where
          ORDER BY p.created_at DESC
          LIMIT $start, $per_page";
$products = $conn->query($query);
?>

<div class="container-fluid">
    <div class="row">
        <?php include(__DIR__ . '/../../admin/includes/sidebar.php'); ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Products</h1>
                <div class="btn-toolbar mb-2 mb-md-0 gap-2">
                    <a href="add.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                    <form class="d-flex" method="GET" action="">
                        <input class="form-control form-control-sm" type="search" name="search" placeholder="Search..." 
                               value="<?= htmlspecialchars($search) ?>" aria-label="Search">
                        <button class="btn btn-sm btn-outline-secondary ms-2" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if(isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Brand</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($products->num_rows > 0): ?>
                                    <?php while($product = $products->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $product['id'] ?></td>
                                            <td>
                                                <img src="../../assets/images/uploads/<?= $product['image'] ?>" 
                                                     width="50" height="50" class="img-thumbnail rounded">
                                            </td>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                            <td><?= htmlspecialchars($product['brand_name']) ?></td>
                                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                                            <td>
                                                <?php if($product['discount_price']): ?>
                                                    <span class="text-danger fw-bold">$<?= number_format($product['discount_price'], 2) ?></span>
                                                    <br>
                                                    <small class="text-muted text-decoration-line-through">$<?= number_format($product['price'], 2) ?></small>
                                                <?php else: ?>
                                                    $<?= number_format($product['price'], 2) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $product['quantity'] ?></td>
                                            <td>
                                                <span class="badge <?= $product['quantity'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $product['quantity'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <button class="btn btn-outline-danger delete-product" 
                                                            data-id="<?= $product['id'] ?>" 
                                                            data-name="<?= htmlspecialchars($product['name']) ?>"
                                                            title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">No products found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if($pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $pages; $i++): ?>
                                    <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if($page < $pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete <strong id="productName"></strong>?</p>
        <p class="text-danger">This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Delete product with modal confirmation
    let productToDelete = null;
    
    // Handle delete button click
    $(document).on('click', '.delete-product', function(e) {
        e.preventDefault(); // Prevent default button behavior
        productToDelete = $(this).data('id');
        $('#productName').text($(this).data('name'));
        $('#deleteModal').modal('show');
    });
    
    // Handle confirm delete click
    $('#confirmDelete').click(function() {
        if(!productToDelete) return;
        
        // Show loading state
        $(this).prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
        
        // Make AJAX request
        $.ajax({
            url: 'delete.php',
            type: 'POST',
            dataType: 'json',
            data: { 
                id: productToDelete,
                csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>' 
            },
            success: function(response) {
                if(response.success) {
                    // Refresh the page to see changes
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to delete product'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error, xhr.responseText);
                alert('Error deleting product. Check console for details.');
            },
            complete: function() {
                $('#deleteModal').modal('hide');
                $('#confirmDelete').prop('disabled', false).text('Delete');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>