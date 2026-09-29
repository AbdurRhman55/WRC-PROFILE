<?php
require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];

            $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            if ($remember) {
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), time() + (86400 * 30), $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WRC Admin - Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brandRed: {
              DEFAULT: '#ca0000',
              hover: '#a30000',
              light: '#fef2f2',
            },
            ink: {
              900: '#0E1F27',
              800: '#152C36',
              700: '#1D3A47'
            },
          },
          fontFamily: {
            display: ['Fraunces', 'serif'],
            sans: ['Inter', 'sans-serif'],
          },
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .font-heading { font-family: 'Fraunces', serif; }
    .login-bg {
      background: linear-gradient(135deg, #0E1F27 0%, #152C36 50%, #1D3A47 100%);
    }
    .login-card {
      backdrop-filter: blur(12px);
      background: rgba(255,255,255,0.95);
    }
    .brand-accent {
      background: linear-gradient(135deg, #ca0000 0%, #a30000 100%);
    }
  </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-md">
    <div class="login-card rounded-3xl shadow-2xl p-8 sm:p-10">
      <!-- Logo -->
      <div class="text-center mb-8">
        <a href="../index.php" class="inline-flex items-center gap-3 mb-4">
          <img src="../assets/images/logo.png" alt="WRC logo" class="w-14 h-14 object-contain rounded-xl bg-white p-1 border border-slate-200 shadow-sm" />
        </a>
        <h1 class="text-2xl font-bold text-slate-900 font-heading">WRC Admin Panel</h1>
        <p class="text-slate-500 text-sm mt-1">Sign in to manage your content</p>
      </div>

      <!-- Error Message -->
      <?php if ($error): ?>
      <div class="mb-6 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form method="POST" action="" class="space-y-5">
        <div>
          <label for="username" class="block text-sm font-semibold text-slate-700 mb-1.5">Username or Email</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
              <i class="fa-solid fa-user text-sm"></i>
            </span>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required
              class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all placeholder:text-slate-400"
              placeholder="Enter username or email">
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
              <i class="fa-solid fa-lock text-sm"></i>
            </span>
            <input type="password" id="password" name="password" required
              class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all placeholder:text-slate-400"
              placeholder="Enter password">
          </div>
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/30">
            <span class="text-sm text-slate-600">Remember me</span>
          </label>
        </div>

        <button type="submit"
          class="w-full brand-accent hover:bg-brandRed-hover text-white font-bold text-sm py-3.5 rounded-xl shadow-lg shadow-brandRed/20 transition-all flex items-center justify-center gap-2">
          <i class="fa-solid fa-right-to-bracket"></i> Sign In
        </button>
      </form>
    </div>

    <p class="text-center text-slate-500 text-xs mt-6">&copy; <?= date('Y') ?> World Relief Committee. All rights reserved.</p>
  </div>

</body>
</html>
