<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Complaint Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {            
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .register-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px 15px 0 0;
        }
    </style>
    <script>
        window.API_URL = '{{ config('app.api_url') }}';
    </script>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card register-card">
                    <div class="card-header register-header text-center py-4">
                        <h3><i class="fas fa-user-plus me-2"></i>Register</h3>
                        <p class="mb-0">Create your account</p>
                    </div>
                    <div class="card-body p-4">
                        <form id="register-form">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" placeholder="Enter your full name" class="form-control" id="name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email"  placeholder="Enter your email address" class="form-control" id="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                    <select class="form-select" id="role" required>
                                        <option value="">Select Role</option>
                                        <option value="customer">Customer</option>
                                        <option value="technician">Technician</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" placeholder="Enter your password" class="form-control" id="password" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" placeholder="Confirm your password" class="form-control" id="password_confirmation" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Register
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted">Already have an account?</small><br>
                            <a href="/login" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
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
            $('#register-form').submit(function(e) {
                e.preventDefault();
                register();
            });
        });

        function register() {
            let password = $('#password').val();
            let confirmPassword = $('#password_confirmation').val();
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }

            let registerData = {
                name: $('#name').val(),
                email: $('#email').val(),
                role: $('#role').val(),
                password: password,
                password_confirmation: confirmPassword
            };

            $.ajax({
                url: window.API_URL + 'register',
                method: 'POST',
                data: JSON.stringify(registerData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json'
            })
            .done(function(response) {
                alert('Registration successful!');
                // Store auth token and user data
                localStorage.setItem('auth_token', response.token);
                localStorage.setItem('user_data', JSON.stringify(response.user));
                // Redirect to dashboard
                window.location.href = '/dashboard';
                // window.location.href = '/login';
            })
            .fail(function(xhr) {
                let errorMessage = 'Registration failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                alert(errorMessage);
            });
        }
    </script>
</body>
</html>