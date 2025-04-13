<?php
require_once __DIR__ . '/../includes/header.php';
require_once '../config.php'; // Database connection

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Count total records
$count_sql = "SELECT COUNT(*) AS total FROM users WHERE name LIKE ? OR mobile LIKE ?";
$stmt = $conn->prepare($count_sql);
$search_param = "%$search%";
$stmt->bind_param("ss", $search_param, $search_param);
$stmt->execute();
$count_result = $stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch records with formatted ID (SSC Year + 4-digit ID)
$sql = "SELECT id, name, mobile, ssc_year, 
        CONCAT(ssc_year, LPAD(id, 4, '0')) AS formatted_id 
        FROM users 
        WHERE name LIKE ? OR mobile LIKE ? 
        ORDER BY id ASC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $search_param, $search_param, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-users me-2"></i> ইউজার তালিকা</h4>
            <form class="d-flex" method="get" action="">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="নাম বা মোবাইল" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-light btn-sm"><i class="fas fa-search"></i> খুঁজুন</button>
            </form>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>ক্রমিক নং</th>
                                <th><i class="fas fa-id-card"></i> আইডি নাম্বার</th>
                                <th><i class="fas fa-user"></i> নাম</th>
                                <th><i class="fas fa-phone"></i> মোবাইল নাম্বার</th>
                                <th><i class="fas fa-info-circle"></i> বিস্তারিত</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php 
                            $serial = $offset + 1;
                            while ($row = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($row['formatted_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                <td>
                                    <a href="user_info.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> বিস্তারিত
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <nav>
                    <ul class="pagination justify-content-center mt-3">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>

            <?php else: ?>
                <div class="alert alert-warning text-center" role="alert">
                    কোনো ইউজার পাওয়া যায়নি।
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>