<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Only allow logged-in users
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user's used shoe listings
$query = "SELECT * FROM used_shoes WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$listings = $stmt->get_result();
$stmt->close();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php include '../user/user_menu.php'; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Used Shoe Listings</h5>
                    <a href="sell.php" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Add New Listing
                    </a>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                    <?php endif; ?>
                    
                    <?php if($listings->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Details</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($listing = $listings->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <img src="../../assets/images/used_shoes/<?php echo $listing['image']; ?>" 
                                                     width="80" height="80" class="img-thumbnail">
                                            </td>
                                            <td>
                                                <h6><?php echo htmlspecialchars($listing['name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($listing['brand']); ?></small><br>
                                                <small>Size: <?php echo htmlspecialchars($listing['size']); ?></small><br>
                                                <small>Color: <?php echo htmlspecialchars($listing['color']); ?></small>
                                            </td>
                                            <td>$<?php echo number_format($listing['price'], 2); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                        switch($listing['status']) {
                                                            case 'approved': echo 'bg-success'; break;
                                                            case 'pending': echo 'bg-warning text-dark'; break;
                                                            case 'rejected': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                    <?php echo ucfirst($listing['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_listing.php?id=<?php echo $listing['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger delete-listing" data-id="<?php echo $listing['id']; ?>" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center py-4">
                            <i class="fas fa-shoe-prints fa-3x text-muted mb-3"></i>
                            <h5>No Listings Found</h5>
                            <p>You haven't listed any used shoes for sale yet.</p>
                            <a href="sell.php" class="btn btn-primary">List Your First Pair</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete listing
    $('.delete-listing').click(function() {
        if(confirm('Are you sure you want to delete this listing? This cannot be undone.')) {
            const listingId = $(this).data('id');
            
            $.ajax({
                url: '../../functions/used_shoes.php?action=delete',
                method: 'POST',
                data: { id: listingId },
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