<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

// Redirect to dashboard if user is ALREADY logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    // Validate CSRF Token
    if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'Invalid security token. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Fetch user from database using ONLY username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
                // Regenerate Session ID for security
                session_regenerate_id(true);

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'] ?? 'user';

                header("Location: index.php");
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database connection error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Income & Service Management Dashboard</title>
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --bg-body: #f4f6fb;
            --bg-card: #ffffff;
            --text-color: #0f172a;
            --border-color: #e2e8f0;
        }

        [data-bs-theme="dark"] {
            --bg-body: #0b0f19;
            --bg-card: #151d2a;
            --text-color: #f8fafc;
            --border-color: #1e293b;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-color);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.25s ease, color 0.25s ease;
            padding: 1.5rem;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            background: var(--bg-card);
            padding: 2.25rem;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
        }

        .theme-toggle-corner {
            position: fixed;
            top: 20px;
            right: 20px;
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
        }
    </style>
</head>
<body>

    <!-- Theme Toggle Corner Button -->
    <div class="theme-toggle-corner">
        <button id="themeToggleBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-2">
            <i id="themeIcon" class="bi bi-moon-stars-fill"></i>
            <span id="themeLabel">Dark Mode</span>
        </button>
    </div>

    <!-- Login Container Card -->
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="brand-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <h4 class="fw-bold mb-1">Welcome Back</h4>
            <p class="text-muted small">Sign in to access Income Control Panel</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show small py-2.5 rounded-3 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close py-2.5" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="mb-3">
                <label for="username" class="form-label small fw-bold">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" id="username" class="form-control border-start-0 ps-0" placeholder="e.g. admin" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control border-start-0 border-end-0 ps-0" placeholder="••••••••" required>
                    <button type="button" class="btn btn-outline-secondary border-start-0 bg-transparent" id="togglePasswordBtn" style="border-color: var(--border-color);">
                        <i id="togglePasswordIcon" class="bi bi-eye text-muted"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold py-2.5 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border: none;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dark/Light Theme Switcher
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeLabel = document.getElementById('themeLabel');
        const htmlElement = document.documentElement;

        function applyTheme(theme) {
            htmlElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);

            if (theme === 'dark') {
                themeIcon.className = 'bi bi-sun-fill text-warning';
                themeLabel.textContent = 'Light Mode';
                themeToggleBtn.classList.replace('btn-outline-secondary', 'btn-outline-warning');
            } else {
                themeIcon.className = 'bi bi-moon-stars-fill text-dark';
                themeLabel.textContent = 'Dark Mode';
                themeToggleBtn.classList.replace('btn-outline-warning', 'btn-outline-secondary');
            }
        }

        const savedTheme = localStorage.getItem('theme') || 'light';
        applyTheme(savedTheme);

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });

        // Show/Hide Password Toggle
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const togglePasswordIcon = document.getElementById('togglePasswordIcon');

        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            togglePasswordIcon.className = isPassword ? 'bi bi-eye-slash text-primary' : 'bi bi-eye text-muted';
        });
    </script>
</body>
</html>