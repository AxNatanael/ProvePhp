<?php
$host = '127.0.0.1';
$db   = 'school_db'; 
$user = 'root';          
$pass = '';              
$port = 3307;            
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";   //dsn is the standard name for string connecions and stand for Data Source Name 

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //stop the compilation and send a error if there is a mistake
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options); //PHP data object make the call to the db 
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit;
}
?>