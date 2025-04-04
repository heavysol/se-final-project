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
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="fullName" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ashesi Email</label>
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
                        <tr>
                            <td>John Doe</td>
                            <td>john.doe@ashesi.edu.gh</td>
                            <td><span class="badge bg-primary">Student</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">Edit</button>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('userCreationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Frontend validation and AJAX submission logic here
            console.log('User Creation Submitted');
        });
    </script>
</body>
</html>