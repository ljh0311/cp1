<?php
require_once '../inc/init.php';
requireAdmin();

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    $db = DatabaseManager::getInstance();
    
    // Build query based on filters
    $where_clause = $status !== 'all' ? "WHERE o.status = ?" : "";
    $params = $status !== 'all' ? [$status] : [];
    
    // Get total count for pagination
    $count_query = "SELECT COUNT(*) as total FROM orders o $where_clause";
    $count_result = $db->query($count_query, $params);
    $total_orders = $db->fetch($count_result)['total'];
    $total_pages = ceil($total_orders / $per_page);
    
    // Get orders with user details
    $query = "SELECT o.*, u.email, u.username,
                     (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) as item_count
              FROM orders o
              JOIN users u ON o.user_id = u.user_id
              $where_clause
              ORDER BY o.created_at DESC
              LIMIT ? OFFSET ?";
    
    $params[] = $per_page;
    $params[] = $offset;
    
    $result = $db->query($query, $params);
    $orders = $db->fetchAll($result);
    
} catch (Exception $e) {
    ErrorHandler::logError($e->getMessage());
    $orders = [];
    $total_pages = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Orders - Admin Dashboard</title>
    <?php require_once '../inc/head.inc.php'; ?>
    <style>
        .status-badge {
            text-transform: capitalize;
        }
    </style>
</head>
<body>
    <?php require_once '../inc/nav.inc.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-2">
                <?php require_once 'sidebar.php'; ?>
            </div>
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Manage Orders</h1>
                    <div class="d-flex gap-2">
                        <select class="form-select" id="statusFilter">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Orders</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No orders found.
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Items</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['order_id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($order['username']); ?><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                                </td>
                                                <td><?php echo $order['item_count']; ?> items</td>
                                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($order['status']) {
                                                            'pending' => 'warning',
                                                            'processing' => 'info',
                                                            'completed' => 'success',
                                                            'cancelled' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?> status-badge">
                                                        <?php echo $order['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary view-order" 
                                                                data-order-id="<?php echo $order['order_id']; ?>">
                                                            View
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                                                                data-bs-toggle="dropdown">
                                                            <span class="visually-hidden">Toggle Dropdown</span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <?php if ($order['status'] !== 'completed'): ?>
                                                                <li>
                                                                    <button class="dropdown-item update-status" 
                                                                            data-order-id="<?php echo $order['order_id']; ?>"
                                                                            data-status="completed">
                                                                        Mark as Completed
                                                                    </button>
                                                                </li>
                                                            <?php endif; ?>
                                                            <?php if ($order['status'] === 'pending'): ?>
                                                                <li>
                                                                    <button class="dropdown-item update-status"
                                                                            data-order-id="<?php echo $order['order_id']; ?>"
                                                                            data-status="processing">
                                                                        Mark as Processing
                                                                    </button>
                                                                </li>
                                                            <?php endif; ?>
                                                            <?php if ($order['status'] !== 'cancelled'): ?>
                                                                <li>
                                                                    <button class="dropdown-item update-status text-danger"
                                                                            data-order-id="<?php echo $order['order_id']; ?>"
                                                                            data-status="cancelled">
                                                                        Cancel Order
                                                                    </button>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if ($total_pages > 1): ?>
                                <nav class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>">Previous</a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="orderDetails">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../inc/footer.inc.php'; ?>

    <script>
        // Handle status filter changes
        document.getElementById('statusFilter').addEventListener('change', function() {
            window.location.href = '?status=' + this.value;
        });

        // Handle view order button clicks
        document.querySelectorAll('.view-order').forEach(button => {
            button.addEventListener('click', async function() {
                const orderId = this.dataset.orderId;
                const modal = new bootstrap.Modal(document.getElementById('orderModal'));
                const detailsContainer = document.getElementById('orderDetails');
                
                try {
                    detailsContainer.innerHTML = 'Loading...';
                    modal.show();
                    
                    const response = await fetch(`/admin/get_order_details.php?id=${orderId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        detailsContainer.innerHTML = data.html;
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    detailsContainer.innerHTML = `<div class="alert alert-danger">Error loading order details: ${error.message}</div>`;
                }
            });
        });

        // Handle status update button clicks
        document.querySelectorAll('.update-status').forEach(button => {
            button.addEventListener('click', async function() {
                if (!confirm('Are you sure you want to update this order\'s status?')) {
                    return;
                }

                const orderId = this.dataset.orderId;
                const newStatus = this.dataset.status;
                
                try {
                    const response = await fetch('/admin/update_order_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            status: newStatus
                        })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    alert('Failed to update order status: ' + error.message);
                }
            });
        });
    </script>
</body>
</html> 