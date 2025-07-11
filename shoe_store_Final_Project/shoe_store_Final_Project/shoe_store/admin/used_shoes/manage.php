<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Only allow admin access
requireAdmin();

// Get filter parameter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Get used shoes listings
$query = "SELECT u.*, us.name as seller_name, us.email as seller_email 
          FROM used_shoes u
          JOIN users us ON u.user_id = us.id
          WHERE u.status = ?
          ORDER BY u.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $status_filter);
$stmt->execute();
$listings = $stmt->get_result();
$stmt->close();

// Get counts for each status
$counts = $conn->query("SELECT status, COUNT(*) as count FROM used_shoes GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$status_counts = [];
foreach($counts as $count) {
    $status_counts[$count['status']] = $count['count'];
}

// Handle status update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $listing_id = (int)$_POST['listing_id'];
    $new_status = $_POST['new_status'];
    
    $update_stmt = $conn->prepare("UPDATE used_shoes SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $listing_id);
    
    if($update_stmt->execute()) {
        $_SESSION['success_message'] = "Listing status updated successfully!";
        header("Location: manage.php?status=$status_filter");
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to update listing status.";
    }
    $update_stmt->close();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Used Shoes Listings</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                </div>
            </div>

            <?php if(isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            
            <!-- Status Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link text-black <?php echo $status_filter == 'pending' ? 'active' : ''; ?>" href="?status=pending">
                        Pending
                        <span class="badge bg-secondary ms-1"><?php echo $status_counts['pending'] ?? 0; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-black <?php echo $status_filter == 'approved' ? 'active' : ''; ?>" href="?status=approved">
                        Approved
                        <span class="badge bg-success ms-1"><?php echo $status_counts['approved'] ?? 0; ?></span>
                    </a>
                </li>

                

                <li class="nav-item">
                    <a class="nav-link text-black<?php echo $status_filter == 'rejected' ? 'active' : ''; ?>" href="?status=rejected">
                        Rejected
                        <span class="badge bg-danger ms-1"><?php echo $status_counts['rejected'] ?? 0; ?></span>
                    </a>
                </li>
            </ul>
            
            <?php if($listings->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Details</th>
                                <th>Seller</th>
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
                                        <small>Color: <?php echo htmlspecialchars($listing['color']); ?></small><br>
                                        <small>Condition: <?php echo ucfirst(str_replace('_', ' ', $listing['shoe_condition'])); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($listing['seller_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($listing['seller_email']); ?></small>
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
                                        <div class="btn-group">
                                            <?php if($listing['status'] == 'pending'): ?>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                                    <input type="hidden" name="new_status" value="approved">
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                                    <input type="hidden" name="new_status" value="rejected">
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-secondary view-listing" data-id="<?php echo $listing['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
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
                    <p>There are no <?php echo $status_filter; ?> used shoe listings.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- View Listing Modal -->
<div class="modal fade" id="listingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Listing Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="listingDetails">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // View listing details
    $('.view-listing').click(function() {
        const listingId = $(this).data('id');
        
        $.ajax({
            url: 'get_listing.php',
            method: 'GET',
            data: { id: listingId },
            success: function(response) {
                $('#listingDetails').html(response);
                $('#listingModal').modal('show');
            },
            error: function() {
                alert('Failed to load listing details.');
            }
        });
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>