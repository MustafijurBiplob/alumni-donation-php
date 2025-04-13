<?php
    require_once __DIR__ . '/../includes/admin_auth.php';
    require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../includes/functions.php';

    // Handle Delete Action
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user_id'])) {
        $user_id_to_delete = (int)$_POST['delete_user_id'];
        $current_admin_id = $_SESSION['user_id'];

        if ($user_id_to_delete > 0 && $user_id_to_delete != $current_admin_id) { // Prevent admin from deleting themselves
            // Optional: Check if user has donations before deleting, or handle via DB constraints (ON DELETE CASCADE/SET NULL)
            // Our donation table uses ON DELETE CASCADE, so donations will be deleted too. Be careful!

            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'"); // Extra safety: don't delete other admins this way
            $stmt->bind_param("i", $user_id_to_delete);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    set_message("User ID {$user_id_to_delete} deleted successfully.", "success");
                } else {
                    set_message("User ID {$user_id_to_delete} could not be deleted (might be an admin or not found).", "warning");
                }
            } else {
                set_message("Error deleting user: " . $stmt->error, "danger");
            }
            $stmt->close();
        } elseif ($user_id_to_delete == $current_admin_id) {
             set_message("You cannot delete your own admin account.", "danger");
        } else {
             set_message("Invalid user ID for deletion.", "danger");
        }
        redirect('admin/user_management.php');
    }

    // Handle Role Change Action
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_role_user_id']) && isset($_POST['new_role'])) {
        $user_id_to_change = (int)$_POST['change_role_user_id'];
        $new_role = $_POST['new_role'];
        $current_admin_id = $_SESSION['user_id'];

        if ($user_id_to_change > 0 && ($new_role === 'admin' || $new_role === 'user') && $user_id_to_change != $current_admin_id) {
             $stmt = $conn->prepare("UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?");
             $stmt->bind_param("si", $new_role, $user_id_to_change);
             if ($stmt->execute()) {
                 set_message("User ID {$user_id_to_change}'s role updated to {$new_role}.", "success");
             } else {
                 set_message("Error updating user role: " . $stmt->error, "danger");
             }
             $stmt->close();
        } elseif ($user_id_to_change == $current_admin_id) {
             set_message("You cannot change your own role.", "danger");
        } else {
             set_message("Invalid user ID or role for change.", "danger");
        }
        redirect('admin/user_management.php');
    }


    // Fetch All Users (excluding maybe the current admin for safety?)
    $stmt = $conn->prepare("SELECT id, name, email, mobile, role, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    ?>

    <h1>User Management</h1>
    <?php display_message(); ?>
    <p>View, manage roles, and delete users.</p>
    <hr>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Role</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                        <td>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline-block;">
                                <input type="hidden" name="change_role_user_id" value="<?php echo $user['id']; ?>">
                                <select name="new_role" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()" <?php echo ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                    <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <small class="text-muted">(You)</small>
                                <?php endif; ?>
                            </form>
                        </td>
                        <td><?php echo date("Y-m-d H:i", strtotime($user['created_at'])); ?></td>
                        <td>
                            <!-- Edit User (Redirect to a separate edit page or use modal) -->
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning me-1" title="Edit User Details (Not Implemented Yet)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                  <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                  <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                </svg>
                            </a>
                             <!-- Reset Password (Needs implementation - maybe send reset link) -->
                             <button class="btn btn-sm btn-secondary me-1" disabled title="Reset Password (Not Implemented Yet)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16">
                                  <path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2zM2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                </svg>
                             </button>
                            <!-- Delete User -->
                            <?php if ($user['id'] != $_SESSION['user_id']): // Prevent self-delete button ?>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline-block;">
                                    <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete user <?php echo htmlspecialchars($user['name']); ?>? This will also delete their donations.');" title="Delete User">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                          <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
                                        </svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

     <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-secondary">Back to Admin Dashboard</a>
        <!-- Add button to create new user if needed -->
        <!-- <a href="create_user.php" class="btn btn-primary">Add New User</a> -->
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
