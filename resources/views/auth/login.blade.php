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
        <div class="row" style="justify-content: center ;align-items: center;min-height: 100vh">
            <!-- Sidebar -->
            <div class="col-md-6 col-lg-4">
                <div class="card login-card">
                    <div class="card-header login-header text-center py-4">
                        <h3><i class="fas fa-headset me-2"></i>CMS Login</h3>
                        <p class="mb-0">Complaint Management System</p>
                    </div>
                    <div class="card-body p-4">
                        <form id="login-form">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" placeholder="Enter your email address" class="form-control" id="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" placeholder="Enter your password" class="form-control" id="password" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted">Don't have an account?</small><br>
                            <a href="/register" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Check if already logged in
            if (localStorage.getItem('auth_token')) {
                window.location.href = '/dashboard';
            }

            $('#login-form').submit(function(e) {
                e.preventDefault();
                login();
            });
        });

        function login() {
            let loginData = {
                email: $('#email').val(),
                password: $('#password').val()
            };

            $.ajax({
                url: window.API_URL + 'login',
                method: 'POST',
                data: JSON.stringify(loginData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json'
            })
            .done(function(response) {
                // Store auth token and user data
                localStorage.setItem('auth_token', response.token);
                localStorage.setItem('user_data', JSON.stringify(response.user));
                
                // Redirect to dashboard
                window.location.href = '/dashboard';
            })
            .fail(function(xhr) {
                let errorMessage = 'Login failed. Please check your credentials.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            });
        }
    </script>
</body>
</html>
