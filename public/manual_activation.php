<?php
// Load environment variables from .env file
function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        throw new Exception('.env file not found at: ' . $path);
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse line
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                $value = $matches[1];
            }
            
            // Set environment variable
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

// Define the path to .env file
$envPath = '/var/www/html/rambuchat/.env';

// Load .env file
try {
    loadEnv($envPath);
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

// Database configuration from environment
$db_config = [
    'host' => getenv('DB_HOST') ?: '',
    'user' => getenv('DB_USERNAME') ?: '',
    'password' => getenv('DB_PASSWORD') ?: '',
    'database' => getenv('DB_DATABASE') ?: ''
];

// Basic Authentication
$valid_users = [
    'admin' => base64_encode(getenv('AUTHUSER').':'.getenv('AUTHPASSWORD'))
];

// Check authentication
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

// Handle different server configurations
if (empty($auth_header) && isset($_SERVER['PHP_AUTH_USER'])) {
    $auth_header = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
}

$authenticated = false;

if (!empty($auth_header) && preg_match('/Basic\s+(.*)$/i', $auth_header, $matches)) {
    $credentials = $matches[1];
    
    // Check if credentials match any valid user
    foreach ($valid_users as $user => $valid_credentials) {
        if ($credentials === $valid_credentials) {
            $authenticated = true;
            break;
        }
    }
}

// If not authenticated, send auth headers
if (!$authenticated) {
    header('WWW-Authenticate: Basic realm="WhatsApp Configuration"');
    header('HTTP/1.0 401 Unauthorized');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>401 Unauthorized</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            h1 { color: #e74c3c; }
        </style>
    </head>
    <body>
        <h1>401 Unauthorized</h1>
        <p>Authentication required to access this page.</p>
    </body>
    </html>';
    exit;
}

// Generate UUID v4
function generateUUID() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Initialize default values
$email = '';
$expired_at = '';

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $errors = [];
    
    $email = trim($_POST['email'] ?? '');
    $expired_at = trim($_POST['expired_at'] ?? '');
    
    // Email validation
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    
    // Expired at validation
    if (empty($expired_at)) {
        $errors[] = 'Expired At is required.';
    } elseif (strtotime($expired_at) < strtotime(date('Y-m-d H:i:s'))) {
        $errors[] = 'Expired At must be today or a future date.';
    }

    if (empty($errors)) {
        $mysqli = new mysqli($db_config['host'], $db_config['user'], $db_config['password'], $db_config['database']);

        if ($mysqli->connect_error) {
            $message = "Database connection failed: " . $mysqli->connect_error;
            $messageType = 'error';
        } else {
            $stmt = $mysqli->prepare("SELECT company_id FROM users WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $message = 'No user found with the provided email.';
                $messageType = 'error';
            } else {
                $row = $result->fetch_assoc();
                $company_id = $row['company_id'];

                if (!$company_id) {
                    $message = 'No company ID associated with this user.';
                    $messageType = 'error';
                } else {
                    $stmt = $mysqli->prepare("UPDATE company SET expired_at = ? WHERE id = ?");
                    $stmt->bind_param('si', $expired_at, $company_id);
                    if ($stmt->execute()) {
                        $message = 'Company expiration updated successfully.';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to update company expiration.';
                        $messageType = 'error';
                    }
                }
            }
            $stmt->close();
            $mysqli->close();
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Setting Expired Company</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
      background-color: #f0f2f5;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .container {
      background-color: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      max-width: 500px;
      width: 100%;
    }

    h1 {
      text-align: center;
      color: #333;
      font-size: 24px;
      margin-bottom: 25px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      font-weight: 600;
      display: block;
      margin-bottom: 8px;
      color: #444;
    }

    input[type="text"],
    input[type="datetime-local"] {
      width: 100%;
      padding: 12px 14px;
      font-size: 16px;
      border: 1.5px solid #ccc;
      border-radius: 8px;
      background-color: #fdfdfd;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    input:focus {
      border-color: #25d366;
      box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.15);
      outline: none;
    }

    input[type="datetime-local"]::-webkit-calendar-picker-indicator {
      filter: invert(0.5);
      cursor: pointer;
    }

    button {
      width: 100%;
      padding: 14px;
      background-color: #25d366;
      border: none;
      color: white;
      font-size: 16px;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #128c7e;
    }

    .alert {
      padding: 12px 16px;
      margin-bottom: 20px;
      border-radius: 8px;
      font-weight: 500;
    }

    .alert.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .logout-link {
      text-align: center;
      margin-top: 20px;
    }

    .logout-link a {
      color: #25d366;
      text-decoration: none;
      font-weight: 600;
    }

    .logout-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Company Configuration</h1>

    <?php if ($message): ?>
      <div class="alert <?php echo $messageType; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" id="configForm">
      <div class="form-group">
        <label for="email">Email</label>
        <input
          type="text"
          id="email"
          name="email"
          placeholder="admin@admin.com"
          value="<?php echo htmlspecialchars($email); ?>"
          required
        />
      </div>

      <div class="form-group">
        <label for="expired_at">Expired At</label>
        <input
          type="datetime-local"
          id="expired_at"
          name="expired_at"
          value="<?php echo htmlspecialchars($expired_at); ?>"
          required
        />
      </div>

      <button type="submit">Save Configuration</button>
    </form>

    <div class="logout-link">
      <a href="#" onclick="logout()">Logout</a>
    </div>
  </div>

  <script>
    function logout() {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', window.location.href, true, 'logout', 'logout');
      xhr.send();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          window.location.reload();
        }
      };
    }
  </script>
</body>
</html>
