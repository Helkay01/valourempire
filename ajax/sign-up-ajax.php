<?php
include('../connections.php');


if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // 1. Sanitize input
    $fn      = trim($_POST['fn']);
    $ln      = trim($_POST['ln']);
    $email   = trim($_POST['email']);
    $pwd     = $_POST['pwd']; // hash later
    $bizName = trim($_POST['bizName']);
    $bizType = trim($_POST['bizType']);
    $addr    = trim($_POST['addr']);

    // 2. Generate unique 8-digit user ID (as string, preserving leading 0s)
    function generateUniqueId($pdo) {
        do {
            $uid = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare("SELECT 1 FROM login WHERE user_id = :uid");
            $stmt->execute([':uid' => $uid]);
        } while ($stmt->fetch());
        return $uid;
    }

    try {
        // 3. Connect to database (assuming $userdata is your PDO connection)
      //  $pdo = $userdata; // or replace with your actual PDO instance

        // 4. Check if email already exists
        $checkStmt = $pdo->prepare("SELECT 1 FROM login WHERE email = :email");
        $checkStmt->execute([':email' => $email]);

        if ($checkStmt->fetch()) {
            echo "Email already exists.";
            exit;
        }

        // 5. Hash the password
        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);

        // 6. Generate a unique ID
        $uid = generateUniqueId($pdo);

        // 7. Insert new user
        $ins = "INSERT INTO login 
            (user_id, fn, ln, email, pwd, biz_name, biz_type, biz_address) 
            VALUES (:uid, :fn, :ln, :email, :pwd, :bn, :bt, :ba)";

        $stmt = $pdo->prepare($ins);
        $stmt->execute([
            ':uid'   => $uid,
            ':fn'    => $fn,
            ':ln'    => $ln,
            ':email' => $email,
            ':pwd'   => $hashedPwd,
            ':bn'    => $bizName,
            ':bt'    => $bizType,
            ':ba'    => $addr
        ]);

        echo "Registration successful.";

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


