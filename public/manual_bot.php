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
$waba_id = '';
$number_id = '';
$number = '';
$bot_name = '';
$status_text = '';
$users_id = '';
$company_id = '';

// Additional fixed values
$wa_bot_status = 1;
$wa_bot_type_url = "cloud_wa";
$wa_bot_webhook_url = getenv('WEBHOOK_URL') ?: '';
$base_url = '';
$token = getenv('META_TOKEN') ?: '';
$wa_bot_free_session = 1000;

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $errors = [];
    
    $waba_id = trim($_POST['waba_id'] ?? '');
    $users_id = trim($_POST['users_id'] ?? '');
    $company_id = trim($_POST['company_id'] ?? '');
    
    // Validate WABA ID
    if (empty($waba_id)) {
        $errors[] = 'WABA ID is required';
    } elseif (!preg_match('/^\d+$/', $waba_id)) {
        $errors[] = 'WABA ID must contain only numbers';
    }
    
    if (empty($users_id)) {
        $errors[] = 'Users ID is required';
    }

    if (empty($company_id)) {
        $errors[] = 'Company ID is required';
    }
    
    if (empty($errors)) {

        $curl_check = curl_init();

        curl_setopt_array($curl_check, array(
        CURLOPT_URL => 'https://graph.facebook.com/'.getenv('META_VERSION').'/'.$waba_id.'/phone_numbers',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$token
        ),
        ));

        $responseDetail = curl_exec($curl_check);
        curl_close($curl_check);
        $responseDetail = json_decode($responseDetail, true);

        if(isset($responseDetail["error"])) #handle error
        {
            $message = $responseDetail["error"]["message"];
            $messageType = 'error';
        }
        else
        {
            $number_id = isset($responseDetail["data"][0]["id"]) ? $responseDetail["data"][0]["id"] : "";
            $bot_name = isset($responseDetail["data"][0]["verified_name"]) ? $responseDetail["data"][0]["verified_name"] : "";
            $number = isset($responseDetail["data"][0]["display_phone_number"]) ? $responseDetail["data"][0]["display_phone_number"] : "";
            $status_text = isset($responseDetail["data"][0]["code_verification_status"]) ? $responseDetail["data"][0]["code_verification_status"] : "";
            
            $base_url = 'https://graph.facebook.com/'.getenv('META_VERSION').'/'.$number_id.'/messages';
    
            // Connect to database
            $mysqli = new mysqli($db_config['host'], $db_config['user'], $db_config['password'], $db_config['database']);
            
            // Check connection
            if ($mysqli->connect_error) {
                $message = "Database connection failed: " . $mysqli->connect_error;
                $messageType = 'error';
            } else {
                // Generate UUID and prepare data
                $wa_bot_uuid = generateUUID();
                $date_created = date('Y-m-d H:i:s');
    
                // Prepare the statement
                $stmt = $mysqli->prepare("
                    INSERT INTO whatsapp_bot (
                        wa_bot_uuid,
                        wa_bot_status,
                        wa_bot_type_url,
                        wa_bot_number,
                        wa_bot_number_id,
                        wa_bot_name,
                        wa_bot_base_url,
                        wa_waba_id,
                        wa_bot_webhook_url,
                        wa_bot_users_id,
                        company_id,
                        created_at,
                        encoded_users_token,
                        wa_bot_free_session,
                        wa_bot_status_text,
                        token_waba
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                // Check if prepare failed
                if (!$stmt) {
                    $message = "Prepare failed: " . $mysqli->error;
                    $messageType = 'error';
                    error_log("Prepare failed: " . $mysqli->error);
                } else {
                    // Bind parameters
                    if (!$stmt->bind_param(
                        "sisssssssiississ",
                        $wa_bot_uuid,
                        $wa_bot_status,
                        $wa_bot_type_url,
                        $number,
                        $number_id,
                        $bot_name,
                        $base_url,
                        $waba_id,
                        $wa_bot_webhook_url,
                        $users_id,
                        $company_id,
                        $date_created,
                        $token,
                        $wa_bot_free_session,
                        $status_text,
                        $token
                    )) {
                        $message = "Bind failed: " . $stmt->error;
                        $messageType = 'error';
                        error_log("Bind failed: " . $stmt->error);
                    } else {
                        if ($stmt->execute()) {
                            $insert_id = $stmt->insert_id;
    
                            $message = "Bot saved successfully! Insert ID: " . $stmt->insert_id . " (UUID: " . $wa_bot_uuid . ")";
                            $messageType = 'success';
                            
                            // First CURL: Subscribe Apps
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                                CURLOPT_URL => 'https://graph.facebook.com/'.getenv('META_VERSION').'/'.$waba_id.'/subscribed_apps',
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'POST',
                                CURLOPT_HTTPHEADER => array(
                                    'Authorization: Bearer '.$token
                                ),
                            ));
                            
                            $responseSubscribe = curl_exec($curl);
                            curl_close($curl);
                            $responseSubscribe = json_decode($responseSubscribe, true);
    
                            if(isset($responseSubscribe['error']) && $responseSubscribe['error'] != '')
                            {
                                $deleteStmt = $mysqli->prepare("DELETE FROM whatsapp_bot WHERE id = ?");
                                $deleteStmt->bind_param("i", $insert_id);
                                $deleteStmt->execute();
                                $deleteStmt->close();
    
                                $message = $responseSubscribe["error"]["message"];
                                $messageType = 'error';
                            }
                            
                            // Second CURL: Register Number
                            $curl2 = curl_init();
                            curl_setopt_array($curl2, array(
                                CURLOPT_URL => 'https://graph.facebook.com/'.getenv('META_VERSION').'/'.$number_id.'/register',
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'POST',
                                CURLOPT_POSTFIELDS =>'{
                                    "messaging_product": "whatsapp",
                                    "pin": "175758"
                                }',
                                CURLOPT_HTTPHEADER => array(
                                    'Content-Type: application/json',
                                    'Authorization: Bearer '.$token
                                ),
                            ));
                            
                            $responseRegister = curl_exec($curl2);
                            curl_close($curl2);
                            $responseRegister = json_decode($responseRegister, true);
    
                            if(isset($responseRegister['error']) && $responseRegister['error'] != '')
                            {
                                $deleteStmt = $mysqli->prepare("DELETE FROM whatsapp_bot WHERE id = ?");
                                $deleteStmt->bind_param("i", $insert_id);
                                $deleteStmt->execute();
                                $deleteStmt->close();
    
                                $message = $responseRegister["error"]["message"];
                                $messageType = 'error';
                            }
            
                        } else {
                            $message = "Database error: " . $stmt->error;
                            $messageType = 'error';
                        }
    
                        $stmt->close();
                    
                    }
                }
    
                $mysqli->close();
            }
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Configuration Form</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #25d366;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        input[type="text"]:focus,
        input[type="tel"]:focus,
        select:focus {
            outline: none;
            border-color: #25d366;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background-color: #25d366;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        button:hover {
            background-color: #128c7e;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
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
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #28a745;
            margin-right: 5px;
        }
        
        .help-text {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
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
        <h1>WhatsApp Configuration</h1>
        
        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="configForm">
            <div class="form-group">
                <label for="waba_id">WABA ID</label>
                <input type="text" id="waba_id" name="waba_id" placeholder="123456789123456" value="<?php echo htmlspecialchars($waba_id); ?>" required>
                <div class="help-text">WhatsApp Business Account ID (numbers only)</div>
            </div>
            
            <div class="form-group">
                <label for="users_id">USERS ID</label>
                <input type="text" id="users_id" name="users_id" placeholder="0" value="<?php echo htmlspecialchars($users_id); ?>" required>
            </div>


            <div class="form-group">
                <label for="company_id">COMPANY ID</label>
                <input type="text" id="company_id" name="company_id" placeholder="0" value="<?php echo htmlspecialchars($company_id); ?>" required>
            </div>
            
            <button type="submit">Save Configuration</button>
        </form>
        
        <div class="logout-link">
            <a href="#" onclick="logout()">Logout</a>
        </div>
    </div>
    
    <script>
        // Client-side validation
        document.getElementById('configForm').addEventListener('submit', function(e) {
            const wabaId = document.getElementById('waba_id').value;
            
            // Validate WABA ID (numbers only)
            if (!/^\d+$/.test(wabaId)) {
                alert('WABA ID must contain only numbers');
                e.preventDefault();
                return;
            }
        });

        // Only allow numbers for Users ID and Company ID
        document.getElementById('users_id').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
        
        document.getElementById('company_id').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
        
        // Logout function - forces browser to forget credentials
        function logout() {
            // This attempts to logout by sending invalid credentials
            var xhr = new XMLHttpRequest();
            xhr.open('GET', window.location.href, true, 'logout', 'logout');
            xhr.send();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    // Reload the page which will trigger the auth prompt again
                    window.location.reload();
                }
            };
        }
    </script>
</body>
</html>
