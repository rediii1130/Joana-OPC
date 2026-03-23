<?php
// 1. DATABASE & SESSION CONFIGURATION
date_default_timezone_set('Asia/Manila');

// Database Credentials
$host = 'mysql-13f0ef48-deleonkeyt71-88b2.d.aivencloud.com'; 
$port = '10145';
$db   = 'defaultdb'; 
$user = 'avnadmin'; 
$pass = 'AVNS_Vhd9TZqqpSaORbdJc5k'; 

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    // Sync Database timezone with Manila
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

/**
 * CUSTOM SESSION HANDLER
 * This saves session data in the MySQL database instead of temporary server files.
 * This prevents "Stateless" server environments (like Vercel/Lambda) from logging you out.
 */
class DatabaseSessionHandler implements SessionHandlerInterface {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }
    public function open($path, $name): bool { return true; }
    public function close(): bool { return true; }
    
    public function read($id): string {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['data'] : '';
    }
    
    public function write($id, $data): bool {
        $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, timestamp) VALUES (?, ?, ?)");
        return $stmt->execute([$id, $data, time()]);
    }
    
    public function destroy($id): bool {
        return $this->pdo->prepare("DELETE FROM sessions WHERE id = ?")->execute([$id]);
    }
    
    public function gc($max_lifetime): int|false {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE timestamp < ?");
        return $stmt->execute([time() - $max_lifetime]) ? 1 : false;
    }
}

// Initialize the Database Session Handler
$handler = new DatabaseSessionHandler($pdo);
session_set_save_handler($handler, true);

// Set session to last for 24 hours
ini_set('session.gc_maxlifetime', 86400); 
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();



// 2. PHPMailer Inclusion (Using Absolute Path Fix)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/Exception.php';
require __DIR__ . '/../PHPMailer/PHPMailer.php';
require __DIR__ . '/../PHPMailer/SMTP.php';

// 3. LOGOUT LOGIC
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: pos.php");
    exit;
}

// 2. DATABASE CONNECTION
$host = 'mysql-13f0ef48-deleonkeyt71-88b2.d.aivencloud.com'; 
$port = '10145';
$db   = 'defaultdb'; 
$user = 'avnadmin'; 
$pass = 'AVNS_Vhd9TZqqpSaORbdJc5k'; 

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// 3. LOGOUT LOGIC
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: pos.php");
    exit;
}

// --- START AUTH & STAFF LOGIC ---
$error = "";

// A. LOGIN LOGIC
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $userFound = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userFound) {
        if (password_verify($password, $userFound['password']) || $password === $userFound['password']) {
            if ($password === $userFound['password'] && substr($password, 0, 4) !== '$2y$') {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $userFound['id']]);
            }
            $_SESSION['user'] = $userFound;
            header("Location: pos.php");
            exit;
        } else {
            $error = "Wrong password. Please try again.";
        }
    } else {
        $error = "Username not found.";
    }
}

// --- RESET PASSWORD & OTP LOGIC ---
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'request_otp') {
        $email = $_POST['email'];
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $otp = rand(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));
            
            $update = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
            $update->execute([$otp, $expiry, $user['id']]);

            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0; 
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'deleonkeyt71@gmail.com'; 
                $mail->Password   = 'sittngbmflyqfxyn';   
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('deleonkeyt71@gmail.com', "Joana's Blacksheep");
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset Code';
                $mail->Body    = "Your OTP code is: <b style='font-size: 24px;'>$otp</b><br>It expires in 15 minutes.";

                $mail->send();
                header("Location: pos.php?view=verify_otp&email=" . urlencode($email));
                exit;
            } catch (Exception $e) {
                echo "<script>alert('Email failed to send. Error: {$mail->ErrorInfo}'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Email not found!'); window.history.back();</script>";
        }
    }

    if ($_POST['action'] === 'finalize_reset') {
        $email = $_POST['email'];
        $otp = $_POST['otp'];
        $new_pass = $_POST['new_password'];
        $now = date("Y-m-d H:i:s");

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND reset_token = ? AND token_expiry > ?");
        $stmt->execute([$email, $otp, $now]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
            $update->execute([$hashed_pass, $user['id']]);
            echo "<script>alert('Success! Login with your new password.'); window.location.href='pos.php';</script>";
            exit;
        } else {
            echo "<script>alert('Invalid or Expired Code!'); window.history.back();</script>";
        }
    }
}

