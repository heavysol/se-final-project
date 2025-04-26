<?php
session_start();
include('../../../db/config.php'); // Ensure the path is correct

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/admin-styles.css">
    <style>
        .admin-sidebar {
            background: var(--primary-color);
            color: var(--text-light);
            width: 250px;
            position: fixed;
            height: 100vh;
            padding-top: 0;
            box-shadow: 4px 0 10px var(--shadow-color);
        }
        
        .admin-sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .admin-sidebar-header h3 {
            color: var(--text-light);
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .admin-sidebar-header .small {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin: 5px 0 0 0;
        }
        
        .admin-sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-sidebar-menu li a {
            color: var(--text-light);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        
        .admin-sidebar-menu li a:hover {
            background-color: var(--hover-color);
        }
        
        .admin-sidebar-menu li a.active {
            background-color: var(--active-color);
        }
        
        .admin-sidebar-menu li a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .admin-sidebar-menu li:last-child {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 20px;
        }

        .admin-main-content {
            margin-left: 250px;
            padding: 2rem;
            background-color: var(--background-color);
            min-height: 100vh;
        }

        .user-card {
            background-color: var(--background-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px var(--shadow-color);
        }

        .user-role {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .role-admin {
            background-color: var(--primary-color);
            color: var(--text-light);
        }

        .role-organizer {
            background-color: var(--secondary-color);
            color: var(--text-primary);
        }

        .role-student {
            background-color: var(--accent-color);
            color: var(--text-light);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-light);
        }

        .btn-primary:hover {
            background-color: var(--hover-color);
            border-color: var(--hover-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: var(--danger-hover);
            border-color: var(--danger-hover);
        }

        .text-muted {
            color: var(--text-secondary) !important;
        }

        h2 {
            color: var(--text-primary);
        }

        .table {
            background-color: var(--background-color);
            color: var(--text-primary);
        }

        .table thead th {
            background-color: var(--secondary-color);
            color: var(--text-primary);
            border-bottom: 2px solid var(--border-color);
        }

        .table tbody tr {
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background-color: var(--hover-color);
        }

        .modal-content {
            background-color: var(--background-color);
            color: var(--text-primary);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
        }

        .form-control {
            background-color: var(--background-color);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .form-control:focus {
            background-color: var(--background-color);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-color-rgb), 0.25);
        }
    </style>
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3>Campus Events</h3>
            <div class="small">Admin Dashboard</div>
        </div>
        <ul class="admin-sidebar-menu">
            <li><a href="../../../index.php">
                <i class="bi bi-house-door"></i> Home
            </a></li>
            <li><a href="admin_dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a></li>
            <li><a href="event_management.php">
                <i class="bi bi-calendar-event"></i> Events Management
            </a></li>
            <li><a href="user_management.php" class="active">
                <i class="bi bi-people"></i> User Management
            </a></li>
            <li><a href="analytics.php">
                <i class="bi bi-graph-up"></i> Analytics
            </a></li>
            <li><a href="notifications.php">
                <i class="bi bi-bell"></i> Notifications
            </a></li>
            <li><a href="../../logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="admin-main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h2>User Management</h2>
                    <p class="text-muted">Manage system users and their roles</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mb-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        + Create New User
                    </button>
                </div>
                <div class="col-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userCreationForm">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="firstName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="lastName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="organizer">Organizer</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Create User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="firstName" id="editFirstName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="lastName" id="editLastName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" id="editRole" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="organizer">Organizer</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to load users and display them in the table
        document.addEventListener("DOMContentLoaded", loadUsers);

        function loadUsers() {
            fetch("../../../actions/user_management_action.php")
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById("userTableBody");
                    tbody.innerHTML = "";
                    data.forEach(user => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${user.first_name} ${user.last_name}</td>
                                <td>${user.email}</td>
                                <td><span class="badge bg-primary">${user.role}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="editUser(${user.user_id})">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.user_id})">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                });
        }

        // Handle the form submission for creating a user
        document.getElementById('userCreationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append("action", "create");

            fetch("../../../actions/user_management_action.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    alert("User created successfully");
                    document.getElementById('userCreationForm').reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createUserModal'));
                    modal.hide();
                    loadUsers();
                } else {
                    alert("Error: " + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("An error occurred while creating the user. Please try again.");
            });
        });

        // Delete user
        function deleteUser(userId) {
            if (!confirm("Are you sure you want to delete this user?")) return;

            const formData = new FormData();
            formData.append("action", "delete");
            formData.append("user_id", userId);

            fetch("../../../actions/user_management_action.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("User deleted successfully");
                    loadUsers();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("An error occurred while deleting the user. Please try again.");
            });
        }

        // Edit user and show the modal with pre-filled data
        function editUser(userId) {
            fetch("../../../actions/user_management_action.php?user_id=" + userId)
                .then(res => res.json())
                .then(user => {
                    document.getElementById("editUserId").value = user.user_id;
                    document.getElementById("editFirstName").value = user.first_name;
                    document.getElementById("editLastName").value = user.last_name;
                    document.getElementById("editEmail").value = user.email;
                    document.getElementById("editRole").value = user.role;

                    const editModal = new bootstrap.Modal(document.getElementById("editUserModal"));
                    editModal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred while loading user data. Please try again.");
                });
        }

        // Handle the form submission for updating user information
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append("action", "update");

            fetch("../../../actions/user_management_action.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    alert("User updated successfully!");
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                    modal.hide();
                    loadUsers();
                } else {
                    alert("Error: " + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("An error occurred while updating the user. Please try again.");
            });
        });
    </script>
</body>
</html>