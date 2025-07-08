<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Get environment variables
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];       // corrected variable name
$pass = $_ENV['DB_PASS'];       // corrected variable name
$charset = 'utf8mb4';           // make sure charset is defined

// DSN (Data Source Name) string for PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";  // corrected $db to $dbname

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

function isAlreadyEnrolled($phone, $dob)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE phone = ? AND dob = ?");
    $stmt->execute([$phone, $dob]);
    return $stmt->fetchColumn() > 0;
}

function addEnrollment($data)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO enrollments (name, phone, dob, size, allergies, parent, nparent, parenting)
        VALUES (:name, :phone, :dob, :size, :allergies, :parent, :nparent, :parenting)
    ");

    return $stmt->execute([
        ':name' => $data['name'],
        ':phone' => $data['phone'],
        ':dob' => $data['dob'],
        ':size' => $data['size'],
        ':allergies' => $data['allergies'],
        ':parent' => $data['parent'],
        ':nparent' => $data['nparent'],
        ':parenting' => $data['parenting']
    ]);
}

function loginUser($email, $password)
{
    global $pdo;
    $sql = "SELECT id, name, password_hash FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Password matches
        return $user;
    } else {
        // Invalid credentials
        return false;
    }
}

function userExists($email)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

function enrollUserWithTransaction($name, $email, $course)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        if (userExists($email)) {
            $pdo->rollBack();
            return false; // user exists
        }

        $sql = "INSERT INTO enrollments (name, email, course) VALUES (:name, :email, :course)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['name' => $name, 'email' => $email, 'course' => $course]);

        // Imagine you want to log enrollment to another table:
        $logSql = "INSERT INTO enrollment_logs (email, enrolled_at) VALUES (:email, NOW())";
        $logStmt = $pdo->prepare($logSql);
        $logStmt->execute(['email' => $email]);

        $pdo->commit();
        return true;
    } catch (\Exception $e) {
        $pdo->rollBack();
        // Handle error or log it
        return false;
    }
}
