<?php
session_start();
include 'includes/db.php';

$error = "";
$selected_role = 'admin'; // Default role

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $selected_role = $_POST['role'] ?? 'admin';

    if (empty($email) || empty($password)) {
        $error = "Please fill in both fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $email, $selected_role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['name'];

                    switch ($user['role']) {
                        case 'admin': 
                            header("Location: admin/dashboard.php"); 
                            break;
                        case 'technician': 
                            header("Location: technician/dashboard.php"); 
                            break;
                        case 'staff': 
                            header("Location: staff/dashboard.php"); 
                            break;
                        default: 
                            $error = "Unknown user role.";
                    }
                    exit();
                } else {
                    $error = "Incorrect password for selected role.";
                }
            } else {
                $error = "User not found with this email/role combination.";
            }
            $stmt->close();
        } else {
            $error = "Database query failed.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OBN Maintenance System</title>
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: url('Images/back1.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(230, 18, 18, 0.2);
            overflow: hidden;
            backdrop-filter: blur(5px);
        }
        
        .login-header {
            padding: 30px;
            text-align: center;
            background: var(--primary);
            color: white;
        }
        
        .login-header img {
            max-width: 340px;
            max-height: 100px;
            margin-bottom: 25px;
        }
        
        .login-header h1 {
            font-size: 0px;
            margin-bottom: 0px;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .role-tabs {
            display: flex;
            margin-bottom: 25px;
            background: #f1f1f1;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .role-tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            cursor: pointer;
            font-weight: 600;
            color: var(--gray);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .role-tab.active {
            background: var(--primary);
            color: white;
        }
        
        .role-tab input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .error-alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .home-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
        }
        
        .home-link i {
            margin-right: 5px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                border-radius: 5px;
            }
            
            .login-header {
                padding: 20px;
            }
            
            .login-body {
                padding: 20px;
            }
            
            .role-tab {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="Images/h.jfif" alt="OBN Logo">
            <h1>OBN Digital Infrastructure </h1>
            <p>Login to your account</p>
        </div>
        
        <div class="login-body">
            <form method="POST" action="">
                <div class="role-tabs">
                    <label class="role-tab <?= $selected_role === 'admin' ? 'active' : '' ?>">
                        <input type="radio" name="role" value="admin" <?= $selected_role === 'admin' ? 'checked' : '' ?>>
                        Admin Login
                    </label>
                    <label class="role-tab <?= $selected_role === 'technician' ? 'active' : '' ?>">
                        <input type="radio" name="role" value="technician" <?= $selected_role === 'technician' ? 'checked' : '' ?>>
                        Technician Login
                    </label>
                    <label class="role-tab <?= $selected_role === 'staff' ? 'active' : '' ?>">
                        <input type="radio" name="role" value="staff" <?= $selected_role === 'staff' ? 'checked' : '' ?>>
                        Staff Login
                    </label>
                </div>
                
                <?php if ($error): ?>
                    <div class="error-alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" required 
                           placeholder="Enter your email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <a href="index.php" class="home-link">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </form>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.role-tab input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.role-tab').forEach(tab => {
                    tab.classList.remove('active');
                    if (tab.querySelector('input').checked) {
                        tab.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>