<?php
declare(strict_types=1);
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password = (string)($_POST['password'] ?? '');
$phone    = trim($_POST['phone']    ?? '');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Please enter a name, valid email, and a password (6+ chars).']);
    exit;
}

try {
    // Get all columns from `users`
    $colsStmt = $pdo->prepare("
        SELECT LOWER(COLUMN_NAME) AS c
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
    ");
    $colsStmt->execute();
    $columns = array_map(fn($r) => $r['c'], $colsStmt->fetchAll(PDO::FETCH_ASSOC));

    $find = function(array $candidates) use ($columns) {
        foreach ($candidates as $c) {
            if (in_array(strtolower($c), $columns, true)) return $c;
        }
        return null;
    };

    $colName     = $find(['name','full_name','client_name','user_name','username']) ?? 'name';
    $colEmail    = $find(['email','email_address','user_email','username']);
    $colPhone    = $find(['phone','contact','mobile','phone_number']);
    $colRole     = $find(['role','user_role']);
    $colPassHash = $find(['password_hash','pass_hash']);
    $colPass     = $colPassHash ? null : $find(['password','passwd']);

    if (!$colEmail) {
        echo json_encode(['success' => false, 'message' => "No email-like column found."]);
        exit;
    }

    // Duplicate check
    $check = $pdo->prepare("SELECT id FROM users WHERE `$colEmail` = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered.']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Build insert columns/values without duplicates
    $fields = [];
    $values = [];

    $addField = function($col, $val) use (&$fields, &$values) {
        if ($col && !in_array($col, $fields, true)) {
            $fields[] = $col;
            $values[] = $val;
        }
    };

    $addField($colName, $name);
    $addField($colEmail, $email);

    if ($colPassHash) {
        $addField($colPassHash, $hashed);
    } elseif ($colPass) {
        $addField($colPass, $hashed);
    } else {
        echo json_encode(['success' => false, 'message' => "No password column found."]);
        exit;
    }

    if ($colPhone) $addField($colPhone, $phone);
    if ($colRole)  $addField($colRole, 'user');

    $placeholders = implode(',', array_fill(0, count($fields), '?'));
    $sql = "INSERT INTO users (`" . implode('`,`', $fields) . "`) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    echo json_encode(['success' => true, 'message' => 'Registration successful!']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
