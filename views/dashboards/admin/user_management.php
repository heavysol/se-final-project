<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">User Management</h2>

        <!-- User Creation Modal -->
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
                                <select class="form-select" name="role">
                                    <option>Student</option>
                                    <option>Event Organizer</option>
                                    <option>Admin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Temporary Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Create User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- User List Section -->
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
                if (result.status === 'success') {
                    alert("User created successfully");
                    document.getElementById('userCreationForm').reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createUserModal'));
                    modal.hide();
                    loadUsers();
                } else {
                    alert("Error: " + result.message);
                }
            });
        });

        function deleteUser(userId) {
            if (!confirm("Are you sure you want to delete this user?")) return;

            const formData = new FormData();
            formData.append("action", "delete");
            formData.append("user_id", userId);

            fetch("../../../actions/user_management_action.php", {
                method: "POST",
                body: formData
            }).then(res => res.json())
            .then(data => {
                if (data.status === "deleted") {
                    loadUsers();
                }
            });
        }

        function editUser(userId) {
            alert("Edit functionality coming soon.");
        }
    </script>
</body>
</html>
