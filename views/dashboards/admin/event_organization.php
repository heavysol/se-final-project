<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizations Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Campus Organizations</h2>

        <!-- Organization Creation Modal -->
        <div class="modal fade" id="createOrgModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Organization</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="orgCreationForm">
                            <div class="mb-3">
                                <label class="form-label">Organization Name</label>
                                <input type="text" class="form-control" name="orgName" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="orgDescription" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" name="contactPerson">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" class="form-control" name="contactEmail">
                            </div>
                            <button type="submit" class="btn btn-primary">Create Organization</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organizations List Section -->
        <div class="row">
            <div class="col-12 mb-3">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createOrgModal">
                    + Create New Organization
                </button>
            </div>
            <div class="col-12">
                <div class="row" id="organizationsList">
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Ashesi Student Council</h5>
                                <p class="card-text">Student representative body</p>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-info">Active</span>
                                    <div>
                                        <button class="btn btn-sm btn-primary">View</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('orgCreationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Frontend validation and AJAX submission logic here
            console.log('Organization Creation Submitted');
        });
    </script>
</body>
</html>