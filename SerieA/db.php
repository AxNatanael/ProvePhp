<?php
$host = "127.0.0.1";
$dbName = "SerieA";
$user = "root";
$passwd = "";
$strCode = "utf8mb4";
$port = 3307;

$dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=$strCode";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,    
];

try{
    $pdo = new PDO($dsn, $user , $passwd, $options);
} catch(\PDOException $err){
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode([ "error"=>"connection failed" . $err->getMessage() ]);
}
?>