// 5. BACKEND ACTIONS
if (isset($_SESSION['user']) && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_business') {
        $stmt = $pdo->prepare("UPDATE business_info SET business_name = ?, address = ?, contact_no = ?, email_recovery = ?, low_stock_threshold = ?, receipt_footer = ? WHERE id = 1");
        $stmt->execute([$_POST['b_name'], $_POST['b_address'], $_POST['b_contact'], $_POST['b_email'], $_POST['b_threshold'], $_POST['b_footer']]);
        header("Location: pos.php?view=settings"); exit;
    }

    if ($_POST['action'] === 'add_inventory') {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, price_wholesale, wholesale_qty) VALUES (?, ?, ?, ?, ?, ?)");
       $stmt->execute([$_POST['name'], $_POST['category'], $_POST['price'], (int)$_POST['add_stock_qty'], $_POST['price_wholesale'], $_POST['wholesale_qty']]);
        header("Location: pos.php?view=inventory"); exit;
    }

    if ($_POST['action'] === 'update_inventory') {
        $current_stock = (int)$_POST['stock'];
        $added_qty = (int)$_POST['add_stock_qty'];
        $final_stock = $current_stock + $added_qty; 

        $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, stock = ?, price_wholesale = ?, wholesale_qty = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['category'], $_POST['price'], $final_stock, $_POST['price_wholesale'], $_POST['wholesale_qty'], $_POST['id']]);
        header("Location: pos.php?view=inventory"); exit;
    }

    if ($_POST['action'] === 'delete_inventory') {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header("Location: pos.php?view=inventory"); exit;
    }

    if (isset($_POST['action']) && $_SESSION['user']['role'] === 'admin') {
        if ($_POST['action'] === 'add_staff') {
            $hashed = password_hash($_POST['staff_pass'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['staff_user'], $_POST['staff_email'], $hashed, $_POST['staff_role']]);
            header("Location: pos.php?view=settings&tab=user");
            exit;
        }

        if ($_POST['action'] === 'update_staff') {
            $pass = $_POST['staff_pass'];
            if (substr($pass, 0, 4) !== '$2y$') {
                $pass = password_hash($pass, PASSWORD_DEFAULT);
            }
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, password=?, role=? WHERE id=?");
            $stmt->execute([$_POST['staff_user'], $_POST['staff_email'], $pass, $_POST['staff_role'], $_POST['staff_id']]);
            header("Location: pos.php?view=settings&tab=user");
            exit;
        }
    }

    if ($_POST['action'] === 'delete_staff') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_POST['staff_id']]);
        header("Location: pos.php?view=settings&tab=user"); 
        exit;
    }

    if ($_POST['action'] === 'complete_sale') {
        header('Content-Type: application/json');
        $cart = json_decode($_POST['cart'], true);
        $total = $_POST['total'];
        $invoice = $_POST['invoice_no'];
        $salesPerson = $_SESSION['user']['username'];
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO sales (invoice_no, total_amount, sales_person, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$invoice, $total, $salesPerson]);
            $saleId = $pdo->lastInsertId();
            foreach ($cart as $item) {
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['deduction'], $item['id']]);
                $stmt = $pdo->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)");
                $stmt->execute([$saleId, $item['id'], $item['sold_qty'], $item['price']]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'get_sale_details') {
        header('Content-Type: application/json');
        $saleId = $_POST['sale_id'];
        $stmt = $pdo->prepare("SELECT si.*, p.name FROM sales_items si JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
        $stmt->execute([$saleId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($_POST['action'] === 'system_backup') {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename=jbs_backup_' . date('Ymd') . '.sql');
        echo "-- JBS POS Backup\n"; exit;
    }
}

// 6. DATA FETCHING (Same as your original code)
$biz = $pdo->query("SELECT * FROM business_info WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$view = $_GET['view'] ?? 'pos';

// SECURITY GATE: Redirect based on Role
if (isset($_SESSION['user'])) {
    $userRole = $_SESSION['user']['role'];
    if ($userRole === 'cashier' && !in_array($view, ['pos', 'sales'])) {
        $view = 'pos';
    } elseif (strpos($userRole, 'inventory') !== false && $view !== 'inventory') {
        $view = 'inventory';
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$allUsers = $pdo->query("SELECT * FROM users ORDER BY role ASC")->fetchAll(PDO::FETCH_ASSOC);

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$isFiltered = (!empty($startDate) || !empty($endDate));

if ($isFiltered) {
    $stmtSales = $pdo->prepare("SELECT * FROM sales WHERE DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC");
    $stmtSales->execute([$startDate, $endDate]);
    $stmtTotal = $pdo->prepare("SELECT SUM(total_amount) as total FROM sales WHERE DATE(created_at) BETWEEN ? AND ?");
    $stmtTotal->execute([$startDate, $endDate]);
} else {
    $stmtSales = $pdo->query("SELECT * FROM sales WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC");
    $stmtTotal = $pdo->query("SELECT SUM(total_amount) as total FROM sales WHERE DATE(created_at) = CURDATE()");
}

$salesHistory = $stmtSales->fetchAll(PDO::FETCH_ASSOC);
$grandTotalSales = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$totalInvValue = array_reduce($products, function($carry, $p) { return $carry + ($p['stock'] * $p['price']); }, 0);
$threshold = $biz['low_stock_threshold'] ?? 5;
$lowStockList = array_filter($products, function($p) use ($threshold) { return $p['stock'] <= $threshold && $p['stock'] > 0; });
$outOfStockList = array_filter($products, function($p) { return $p['stock'] <= 0; });
$topSelling = $pdo->query("SELECT p.name, SUM(si.quantity) as total_sold FROM sales_items si JOIN products p ON si.product_id = p.id GROUP BY si.product_id ORDER BY total_sold DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$receiptInvoice = "INV-" . str_pad(($pdo->query("SELECT COUNT(id) FROM sales")->fetchColumn() + 1), 5, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($biz['business_name'] ?? "Joana's Black Sheep"); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #E2DFCD; color: #3F4232; overflow: hidden; height: 100vh; }
        .nav-active { border-bottom: 3px solid white; color: white !important; }
        .mgmt-container { background-color: #3B3822; border-radius: 40px; overflow: hidden; }
        .input-dark { background-color: #5F6449; color: #D1D5DB; border-radius: 8px; border: none; }
        .scroll-container { overflow-y: scroll !important; }
        .scroll-container::-webkit-scrollbar { width: 8px; }
        .scroll-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .scroll-container::-webkit-scrollbar-thumb { background: #9BA17B; border-radius: 10px; }
        

        /* Hide the default browser eye icon in Edge/Chrome */
        input::-ms-reveal,
        input::-ms-clear {
        display: none;
        }

        .main-table { width: 100%; border-collapse: separate; border-spacing: 0; background: white; font-size: 11px; }
        .main-table thead th { position: sticky; top: 0; z-index: 30; background: #f1f1f1; border-bottom: 2px solid #ddd; padding: 12px; font-weight: 800; text-transform: uppercase; }
        .main-table td { padding: 10px; border-bottom: 1px solid #eee; text-align: center; }
        
        tr.inv-row:hover, tr.sales-row:hover, tr.report-row:hover, tr.pos-row:hover { background-color: #fef9c3 !important; cursor: pointer; transition: 0.1s; } 
        .selected-row { background-color: #ecfccb !important; border-left: 6px solid #84cc16; }

        .btn-filter-action { background-color: #9BA17B; color: white; transition: 0.3s; }
        .btn-filter-action:hover { background-color: #3B3822 !important; transform: translateY(-1px); }
        .btn-refilter-action { background-color: #EA580C; color: white; transition: 0.3s; }
        .btn-refilter-action:hover { background-color: #9A3412 !important; transform: translateY(-1px); }
        .btn-reset-action { background-color: #e5e7eb; color: #3F4232; transition: 0.3s; }
        .btn-reset-action:hover { background-color: #d1d5db !important; transform: translateY(-1px); }

        .btn-mgmt-active:not(:disabled):hover { background-color: #2d5b55 !important; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: 0.2s; }
        .btn-danger-active { color: white !important; background-color: #ef4444; transition: 0.2s; }
        .btn-danger-active:not(:disabled):hover { background-color: #b91c1c !important; transform: translateY(-2px); }
        .btn-add-new { background-color: transparent; border: 2px solid #9BA17B; color: white; transition: 0.3s all ease; }
        .btn-add-new:hover { background-color: #9BA17B !important; color: white !important; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .btn-clear { background-color: #4b4b4b; color: white; transition: 0.3s; }
        .btn-clear:hover { background-color: #262626 !important; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.3); }

        .btn-remove { color: #ef4444; font-weight: 800; cursor: pointer; transition: 0.2s; font-size: 18px; user-select: none; display: inline-block; }
        .btn-remove:hover { transform: scale(1.4); color: #b91c1c; background: transparent !important; }
        .report-card { cursor: pointer; transition: 0.3s; }
        .report-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }

        .settings-nav-item { padding: 18px 25px; cursor: pointer; transition: 0.3s; color: #3F4232; font-weight: 700; display: flex; align-items: center; gap: 15px; border-radius: 15px; margin-bottom: 10px; }
        .settings-nav-item:hover { background-color: rgba(155, 161, 123, 0.15); }
        .settings-nav-active { background-color: #3B3822 !important; color: white !important; }
        .settings-card { background: white; border-radius: 28px; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.05); }

        @media print {
            body * { visibility: hidden; }
            <?php if($view == 'pos'): ?>
            @page { size: 80mm auto; margin: 0; }
            #receiptContainer, #receiptContainer * { visibility: visible; color: black !important; } /* Force Black for Print */
            #receiptContainer { position: absolute; left: 0; top: 0; width: 80mm !important; padding: 4mm !important; background: white !important; color: black !important; border: none !important; }
            .main-table thead th { border-top: 1px dashed black !important; border-bottom: 1px dashed black !important; background: white !important; font-size: 10px !important; }
            .main-table td { font-size: 10px !important; text-align: left !important; }
            #grandTotalDisplayPrint { border-top: 1px dashed black !important; background: white !important; color: black !important; border-radius: 0; margin-top: 5mm; }
            #grandTotalDisplayPrint *, #grandTotalDisplayPrint strong { color: black !important; font-size: 12px !important; }
            <?php endif; ?>

            <?php if($view == 'reports'): ?>
            @page { size: auto; margin: 15mm; }
            #reportMainContainer, #reportMainContainer * { visibility: visible; }
            #reportMainContainer { position: absolute; left: 0; top: 0; width: 100%; color: black !important; }
            .print-header { display: block !important; border-bottom: 2px solid black; padding-bottom: 5mm; margin-bottom: 5mm; }
            .main-table { width: 100% !important; border: 1px solid black !important; }
            .main-table th, .main-table td { border: 1px solid black !important; padding: 8px !important; color: black !important; }
            <?php endif; ?>
            .no-print, #saleBtn, .btn-remove, #topSellersPanel { display: none !important; }
        }
        .print-header { display: none; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['user'])): ?>
    <div class="flex items-center justify-center h-screen bg-[#D6D2B1]">
        
        <?php if (isset($_GET['view']) && $_GET['view'] === 'forgot_password'): ?>
            <form method="POST" action="pos.php" class="bg-[#3B3822] p-10 rounded-[40px] shadow-2xl text-white w-[500px]">
                <h1 class="text-xl font-black text-center mb-6 uppercase tracking-tight">Reset Password</h1>
                <input type="hidden" name="action" value="request_otp">
                
                <div class="space-y-4">
                    <p class="text-[10px] text-center opacity-50 uppercase font-bold tracking-widest">Enter email to receive code</p>
                    <input type="email" name="email" placeholder="Registered Email" 
                           class="w-full p-4 input-dark outline-none rounded-2xl border border-white/10" required>
                </div>
                
                <button type="submit" class="w-full mt-8 py-4 bg-[#9BA17B] rounded-full font-bold uppercase hover:bg-[#b0b88c] transition-all">Send OTP Code</button>
                <div class="text-center mt-4">
                    <a href="pos.php" class="text-[10px] text-white/50 hover:text-white uppercase font-black">Back to Login</a>
                </div>
            </form>

        <?php elseif (isset($_GET['view']) && $_GET['view'] === 'verify_otp'): ?>
    <form method="POST" action="pos.php" class="bg-[#3B3822] p-10 rounded-[40px] shadow-2xl text-white w-[500px]">
        <h1 class="text-xl font-black text-center mb-6 uppercase tracking-tight">Verify Code</h1>
        <input type="hidden" name="action" value="finalize_reset">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
        
        <div class="space-y-4">
            <input type="text" name="otp" placeholder="Enter 6-Digit Code" 
                   class="w-full p-4 input-dark text-center font-bold tracking-[0.5em] text-lg rounded-2xl" maxlength="6" required>
            
            <div class="relative w-full">
                <input type="password" name="new_password" id="newPass" placeholder="New Password" 
                       class="w-full p-4 input-dark outline-none rounded-2xl pr-16" required>
                <button type="button" onclick="togglePass('newPass', this)" 
                        style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); z-index: 20;"
                        class="text-black/50 hover:text-black focus:outline-none text-[10px] font-black uppercase tracking-tighter">
                    SHOW
                </button>
            </div>
        </div>
        
        <button type="submit" class="w-full mt-8 py-4 bg-[#9BA17B] rounded-full font-bold uppercase hover:bg-[#b0b88c] transition-all">
            Update Password
        </button>
    </form>

        <?php else: ?>
            <form method="POST" action="pos.php" class="bg-[#3B3822] p-10 rounded-[40px] shadow-2xl text-white w-[500px]">
        <h1 class="text-xl font-black text-center mb-2 uppercase tracking-tight">
            <?php echo htmlspecialchars($biz['business_name'] ?? "Joana's Black Sheep"); ?>
        </h1>
        <p class="text-[10px] text-center mb-6 opacity-50 uppercase font-bold tracking-widest">Inventory and Sales Tracking System</p>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-2xl text-red-200 text-xs font-bold uppercase tracking-widest text-center animate-pulse">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <input type="hidden" name="action" value="login">
                
                <div class="space-y-4">
                    <input type="text" name="username" placeholder="Username" class="w-full p-4 input-dark outline-none" required>
                    
                    <div class="relative w-full">
                        <input type="password" name="password" id="loginPass" placeholder="Password" 
                               class="w-full p-4 input-dark outline-none pr-12" required>
                        <button type="button" onclick="togglePass('loginPass', this)" 
        style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); z-index: 20;"
        class="text-black/50 hover:text-black focus:outline-none text-[10px] font-black uppercase tracking-tighter">
    SHOW
</button>
                    </div>
                </div>

                <button type="submit" class="w-full mt-8 py-4 bg-[#9BA17B] rounded-full font-bold uppercase hover:bg-[#b0b88c] transition-all">LOG IN</button>
                
                <div class="text-center mt-6">
    <a href="javascript:void(0)" onclick="handleForgotPassword()" 
       class="text-[10px] text-white/30 hover:text-white uppercase font-black tracking-widest transition-all">
        Forgot Password?
    </a>
    <p id="adminNote" class="hidden text-[9px] text-red-400 mt-2 uppercase font-bold tracking-tighter">
        Contact your administrator to reset your password.
    </p>
</div>
            </form>
        <?php endif; ?>

    </div>
<?php else: ?>
    <header class="bg-[#9BA17B] p-4 text-white shadow-md flex justify-between items-center px-10 shrink-0 no-print">
        <div class="font-black text-xl uppercase italic tracking-tighter"><?php echo htmlspecialchars($biz['business_name'] ?? "Joana's Black Sheep"); ?></div>
        
        <nav class="flex gap-8 font-bold text-[#4B5238]">
            <?php 
                $userRole = $_SESSION['user']['role'];
                if ($userRole === 'admin'): ?>
                    <a href="pos.php?view=pos" class="<?php echo $view == 'pos' ? 'nav-active' : ''; ?>">POS</a>
                    <a href="pos.php?view=inventory" class="<?php echo $view == 'inventory' ? 'nav-active' : ''; ?>">Inventory</a>
                    <a href="pos.php?view=sales" class="<?php echo $view == 'sales' ? 'nav-active' : ''; ?>">Sales</a>
                    <a href="pos.php?view=reports" class="<?php echo $view == 'reports' ? 'nav-active' : ''; ?>">Reports</a>
                    <a href="pos.php?view=settings" class="<?php echo $view == 'settings' ? 'nav-active' : ''; ?>">Settings</a>
                
                <?php elseif ($userRole === 'cashier'): ?>
                    <a href="pos.php?view=pos" class="<?php echo $view == 'pos' ? 'nav-active' : ''; ?>">POS</a>
                    <a href="pos.php?view=sales" class="<?php echo $view == 'sales' ? 'nav-active' : ''; ?>">Sales</a>
                
                <?php elseif (strpos($userRole, 'inventory') !== false): ?>
                    <a href="pos.php?view=inventory" class="<?php echo $view == 'inventory' ? 'nav-active' : ''; ?>">Inventory</a>
                <?php endif; ?>
        </nav>

        <div class="flex items-center gap-4">
            <span class="text-[10px] bg-white/20 px-4 py-1 rounded-full uppercase font-black"><?php echo $_SESSION['user']['username']; ?></span>
            <a href="pos.php?logout=1" class="bg-[#3F4232] text-white px-4 py-1 rounded-full text-xs font-bold hover:bg-red-700 transition-colors">LOGOUT</a>
        </div>
    </header>

    <main class="flex-grow flex overflow-hidden h-[calc(100vh-72px)]">
        
        <?php if($view == 'inventory'): ?>
            <div class="flex p-6 gap-8 w-full h-full overflow-hidden">
                <section class="w-2/3 flex flex-col h-full overflow-hidden">
                    <input type="text" id="invSearch" onkeyup="filterInventory()" placeholder="Search inventory..." class="w-full p-4 rounded-full mb-6 border-2 border-[#9BA17B] outline-none shadow-sm bg-white">
                    <div class="bg-white rounded-[40px] shadow-sm flex flex-grow overflow-hidden">
                        <div class="scroll-container w-full h-full">
                            <table class="main-table w-full">
                                <thead><tr><th>Category</th><th>Product Name</th><th>Stock</th><th>Retail</th><th>Wholesale</th></tr></thead>
                                <tbody id="inventoryTable">
                                    <?php foreach($products as $p): $isLow = ($p['stock'] <= $threshold); ?>
                                    <tr class="inv-row <?php echo $isLow ? 'bg-red-50' : ''; ?>" onclick='selectProduct(<?php echo json_encode($p); ?>, this)'>
                                        <td class="uppercase text-gray-400 text-[9px]"><?php echo htmlspecialchars($p['category']); ?></td>
                                        <td class="font-bold uppercase <?php echo $isLow ? 'text-red-600' : ''; ?>"><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td class="font-bold <?php echo $isLow ? 'text-red-700 font-black' : ''; ?>"><?php echo $p['stock']; ?> <?php echo $isLow ? '⚠️' : ''; ?></td>
                                        <td>P<?php echo number_format($p['price'], 2); ?></td>
                                        <td>P<?php echo number_format($p['price_wholesale'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
                <section class="w-1/3 mgmt-container p-8 text-white flex flex-col overflow-y-auto scroll-container">
                    <h2 class="text-center font-black mb-6 uppercase tracking-widest">Manage Product</h2>
                    <form method="POST" action="pos.php" id="inventoryForm" class="space-y-4">
                        <input type="hidden" name="action" id="formAction" value="add_inventory">
                        <input type="hidden" name="id" id="prodId">
                        <div><label class="text-[10px] font-bold uppercase opacity-80">Product Name</label><input type="text" name="name" id="prodName" required class="w-full p-3 input-dark outline-none mt-1"></div>
                        <div><label class="text-[10px] font-bold uppercase opacity-80">Category</label><input type="text" name="category" id="prodCat" class="w-full p-3 input-dark mt-1"></div>
                        
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="text-[10px] font-bold uppercase opacity-80">Retail Price</label><input type="number" name="price" id="prodPrice" step="0.01" required class="w-full p-3 input-dark mt-1"></div>
                            <div><label class="text-[10px] font-bold uppercase opacity-80">Current Stock</label><input type="number" name="stock" id="prodStock" readonly class="w-full p-3 input-dark mt-1 opacity-50 cursor-not-allowed"></div>
                            <div><label class="text-[10px] font-bold uppercase opacity-80 text-emerald-400">+ Add Stock</label><input type="number" name="add_stock_qty" id="addStockQty" placeholder="0" class="w-full p-3 bg-emerald-900/50 border border-emerald-500/50 text-emerald-200 rounded-lg outline-none mt-1"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="text-[10px] font-bold uppercase opacity-80">Wholesale Price</label><input type="number" name="price_wholesale" id="prodPriceWS" step="0.01" class="w-full p-3 input-dark mt-1"></div>
                            <div><label class="text-[10px] font-bold uppercase opacity-80">WS Min Qty</label><input type="number" name="wholesale_qty" id="prodWSQty" class="w-full p-3 input-dark mt-1"></div>
                        </div>
                        <div class="pt-6 space-y-3">
                            <button type="submit" id="addBtn" class="w-full py-4 rounded-full font-bold uppercase btn-add-new">Add New Product</button>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="submit" id="updateBtn" disabled class="py-4 bg-[#2D5B55] rounded-full font-bold opacity-20 btn-mgmt-active">Update</button>
                                <button type="button" id="deleteBtn" disabled onclick="confirmDelete()" class="py-4 rounded-full font-bold opacity-20 btn-danger-active">Delete</button>
                            </div>
                            <button type="button" onclick="clearForm()" class="w-full py-2 bg-gray-600 rounded-full text-xs font-bold uppercase btn-clear">Clear Form</button>
                        </div>
                    </form>
                </section>
            </div>

        <?php elseif($view == 'pos'): ?>
            <div class="flex p-6 gap-8 w-full h-full overflow-hidden">
                <section class="flex flex-col h-full w-1/2 overflow-hidden">
                    <input type="text" id="posSearch" onkeyup="filterPOS()" placeholder="Search product..." class="w-full p-4 rounded-full mb-6 border-2 border-[#9BA17B] outline-none shadow-sm bg-white">
                    <div class="bg-white rounded-3xl shadow-lg border overflow-hidden flex flex-grow">
                        <div class="scroll-container w-full h-full">
                            <table class="main-table w-full">
                                <thead><tr><th>Category</th><th>Product</th><th>Price</th><th>Stock</th><th>Action</th></tr></thead>
                                <tbody id="posProductTable">
                                    <?php foreach($products as $p): $isLow = ($p['stock'] <= $threshold); ?>
                                    <tr class="pos-row">
                                        <td class="text-gray-400 text-[9px] uppercase"><?php echo htmlspecialchars($p['category']); ?></td>
                                        <td class="font-bold"><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td class="text-[10px] font-bold">RT: P<?php echo number_format($p['price'],0); ?><br>WS: P<?php echo number_format($p['price_wholesale'],0); ?></td>
                                        <td class="<?php echo $isLow ? 'text-red-600 font-bold' : ''; ?>"><?php echo $p['stock']; ?></td>
                                        <td>
                                            <button onclick='addToCart(<?php echo json_encode($p); ?>, "retail")' class="bg-[#5F6449] hover:bg-[#3B3822] text-white px-2 py-1 rounded text-[9px] mr-1">RT</button>
                                            <button onclick='addToCart(<?php echo json_encode($p); ?>, "wholesale")' class="bg-[#3B3822] hover:bg-black text-white px-2 py-1 rounded text-[9px]">WS</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
                <section id="receiptContainer" class="bg-[#332F21] text-white p-8 rounded-[2.5rem] flex flex-col h-full w-1/2 overflow-hidden">
                    <div class="text-center mb-4 uppercase font-black tracking-widest italic">
                        <?php echo htmlspecialchars($biz['business_name']); ?><br>
                        <div class="text-[10px] not-italic font-bold mb-1"><?php echo date('M d, Y | h:i A'); ?></div>
                        <hr class="border-white/20 mb-2 no-print">
                        <span class="text-[10px] text-[#9BA17B] not-italic">Order Receipt</span><br>
                        <span class="text-[10px] text-[#9BA17B] not-italic"><?php echo $receiptInvoice; ?></span>
                    </div>
                    <div class="flex-grow scroll-container bg-white rounded-xl text-black mb-4 h-full">
                        <table class="main-table w-full">
                            <thead><tr><th class="text-left p-2">Item</th><th>Price</th><th>Qty</th><th>Total</th><th class="no-print"></th></tr></thead>
                            <tbody id="cartItems"></tbody>
                        </table>
                    </div>
                    <div id="grandTotalDisplayPrint" class="bg-[#9BA17B] p-4 rounded-2xl flex justify-between items-center mb-4 font-black text-lg">
                        <span class="uppercase tracking-widest">Grand Total:</span><span id="grandTotal"><strong>P 0.00</strong></span>
                    </div>
                    <button onclick="processSale()" id="saleBtn" class="w-full py-4 bg-white text-[#332F21] rounded-full font-black uppercase hover:bg-[#9BA17B] hover:text-white shadow-lg no-print">Complete Order</button>
                </section>
            </div>
            <?php elseif($view == 'sales'): ?>
            <div class="flex flex-col w-full h-full p-6 overflow-hidden">
                <div class="flex items-end justify-between gap-6 mb-4 no-print">
                    <div class="bg-[#3B3822] px-6 py-3 rounded-2xl text-white w-1/4 shadow-md border-b-4 border-[#9BA17B]">
                        <p class="text-[8px] uppercase font-bold opacity-70"><?php echo $isFiltered ? 'Filtered Total' : "Today's Total"; ?></p>
                        <h2 class="text-lg font-black text-[#9BA17B]">P <?php echo number_format($grandTotalSales, 2); ?></h2>
                    </div>
                    <div class="flex items-end gap-2 bg-white p-3 rounded-2xl border shadow-sm">
                        <form method="GET" action="pos.php" class="flex items-end gap-2">
                            <input type="hidden" name="view" value="sales">
                            <input type="date" name="start_date" value="<?php echo $startDate; ?>" class="text-[10px] p-2 rounded bg-gray-100 border outline-none">
                            <input type="date" name="end_date" value="<?php echo $endDate; ?>" class="text-[10px] p-2 rounded bg-gray-100 border outline-none">
                            <button type="submit" class="px-4 py-2 rounded-lg text-[10px] font-bold uppercase btn-filter-action shadow-sm">FILTER</button>
                            <?php if ($isFiltered): ?>
                                <button type="submit" class="px-4 py-2 rounded-lg text-[10px] font-bold uppercase btn-refilter-action shadow-sm">RE-FILTER</button>
                            <?php endif; ?>
                        </form>
                        <a href="pos.php?view=sales" class="btn-reset-action px-4 py-2 rounded-lg text-[10px] font-bold uppercase transition-all shadow-sm">RESET</a>
                    </div>
                    <div class="flex-grow"><input type="text" id="salesSearch" onkeyup="filterSales()" placeholder="Search Invoice No..." class="w-full p-4 rounded-2xl border-2 border-[#9BA17B] outline-none text-sm font-bold shadow-sm"></div>
                </div>
                <div class="bg-white rounded-[30px] shadow-lg flex-grow overflow-hidden flex flex-col h-full">
                    <div class="scroll-container flex-grow">
                        <table class="main-table w-full">
                            <thead><tr><th>Invoice No</th><th>Date & Time</th><th>Total Amount</th><th>Sales Person</th></tr></thead>
                            <tbody id="salesHistoryTable">
                                <?php foreach($salesHistory as $s): ?>
                                <tr class="sales-row" onclick="viewSaleDetails(<?php echo $s['id']; ?>, '<?php echo $s['invoice_no']; ?>', '<?php echo $s['total_amount']; ?>', '<?php echo $s['sales_person']; ?>')">
                                    <td class="font-bold"><?php echo $s['invoice_no']; ?></td>
                                    <td class="text-gray-400 text-[10px]"><?php echo date('M d, Y | h:i A', strtotime($s['created_at'])); ?></td>
                                    <td class="font-black text-[#5F6449]">P<?php echo number_format($s['total_amount'], 2); ?></td>
                                    <td><span class="bg-[#E2DFCD] px-3 py-0.5 rounded-full text-[9px] font-black uppercase"><?php echo htmlspecialchars($s['sales_person'] ?? 'Cashier'); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="saleDetailModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-[40px] w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="bg-[#3B3822] p-6 text-white flex justify-between items-center">
                        <div><h3 id="modalInvoiceNo" class="font-black text-xl">INV-00000</h3><p id="modalSalesPerson" class="text-[10px] opacity-60">Sold by: Admin</p></div>
                        <button onclick="closeModal()" class="text-2xl">×</button>
                    </div>
                    <div class="p-6 overflow-y-auto scroll-container flex-grow">
                        <table class="w-full text-sm">
                            <thead class="border-b-2"><tr><th class="text-left py-2">Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                            <tbody id="modalItemsBody" class="divide-y"></tbody>
                        </table>
                    </div>
                    <div class="bg-gray-50 p-8 border-t flex justify-between items-center">
                        <span class="font-black text-gray-400">TOTAL PAID</span>
                        <span id="modalTotalAmount" class="text-2xl font-black">P 0.00</span>
                    </div>
                </div>
            </div>

        <?php elseif($view == 'reports'): ?>
            <div id="reportMainContainer" class="flex flex-col p-6 gap-6 w-full h-full overflow-hidden">
                <div class="print-header text-center">
                    <h1 class="text-2xl font-black uppercase"><?php echo htmlspecialchars($biz['business_name']); ?></h1>
                    <h2 id="printReportTitle" class="text-xl font-bold mt-2">Full Inventory Report</h2>
                    <div id="printReportValueRow" class="flex justify-center gap-10 mt-4 font-black text-sm">
                        <div id="printReportValLabel">INVENTORY VALUE: P<?php echo number_format($totalInvValue, 2); ?></div>
                        <div id="printReportSalesLabel" style="display:none;">TOTAL SALES: P<?php echo number_format($grandTotalSales, 2); ?></div>
                    </div>
                    <p class="text-[10px] mt-4 italic">Date Generated: <?php echo date('M d, Y h:i A'); ?></p>
                </div>
                
                <div class="grid grid-cols-4 gap-4 no-print flex-shrink-0">
                    <div onclick="filterReport('critical', 'Critical Stock Report')" class="report-card bg-white p-5 rounded-3xl border-l-8 border-red-500 shadow-sm">
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Critical Stock</p>
                        <h3 class="text-2xl font-black text-red-600"><?php echo count($lowStockList); ?></h3>
                    </div>
                    <div onclick="filterReport('out', 'Out of Stock Report')" class="report-card bg-white p-5 rounded-3xl border-l-8 border-black shadow-sm">
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Out of Stock</p>
                        <h3 class="text-2xl font-black text-gray-900"><?php echo count($outOfStockList); ?></h3>
                    </div>
                    <div onclick="filterReport('all', 'Full Inventory Report')" class="report-card bg-white p-5 rounded-3xl border-l-8 border-[#9BA17B] shadow-sm">
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Inventory Value</p>
                        <h3 class="text-2xl font-black text-[#9BA17B]">P<?php echo number_format($totalInvValue, 0); ?></h3>
                    </div>
                    <div onclick="filterReport('sales', 'Sales History Report')" class="report-card bg-white p-5 rounded-3xl border-l-8 border-blue-500 shadow-sm">
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Sales History</p>
                        <h3 class="text-2xl font-black text-blue-600">P<?php echo number_format($grandTotalSales, 0); ?></h3>
                    </div>
                </div>

                <div class="flex gap-6 flex-grow overflow-hidden h-full">
                    <div id="reportTableWrapper" class="w-2/3 bg-white rounded-[40px] shadow-lg overflow-hidden flex flex-col h-full">
                        <div class="p-5 border-b flex justify-between items-center no-print">
                            <h3 id="reportTitle" class="font-black uppercase text-[#3B3822]">Full Inventory List</h3>
                            <button onclick="window.print()" class="bg-[#3B3822] text-white px-6 py-2 rounded-full text-[10px] font-black uppercase hover:bg-black transition-colors">Print Report</button>
                        </div>
                        <div class="scroll-container flex-grow h-full">
                            <table class="main-table w-full" id="reportMainTable">
                                <thead id="reportTableHead"><tr><th>Category</th><th>Product</th><th>Stock</th><th>Price</th></tr></thead>
                                <tbody id="reportItemsTable">
                                    <?php foreach($products as $p): ?>
                                    <tr class="report-row" data-type="product" data-stock="<?php echo $p['stock']; ?>">
                                        <td class="text-gray-400 text-[9px] uppercase"><?php echo htmlspecialchars($p['category']); ?></td>
                                        <td class="font-bold uppercase text-left pl-6"><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td class="font-black"><?php echo $p['stock']; ?></td>
                                        <td class="font-bold text-[#9BA17B]">P<?php echo number_format($p['price'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php foreach($salesHistory as $s): ?>
                                    <tr class="report-row" data-type="sale" style="display:none;">
                                        <td class="font-bold"><?php echo $s['invoice_no']; ?></td>
                                        <td class="text-[10px]"><?php echo date('M d, Y | h:i A', strtotime($s['created_at'])); ?></td>
                                        <td class="font-black text-right pr-6">P<?php echo number_format($s['total_amount'], 2); ?></td>
                                        <td class="uppercase text-[9px]"><?php echo htmlspecialchars($s['sales_person'] ?? 'Cashier'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="topSellersPanel" class="w-1/3 bg-[#3B3822] rounded-[40px] p-8 text-white flex flex-col no-print h-full overflow-hidden">
                        <h3 class="font-black uppercase mb-4 border-b border-white/20 pb-2 tracking-widest italic text-lg">Top 5 Products</h3>
                        <div class="scroll-container flex-grow h-full">
                            <?php foreach($topSelling as $i => $item): ?>
                                <div class="flex justify-between py-4 border-b border-white/10 px-1">
                                    <span class="text-[14px] font-black uppercase tracking-wide">#<?php echo $i+1; ?> <?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="text-[#9BA17B] font-black"><?php echo $item['total_sold']; ?> sold</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif($view == 'settings'): ?>
            <div class="flex w-full h-full overflow-hidden bg-gray-50">
                <aside class="w-72 bg-white border-r p-8 no-print">
                    <h2 class="font-black text-xs uppercase text-gray-400 mb-8 tracking-widest">Control Panel</h2>
                    <nav class="space-y-2">
                        <div onclick="showTab('general')" id="btn_general" class="settings-nav-item settings-nav-active"><span>⚙️</span> General Settings</div>
                        <div onclick="showTab('user')" id="btn_user" class="settings-nav-item"><span>👥</span> Staff Control</div>
                        <div onclick="showTab('maint')" id="btn_maint" class="settings-nav-item"><span>🛠️</span> Maintenance</div>
                    </nav>
                </aside>

                <section class="flex-grow scroll-container p-12 h-full">
                    <div id="tab_general" class="settings-tab space-y-8 h-full">
                        <div class="settings-card">
                            <h3 class="font-black text-[#3B3822] uppercase tracking-wide mb-8">Business Identity</h3>
                            <form method="POST" action="pos.php" class="grid grid-cols-2 gap-8">
                                <input type="hidden" name="action" value="update_business">
                                <div class="col-span-2"><label class="text-[10px] font-black text-gray-400 uppercase">Registered Name</label><input type="text" name="b_name" value="<?php echo htmlspecialchars($biz['business_name']); ?>" required class="w-full p-3 bg-gray-50 border rounded-2xl font-bold mt-1 outline-none"></div>
                                <div class="col-span-2"><label class="text-[10px] font-black text-gray-400 uppercase">Recovery Email</label><input type="email" name="b_email" value="<?php echo htmlspecialchars($biz['email_recovery']); ?>" required class="w-full p-3 bg-gray-50 border rounded-2xl font-bold mt-1 outline-none"></div>
                                <div><label class="text-[10px] font-black text-gray-400 uppercase">Location</label><input type="text" name="b_address" value="<?php echo htmlspecialchars($biz['address']); ?>" class="w-full p-3 bg-gray-50 border rounded-2xl font-bold mt-1 outline-none"></div>
                                <div><label class="text-[10px] font-black text-gray-400 uppercase">Contact</label><input type="text" name="b_contact" value="<?php echo htmlspecialchars($biz['contact_no']); ?>" class="w-full p-3 bg-gray-50 border rounded-2xl font-bold mt-1 outline-none"></div>
                                <div><label class="text-[10px] font-black text-gray-400 uppercase">Low Stock Threshold</label><input type="number" name="b_threshold" value="<?php echo $biz['low_stock_threshold']; ?>" class="w-full p-3 bg-gray-50 border rounded-2xl font-bold mt-1 outline-none"></div>
                                <div class="col-span-2"><label class="text-[10px] font-black text-gray-400 uppercase">Receipt Footer Message</label><textarea name="b_footer" rows="2" class="w-full p-3 bg-gray-50 border rounded-2xl font-bold mt-1 outline-none"><?php echo htmlspecialchars($biz['receipt_footer']); ?></textarea></div>
                                <div class="col-span-2 text-right"><button type="submit" class="bg-[#3B3822] text-white px-10 py-3 rounded-full font-black uppercase text-[10px]">Update Settings</button></div>
                            </form>
                        </div>
                    </div>

                    <div id="tab_user" class="settings-tab hidden flex flex-col lg:flex-row gap-8 anim-fade-in">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 flex-grow overflow-hidden">
                            <div class="p-6 border-b bg-gray-50 flex justify-between items-center">
                                <h3 class="font-bold text-gray-800 uppercase tracking-tight">Staff Directory</h3>
                                <span class="text-[10px] bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-bold">Authorized Personnel</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase font-black tracking-wider">
                                        <tr>
                                            <th class="p-4 text-left">Staff Member</th>
                                            <th class="p-4 text-left">Access Role</th>
                                            <th class="p-4 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <?php foreach($allUsers as $u): ?>
                                        <tr class="hover:bg-emerald-50/50 transition-all cursor-pointer group" onclick='selectStaff(<?php echo json_encode($u); ?>)'>
                                            <td class="p-4">
                                                <div class="font-bold text-gray-800 group-hover:text-emerald-700"><?php echo htmlspecialchars($u['username']); ?></div>
                                                <div class="text-[10px] text-gray-400"><?php echo htmlspecialchars($u['email'] ?? 'No email associated'); ?></div>
                                            </td>
                                            <td class="p-4">
                                                <span class="px-3 py-1 rounded-lg font-bold text-[9px] uppercase tracking-wider
                                                    <?php echo $u['role'] == 'admin' ? 'bg-purple-100 text-purple-700' : 
                                                            ($u['role'] == 'cashier' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'); ?>">
                                                    <?php echo htmlspecialchars($u['role']); ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-center">
                                                <button class="text-emerald-600 font-bold text-[10px] uppercase hover:underline">Manage Account</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="w-full lg:w-[400px] bg-white rounded-3xl shadow-lg border border-gray-200 p-8 flex flex-col h-fit">
                            <div class="text-center mb-8">
                                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">👤</div>
                                <h4 id="staffTitle" class="font-black text-gray-800 uppercase tracking-widest text-sm">Register Staff</h4>
                            </div>

                            <form method="POST" action="pos.php" id="staffForm" class="space-y-5">
                                <input type="hidden" name="action" id="staffAction" value="add_staff">
                                <input type="hidden" name="staff_id" id="staffId">
                                <div class="space-y-1"><label class="text-[10px] font-black text-gray-500 uppercase ml-1">Username</label><input type="text" name="staff_user" id="staffUser" required class="w-full p-3 bg-gray-50 border rounded-xl font-semibold outline-none"></div>
                               <div id="emailContainer" class="space-y-1">
    <label class="text-[10px] font-black text-gray-500 uppercase ml-1">Email</label>
    <input type="email" name="staff_email" id="staffEmail" required class="w-full p-3 bg-gray-50 border rounded-xl font-semibold outline-none">
</div>

<div class="space-y-1">
    <label class="text-[10px] font-black text-gray-500 uppercase ml-1">Password</label>
    <div class="relative"> <input type="password" name="staff_pass" id="staffPass" required class="w-full p-3 bg-gray-50 border rounded-xl font-semibold outline-none pr-16">
        <button type="button" onclick="togglePass('staffPass', this)" 
                class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-black/50 hover:text-black uppercase">
            SHOW
        </button>
    </div>
</div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-gray-500 uppercase ml-1">Access Role</label>
                                    <select name="staff_role" id="staffRole" class="w-full p-3 bg-gray-50 border rounded-xl font-bold outline-none">
                                        <option value="admin">System Administrator</option>
                                        <option value="cashier">Cashier</option>
                                        <option value="inventory checker">Inventory Checker</option>
                                    </select>
                                </div>
                                <div class="pt-6 flex flex-col gap-3">
                                    <button type="submit" id="staffMainBtn" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black uppercase text-[11px]">Create Account</button>
                                    <div id="editButtons" class="hidden flex gap-2">
                                        <button type="submit" name="action" value="delete_staff" class="flex-grow py-3 bg-red-50 text-red-600 rounded-xl font-bold uppercase text-[10px]">Remove Account</button>
                                        <button type="button" onclick="resetStaffForm()" class="px-6 py-3 bg-gray-100 text-gray-500 rounded-xl font-bold uppercase text-[10px]">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="tab_maint" class="settings-tab hidden grid grid-cols-2 gap-8 text-center">
                        <div class="settings-card border-t-8 border-blue-500 transition-all duration-300 hover:shadow-xl">
                            <div class="text-5xl mb-6">📂</div>
                            <h4 class="font-black uppercase text-xs mb-3">Backup</h4>
                            <form method="POST"><input type="hidden" name="action" value="system_backup"><button type="submit" class="bg-blue-500 text-white px-8 py-3 rounded-2xl font-black text-[10px] uppercase hover:bg-blue-600 shadow-md">Export SQL</button></form>
                        </div>
                        <div class="settings-card border-t-8 border-orange-500 transition-all duration-300 hover:shadow-xl">
                            <div class="text-5xl mb-6">🔄</div>
                            <h4 class="font-black uppercase text-xs mb-3">Restart</h4>
                            <button onclick="location.reload()" class="bg-orange-500 text-white px-8 py-3 rounded-2xl font-black text-[10px] uppercase hover:bg-orange-600 shadow-md">Reload</button>
                        </div>
                    </div>
                </section>
            </div>
        <?php endif; ?>
    </main>
<?php endif; ?>
    <script>

        function confirmDelete() { if(confirm("Are you sure?")) { document.getElementById('formAction').value = 'delete_inventory'; document.getElementById('inventoryForm').submit(); } }
        function clearForm() {
            document.getElementById('inventoryForm').reset();
            document.getElementById('formAction').value = 'add_inventory';
            document.getElementById('prodId').value = '';
            document.getElementById('addBtn').disabled = false;
            document.getElementById('updateBtn').disabled = true;
            document.getElementById('deleteBtn').disabled = true;
            document.getElementById('updateBtn').classList.add('opacity-20');
            document.getElementById('deleteBtn').classList.add('opacity-20');
            document.querySelectorAll('.inv-row').forEach(r => r.classList.remove('selected-row'));
            
            // RESET ADD STOCK QTY
            document.getElementById('addStockQty').value = '';
        }
        function selectProduct(data, row) {
            document.querySelectorAll('.inv-row').forEach(r => r.classList.remove('selected-row'));
            row.classList.add('selected-row');
            document.getElementById('formAction').value = 'update_inventory';
            document.getElementById('prodId').value = data.id;
            document.getElementById('prodName').value = data.name;
            document.getElementById('prodCat').value = data.category;
            document.getElementById('prodStock').value = data.stock;
            document.getElementById('prodPrice').value = data.price;
            document.getElementById('prodPriceWS').value = data.price_wholesale;
            document.getElementById('prodWSQty').value = data.wholesale_qty;
            
            // RESET ADD STOCK QTY ON SELECT
            document.getElementById('addStockQty').value = '';

            document.getElementById('addBtn').disabled = true;
            document.getElementById('updateBtn').disabled = false;
            document.getElementById('deleteBtn').disabled = false;
            document.getElementById('updateBtn').classList.remove('opacity-20');
            document.getElementById('deleteBtn').classList.remove('opacity-20');
        }

        let cart = [];
        function addToCart(p, type) {
            const cartId = p.id + '-' + type;
            const ex = cart.find(i => i.cartId === cartId);
            const price = (type === 'wholesale') ? p.price_wholesale : p.price;
            const deduction = (type === 'wholesale') ? parseInt(p.wholesale_qty) : 1;
            if(ex) { ex.qty++; } else { 
                cart.push({ id: p.id, cartId: cartId, name: p.name + (type==='wholesale'?' (WS)':''), price: price, qty: 1, unitDeduction: deduction }); 
            }
            updateCartUI();
        }
        function updateCartUI() {
            const con = document.getElementById('cartItems');
            let tot = 0;
            con.innerHTML = cart.map((i, idx) => { 
                tot += i.price * i.qty; 
                return `<tr>
    <td class="text-left font-bold p-2 text-[10px] uppercase">${i.name}</td>
    <td>${i.price}</td>
    <td><input type="number" value="${i.qty}" onchange="changeQty(${idx}, this.value)" class="w-12 border text-center rounded-md font-bold"></td>
    <td class="font-black">P${(i.price*i.qty).toFixed(2)}</td>
    <td class="p-0 text-center no-print"><span onclick="removeItem(${idx})" class="btn-remove">×</span></td>
    </tr>`;
            }).join('');
            document.getElementById('grandTotal').innerHTML = '<strong>P ' + tot.toFixed(2) + '</strong>';
        }
        function changeQty(idx, v) { let n = parseInt(v); if(n < 1 || isNaN(n)) n = 1; cart[idx].qty = n; updateCartUI(); }
        function removeItem(idx) { cart.splice(idx, 1); updateCartUI(); }

        async function processSale() {
    if(cart.length === 0) return alert("Cart is empty!");
    
    const btn = document.getElementById('saleBtn'); 
    btn.disabled = true; 
    btn.innerText = "Processing...";

    try {
        const fd = new FormData();
        fd.append('action', 'complete_sale');
        fd.append('cart', JSON.stringify(cart.map(i => ({
            id: i.id, 
            deduction: i.qty * i.unitDeduction, 
            sold_qty: i.qty, 
            price: i.price
        }))));
        fd.append('total', cart.reduce((s, i) => s + (i.price * i.qty), 0));
        fd.append('invoice_no', "<?php echo $receiptInvoice; ?>");

        const res = await fetch('pos.php', { method: 'POST', body: fd });
        
        // Check if the response is actually OK before parsing JSON
        if (!res.ok) throw new Error('Server error');

        const data = await res.json();

        if(data.status === 'success') { 
            window.print(); 
            alert("Successful!"); 
            location.reload(); 
        } else { 
            alert("Error: " + (data.message || "Unknown error"));
            btn.disabled = false; 
            btn.innerText = "Complete Order"; 
        }
    } catch (error) {
        console.error(error);
        alert("Failed to process sale. Check your database connection.");
        btn.disabled = false;
        btn.innerText = "Complete Order";
    }
}
        async function viewSaleDetails(saleId, invoice, total, person) {
            // FIX: Show the modal by removing the 'hidden' class
            document.getElementById('saleDetailModal').classList.remove('hidden');

            document.getElementById('modalInvoiceNo').innerText = invoice;
            document.getElementById('modalSalesPerson').innerText = "Sold by: " + (person || 'Admin');
            document.getElementById('modalTotalAmount').innerText = "P " + parseFloat(total).toLocaleString(undefined, {minimumFractionDigits: 2});
            
            const fd = new FormData();
            fd.append('action', 'get_sale_details');
            fd.append('sale_id', saleId);
            
            const res = await fetch('pos.php', { method: 'POST', body: fd });
            const items = await res.json();
            const body = document.getElementById('modalItemsBody');
            
            body.innerHTML = items.map(i => `
                <tr class="text-center text-[10px]">
                    <td class="text-left py-3 font-bold uppercase">${i.name}</td>
                    <td>P${parseFloat(i.price_at_sale).toFixed(2)}</td>
                    <td>${i.quantity}</td>
                    <td class="font-black">P${(i.price_at_sale * i.quantity).toFixed(2)}</td>
                </tr>
            `).join('');
        }
        function closeModal() { document.getElementById('saleDetailModal').classList.add('hidden'); }

        function filterReport(type, titleName) {
            document.getElementById('reportTitle').innerText = titleName;
            document.getElementById('printReportTitle').innerText = titleName;
            const vL = document.getElementById('printReportValLabel');
            const sL = document.getElementById('printReportSalesLabel');
            vL.style.display = 'none'; sL.style.display = 'none';
            const rows = document.querySelectorAll('.report-row');
            const head = document.getElementById('reportTableHead');
            
            if(type === 'sales') { 
                head.innerHTML = "<tr><th>Inv No</th><th>Date</th><th>Amount</th><th>Person</th></tr>"; 
                if(sL) sL.style.display = 'block';
            } else { 
                head.innerHTML = "<tr><th>Category</th><th>Product</th><th>Stock</th><th>Price</th></tr>"; 
                if(type === 'all' && vL) vL.style.display = 'block';
            }
            
            rows.forEach(row => {
                const rowType = row.getAttribute('data-type');
                const stock = parseInt(row.getAttribute('data-stock'));
                if (type === 'critical') row.style.display = (rowType === 'product' && stock <= <?php echo $threshold; ?> && stock > 0) ? '' : 'none';
                else if (type === 'out') row.style.display = (rowType === 'product' && stock <= 0) ? '' : 'none';
                else if (type === 'sales') row.style.display = (rowType === 'sale') ? '' : 'none';
                else row.style.display = (rowType === 'product') ? '' : 'none';
            });
        }

        function showTab(id) {
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.add('hidden'));
            document.getElementById('tab_' + id).classList.remove('hidden');
            document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('settings-nav-active'));
            document.getElementById('btn_' + id).classList.add('settings-nav-active');
        }

       function selectStaff(data) {
    document.getElementById('staffTitle').innerText = "Edit Staff Account";
    document.getElementById('staffAction').value = "update_staff";
    document.getElementById('staffId').value = data.id;
    document.getElementById('staffUser').value = data.username;
    
    // 1. Handle Email Visibility
    const emailBox = document.getElementById('emailContainer');
    const emailInput = document.getElementById('staffEmail');
    
    if (data.role === 'admin') {
        emailBox.style.display = 'block'; // Show for Admin
        emailInput.value = data.email || '';
        emailInput.required = true;
    } else {
        emailBox.style.display = 'none';  // Hide for Cashier/IC
        emailInput.value = '';            // Clear value
        emailInput.required = false;      // Remove required attribute
    }

    // 2. Handle Password
    document.getElementById('staffPass').value = ''; // Clear it for security
    document.getElementById('staffPass').placeholder = "Enter new password or leave blank";
    document.getElementById('staffPass').required = false; 

    document.getElementById('staffRole').value = data.role;
    document.getElementById('editButtons').classList.remove('hidden');
}

        function resetStaffForm() {
    document.getElementById('staffForm').reset();
    document.getElementById('staffTitle').innerText = "Register Staff";
    document.getElementById('staffAction').value = "add_staff";
    document.getElementById('editButtons').classList.add('hidden');
    
    // Ensure email is visible by default when adding new (or logic it based on default role)
    document.getElementById('emailContainer').style.display = 'block';
    document.getElementById('staffPass').required = true;
    document.getElementById('staffPass').placeholder = "";
}
       function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);

    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "HIDE";
    } else {
        input.type = "password";
        btn.textContent = "SHOW";
    }
}

function handleForgotPassword() {
    const usernameInput = document.getElementsByName('username')[0].value.trim().toLowerCase();
    const note = document.getElementById('adminNote');

    if (usernameInput === 'admin') {
        // Redirect to the reset page only if the user is the admin
        window.location.href = "?view=forgot_password";
    } else if (usernameInput === "") {
        // If the box is empty, remind them to type their username first
        alert("Please enter your username first to check reset eligibility.");
    } else {
        // Show the note for non-admin accounts
        note.classList.remove('hidden');
        // Hide it again after 5 seconds so it doesn't clutter the UI
        setTimeout(() => { note.classList.add('hidden'); }, 5000);
    }
}
    </script> 

</body>
</html>
