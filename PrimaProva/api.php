<?php

header("Content-Type: application/json; charset=UTF-8");    //tell to the client the type of the content
header("Access-Control-Allow-Methods: GET, POST, DELETE");  //allow only this metods 

include "db.php";

$method = $_SERVER['REQUEST_METHOD']; //when someone make a request this variable get the request of the CRUD  
$input = json_decode(file_get_contents("php://input"), true);   //translate the body of the request in a simple array string

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['list_classes'])) {    //make to need not another get , this get work for classes and studets   
                $stmt = $pdo->query("SELECT * FROM classes ORDER BY name ASC"); 
                echo json_encode($stmt->fetchAll());
                exit;
            }

            $sql = "SELECT students.id, students.name, students.dob, classes.name as class_name 
                    FROM students 
                    LEFT JOIN classes ON students.class_id = classes.id 
                    ORDER BY students.id DESC";
            
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll());
            break;

        case 'POST':
            if (!isset($input['name']) || !isset($input['class_id'])) {
                throw new Exception("Missing data (Name or Class ID)");
            }
            $sql = "INSERT INTO students (name, class_id, dob) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$input['name'], $input['class_id'], $input['dob']]);
            echo json_encode(["message" => "Student added successfully!"]);
            break;

        case 'DELETE':
            if (!isset($input['id'])) {
                throw new Exception("Missing ID");
            }
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$input['id']]);
            echo json_encode(["message" => "Student deleted!"]);
            break;
            
        default:
            echo json_encode(["message" => "Method not supported"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(400); 
    echo json_encode(["error" => $e->getMessage()]);
}
?>