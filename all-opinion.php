<?php
require_once 'includes/user_auth.php'; // For user authentication
require_once 'includes/functions.php'; // For utility functions

// Fetch all opinions from the database
$stmt = $conn->prepare("SELECT users.name AS username, opinions.opinion_text, opinions.created_at 
                        FROM opinions 
                        JOIN users ON opinions.user_id = users.id 
                        ORDER BY opinions.created_at DESC");

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>সকল মতামত</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <?php require_once 'includes/header.php'; ?>
</head>
<body>

<h1>সকল মতামত</h1>
<p class="lead">এখানে সমস্ত ব্যবহারকারীদের মতামত দেখুন।</p>
<hr>

<?php
// Display all opinions
if ($result->num_rows > 0): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ব্যবহারকারী</th>
                <th>মতামত</th>
                <th>তারিখ</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['opinion_text']) ?></td>
                    <td><?= date("F j, Y, g:i a", strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>কোনো মতামত পাওয়া যায়নি।</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
</body>
</html>
