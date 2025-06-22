<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complaint Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: white !important;
            background-color: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #5a67d8, #6b46c1);
        }
    </style>
    <script>
        window.API_URL = '{{ config('app.api_url') }}';
    </script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white"><i class="fas fa-headset"></i> CMS</h4>
                        <small class="text-white-50">Complaint Management</small>
                    </div>
                    
                    <div class="user-info mb-4 text-center">
                        <div class="bg-white bg-opacity-10 rounded p-3">
                            <i class="fas fa-user-circle fa-2x text-white mb-2"></i>
                            <div class="text-white" id="user-name">Loading...</div>
                            <small class="text-white-50" id="user-role">Loading...</small>
                        </div>
                    </div>

                    <nav class="nav flex-column">
                        <a class="nav-link active" href="#" id="dashboard-link">
                            <i class="fas fa-dashboard me-2"></i> Dashboard
                        </a>
                        
                        <!-- Customer Menu -->
                        <div id="customer-menu" style="display: none;">
                            <a class="nav-link" href="#" id="my-complaints-link">
                                <i class="fas fa-list me-2"></i> My Complaints
                            </a>
                            <a class="nav-link" href="#" id="new-complaint-link">
                                <i class="fas fa-plus me-2"></i> New Complaint
                            </a>
                        </div>

                        <!-- Admin Menu -->
                        <div id="admin-menu" style="display: none;">
                            <a class="nav-link" href="#" id="all-complaints-link">
                                <i class="fas fa-list-alt me-2"></i> All Complaints
                            </a>
                            <a class="nav-link" href="#" id="technicians-link">
                                <i class="fas fa-users me-2"></i> Technicians
                            </a>
                        </div>

                        <!-- Technician Menu -->
                        <div id="technician-menu" style="display: none;">
                            <a class="nav-link" href="#" id="assigned-complaints-link">
                                <i class="fas fa-tasks me-2"></i> Assigned Complaints
                            </a>
                        </div>

                        <hr class="bg-white bg-opacity-25">
                        <a class="nav-link" href="#" id="logout-link">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col">
                            <h2 id="page-title">Dashboard</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb" id="breadcrumb">
                                    <li class="breadcrumb-item active">Dashboard</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Content Area -->
                    <div id="content-area">
                        <!-- Dashboard will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('modals.complaint-form')
    @include('modals.assign-technician')
    @include('modals.update-status')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Global variables
        let authToken = localStorage.getItem('auth_token');
        let currentUser = JSON.parse(localStorage.getItem('user_data') || '{}');

        // Initialize app
        $(document).ready(function() {
            if (!authToken) {
                window.location.href = '/login';
                return;
            }

            loadUserInfo();
            setupMenus();
            loadDashboard();
            setupEventListeners();
        });

        // Load user information
        function loadUserInfo() {
            $('#user-name').text(currentUser.name || 'Unknown');
            $('#user-role').text(currentUser.role || 'Unknown');
            
            // Show appropriate menu based on role
            if (currentUser.role === 'customer') {
                $('#customer-menu').show();
            } else if (currentUser.role === 'admin') {
                $('#admin-menu').show();
            } else if (currentUser.role === 'technician') {
                $('#technician-menu').show();
            }
        }

        // Setup menus
        function setupMenus() {
            $('.nav-link').click(function(e) {
                e.preventDefault();
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
            });
        }
        
        // Setup event listeners
        function setupEventListeners() {
            $('#dashboard-link').click(() => loadDashboard());
            $('#my-complaints-link').click(() => loadMyComplaints());
            $('#new-complaint-link').click(() => showNewComplaintModal());
            $('#all-complaints-link').click(() => loadAllComplaints());        
            $('#technicians-link').click(() => loadTechnicians());
            $('#assigned-complaints-link').click(() => loadAssignedComplaints());
            $('#logout-link').click(() => logout());
        }

        // API Helper function
        function apiCall(url, method = 'GET', data = null) {
            return $.ajax({
                url: url,
                method: method,
                data: data,
                headers: {
                    'Authorization': 'Bearer ' + authToken,
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json'
            });
        }

        // Load Dashboard
        function loadDashboard() {
            $('#page-title').text('Dashboard');
            $('#breadcrumb').html('<li class="breadcrumb-item active">Dashboard</li>');
            
            let dashboardHtml = `
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title" id="total-complaints">0</h4>
                                        <p class="card-text">Total Complaints</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-list fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title" id="pending-complaints">0</h4>
                                        <p class="card-text">Pending</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title" id="not-available-complaints">0</h4>
                                        <p class="card-text">Not Available</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-ban fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title" id="resolved-complaints">0</h4>
                                        <p class="card-text">Resolved</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Activity</h5>
                            </div>
                            <div class="card-body" id="recent-activity">
                                <p class="text-muted">Loading recent activity...</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#content-area').html(dashboardHtml);
            loadDashboardStats();
        }

        // Load Dashboard Statistics
        function loadDashboardStats() {
            
            // let endpoint = currentUser.role === 'customer' ? 'my-complaints' : 'complaints';
            // endpoint = currentUser.role === 'technician' ? 'assigned-complaints' : endpoint;
            let endpoint;

            if (currentUser.role === 'technician') {
                endpoint = 'assigned-complaints';
            } else if (currentUser.role === 'customer') {
                endpoint = 'my-complaints';
            } else {
                endpoint = 'complaints';
            }            
            apiCall(window.API_URL + endpoint)
                .done(function(response) {
                    let complaints = response.complaints.data;
                    let total = complaints.length;
                    let pending = complaints.filter(c => c.status === 'open' || c.status === 'in_progress').length;
                    let notAvailable = complaints.filter(c => c.status === 'not_available').length;
                    let resolved = complaints.filter(c => c.status === 'resolved').length;
                    
                    $('#total-complaints').text(total);
                    $('#pending-complaints').text(pending);
                    $('#resolved-complaints').text(resolved);
                    $('#not-available-complaints').text(notAvailable);
                    
                    // Show recent complaints
                    let recentHtml = '';
                    complaints.slice(0, 5).forEach(complaint => {
                        recentHtml += `
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                <div>
                                    <strong>${complaint.title}</strong>
                                    <br><small class="text-muted">${complaint.description.substring(0, 50)}...</small>
                                </div>
                                <span class="badge ${getStatusBadgeClass(complaint.status)}">${formatStatusText(complaint.status)}</span>
                            </div>
                        `;
                    });
                    
                    $('#recent-activity').html(recentHtml || '<p class="text-muted">No recent activity</p>');
                })
                .fail(function() {
                    $('#recent-activity').html('<p class="text-danger">Failed to load dashboard data</p>');
                });
        }

        // Load Technicians
        function loadTechnicians() {
            $('#page-title').text('Technicians');
            $('#breadcrumb').html('<li class="breadcrumb-item active">Technicians</li>');
            
            apiCall(window.API_URL+'technicians/available')
                .done(function(response) {
                    renderTechniciansList(response);
                })
                .fail(function() {
                    $('#content-area').html('<div class="alert alert-danger">Failed to load technicians</div>');
                });
        }

        // Load My Complaints (Customer)
        function loadMyComplaints(page = 1, status = '') {
            $('#page-title').text('My Complaints');
            $('#breadcrumb').html('<li class="breadcrumb-item active">My Complaints</li>');
            
            let url = window.API_URL + `my-complaints?page=${page}`;
            if (status) url += `&status=${status}`;
            
            apiCall(url)
                .done(function(response) {
                    console.log(response);
                    renderComplaintsList(response, 'customer');
                })
                .fail(function() {
                    $('#content-area').html('<div class="alert alert-danger">Failed to load complaints</div>');
                });
        }

        // Load All Complaints (Admin)
        function loadAllComplaints(page = 1, status = '') {
            $('#page-title').text('All Complaints');
            $('#breadcrumb').html('<li class="breadcrumb-item active">All Complaints</li>');
            
            let url = window.API_URL+ `complaints?page=${page}`;
            if (status) url += `&status=${status}`;
            
            apiCall(url)
                .done(function(response) {
                    renderComplaintsList(response, 'admin');
                })
                .fail(function() {
                    $('#content-area').html('<div class="alert alert-danger">Failed to load complaints</div>');
                });
        }

        // Load Assigned Complaints (Technician)
        function loadAssignedComplaints(page = 1, status = '') {
            $('#page-title').text('Assigned Complaints');
            $('#breadcrumb').html('<li class="breadcrumb-item active">Assigned Complaints</li>');
            
            let url = window.API_URL+`assigned-complaints?page=${page}`;
            if (status) url += `&status=${status}`;
            
            apiCall(url)
                .done(function(response) {
                    renderComplaintsList(response, 'technician');
                })
                .fail(function() {
                    $('#content-area').html('<div class="alert alert-danger">Failed to load complaints</div>');
                });
        }

        function renderTechniciansList(response) {
            console.log('response',response);  
            let technicians = response.technicians;
            let html = '';
            
            technicians.forEach(technician => {
                html += `
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">${technician.name}</h5>
                            <p class="card-text">Email: ${technician.email}</p>                            
                        </div>
                    </div>
                `;
            });
            
            $('#content-area').html(html);
        }

        function renderComplaintsList(response, userType) {
        let complaints = response.complaints;
        let currentStatus = $('#status-filter').val() || ''; // Get current filter value
        
        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    ${userType === 'customer' ? '<button class="btn btn-primary" onclick="showNewComplaintModal()"><i class="fas fa-plus"></i> New Complaint</button>' : ''}
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <select class="form-select w-auto" id="status-filter" onchange="filterByStatus(this.value)">
                            <option value="" ${currentStatus === '' ? 'selected' : ''}>All Status</option>
                            <option value="open" ${currentStatus === 'open' ? 'selected' : ''}>Open</option>
                            <option value="in_progress" ${currentStatus === 'in_progress' ? 'selected' : ''}>In Progress</option>
                            <option value="not_available" ${currentStatus === 'not_available' ? 'selected' : ''}>Not Available</option>
                            <option value="resolved" ${currentStatus === 'resolved' ? 'selected' : ''}>Resolved</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
        
        // Check if complaints data exists and has items
        if (!complaints.data || complaints.data.length === 0) {
            html += `
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Complaints Found</h5>
                                <p class="text-muted">
                                    ${currentStatus ? `No complaints found with status "${formatStatusText(currentStatus)}".` : 'No complaints available at the moment.'}
                                </p>
                                ${currentStatus ? '<button class="btn btn-outline-primary" onclick="clearFilter()">Clear Filter</button>' : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            html += '<div class="row">';
            
            complaints.data.forEach(complaint => {
                html += `
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">${complaint.title}</h6>
                                <span class="badge ${getStatusBadgeClass(complaint.status)}">${formatStatusText(complaint.status)}</span>
                            </div>
                            <div class="card-body">
                                <p class="card-text">${complaint.description}</p>
                                <div class="row text-muted small">
                                    <div class="col-6">
                                        <strong>Customer:</strong> ${complaint.customer.name}
                                    </div>
                                    <div class="col-6">
                                        <strong>Created:</strong> ${formatDate(complaint.created_at)}
                                    </div>
                                </div>
                                ${complaint.technician ? `
                                    <div class="row text-muted small mt-1">
                                        <div class="col-12">
                                            <strong>Technician:</strong> ${complaint.technician.name}
                                        </div>
                                    </div>
                                ` : ''}
                                
                                <div class="mt-3">
                                    ${getActionButtons(complaint, userType)}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            
            // Add pagination only if there are complaints
            html += renderPagination(complaints);
        }
        
        $('#content-area').html(html);
    }

        // Get action buttons based on user type and complaint status
        function getActionButtons(complaint, userType) {
            let buttons = '';
            
            if (userType === 'customer') {
                if (complaint.status === 'open') {
                    buttons += `<button class="btn btn-sm btn-danger me-2" onclick="deleteComplaint(${complaint.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>`;
                }
            } else if (userType === 'admin') {
                if (!complaint.technician_id) {
                    buttons += `<button class="btn btn-sm btn-warning me-2" onclick="showAssignTechnicianModal(${complaint.id})">
                        <i class="fas fa-user-plus"></i> Assign
                    </button>`;
                }
            } else if (userType === 'technician') {
                if (complaint.status !== 'resolved') {
                    buttons += `<button class="btn btn-sm btn-success me-2" onclick="showUpdateStatusModal(${complaint.id}, '${complaint.status}')">
                        <i class="fas fa-edit"></i> Update Status
                    </button>`;
                }
            }
            
            return buttons;
        }

        // Render pagination
        function renderPagination(paginationData) {
            if (paginationData.last_page <= 1) return '';
            
            let html = '<nav aria-label="Complaints pagination"><ul class="pagination justify-content-center">';
            
            // Previous button
            if (paginationData.prev_page_url) {
                html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadCurrentView(${paginationData.current_page - 1})">Previous</a>
                </li>`;
            }
            
            // Page numbers
            for (let i = 1; i <= paginationData.last_page; i++) {
                html += `<li class="page-item ${i === paginationData.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadCurrentView(${i})">${i}</a>
                </li>`;
            }
            
            // Next button
            if (paginationData.next_page_url) {
                html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadCurrentView(${paginationData.current_page + 1})">Next</a>
                </li>`;
            }
            
            html += '</ul></nav>';
            return html;
        }

        // Load current view with pagination
        function loadCurrentView(page) {
            let currentStatus = $('#status-filter').val() || '';
            
            if (currentUser.role === 'customer') {
                loadMyComplaints(page, currentStatus);
            } else if (currentUser.role === 'admin') {
                loadAllComplaints(page, currentStatus);
            } else if (currentUser.role === 'technician') {
                loadAssignedComplaints(page, currentStatus);
            }
        }

        // Filter by status
        function filterByStatus(status) {
            loadCurrentView(1); // Reset to first page when filtering
        }

        // Clear filter function
        function clearFilter() {
            $('#status-filter').val('');
            loadCurrentView(1);
        }

        // Show New Complaint Modal
        function showNewComplaintModal() {
            $('#complaintModal').modal('show');
        }

        // Show Assign Technician Modal
        function showAssignTechnicianModal(complaintId) {
            $('#assignModal').data('complaint-id', complaintId);
            
            // Load available technicians
            apiCall( window.API_URL + 'technicians/available')
                .done(function(response) {
                    let options = '<option value="">Select Technician</option>';
                    response.technicians.forEach(tech => {
                        options += `<option value="${tech.id}">${tech.name} (${tech.email})</option>`;
                    });
                    $('#technician-select').html(options);
                    $('#assignModal').modal('show');
                })
                .fail(function() {
                    alert('Failed to load technicians');
                });
        }

        // Show Update Status Modal
        function showUpdateStatusModal(complaintId, currentStatus) {
            $('#statusModal').data('complaint-id', complaintId);
            $('#status-select').val(currentStatus);
            $('#statusModal').modal('show');
        }

        // Submit new complaint
        function submitComplaint() {
            let formData = {
                title: $('#complaint-title').val(),
                description: $('#complaint-description').val()
            };
            
            apiCall(window.API_URL+'complaints', 'POST', JSON.stringify(formData))
                .done(function(response) {
                    $('#complaintModal').modal('hide');
                    $('#complaint-form')[0].reset();
                    alert('Complaint submitted successfully!');
                    loadMyComplaints();
                })
                .fail(function() {
                    alert('Failed to submit complaint');
                });
        }

        // Assign technician
        function assignTechnician() {
            let complaintId = $('#assignModal').data('complaint-id');
            let technicianId = $('#technician-select').val();
            
            if (!technicianId) {
                alert('Please select a technician');
                return;
            }
            
            let formData = {
                technician_id: parseInt(technicianId)
            };
            
            apiCall( window.API_URL+`complaints/${complaintId}/assign`, 'POST', JSON.stringify(formData))
                .done(function(response) {
                    $('#assignModal').modal('hide');
                    alert('Technician assigned successfully!');
                    loadAllComplaints();
                })
                .fail(function() {
                    alert('Failed to assign technician');
                });
        }

        // Update complaint status
        function updateComplaintStatus() {
            let complaintId = $('#statusModal').data('complaint-id');
            let status = $('#status-select').val();
            
            let formData = {
                status: status
            };
            
            apiCall(window.API_URL+`complaints/${complaintId}/status`, 'PATCH', JSON.stringify(formData))
                .done(function(response) {
                    $('#statusModal').modal('hide');
                    alert('Status updated successfully!');
                    loadAssignedComplaints();
                })
                .fail(function() {
                    alert('Failed to update status');
                });
        }

        // Delete complaint
        function deleteComplaint(complaintId) {
            if (!confirm('Are you sure you want to delete this complaint?')) {
                return;
            }
            
            apiCall(window.API_URL+ `complaints/${complaintId}`, 'DELETE')
                .done(function(response) {
                    alert('Complaint deleted successfully!');
                    loadMyComplaints();
                })
                .fail(function() {
                    alert('Failed to delete complaint');
                });
        }

        // Logout
        function logout() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
            window.location.href = '/login';
        }

        // Utility functions
        function getStatusBadgeClass(status) {
            switch(status) {
                case 'open': return 'bg-primary';
                case 'in_progress': return 'bg-warning';
                case 'not_available': return 'bg-warning';
                case 'resolved': return 'bg-success';
                default: return 'bg-secondary';
            }
        }
        function formatStatusText(status) {
            switch(status) {
                case 'open': return 'Open';
                case 'in_progress': return 'In Progress';                
                case 'not_available': return 'Not Available';
                case 'resolved': return 'Resolved';
                default: return status.charAt(0).toUpperCase() + status.slice(1);
            }
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        }
    </script>
</body>
</html>