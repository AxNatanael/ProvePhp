<?php
header("Content-Type: application/json; charset = UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE");

include "db.php";


$method = $_SERVER["REQUEST_METHOD"];
$input = json_decode(file_get_contents("php://input"),true);

try{
    switch($method){
        case "GET":
            if (isset($_GET["list_teams"])){
                $stmt = $pdo-> query("SELECT * FROM team ORDER BY name ASC");
                echo json_encode($stmt->fetchAll());
                exit;
            }

            $sql = "SELECT soccer_player.id, soccer_player.name, soccer_player.position, team.name as team_name
                    FROM soccer_player
                    LEFT JOIN team ON soccer_player.team = team.id
                    ORDER BY soccer_player.id DESC ";
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll());
            break;

        case "POST":
            if (!isset($input["name"]) || (!isset($input["team"])) || (!isset($input["position"]))){
                throw new Exception("Missing some data!!");
            }
            $sql = "INSERT INTO soccer_player(name, position, team) VALUES (?, ?, ?)";
            $stmt = $pdo -> prepare($sql);
            $stmt -> execute([$input["name"],$input["position"],$input["team"]]);
            echo json_encode(["message" => "Player added succesfully"]);
            break;
        
        case "PUT":
            if (!isset($input["name"]) || (!isset($input["team"])) || (!isset($input["position"]))){
                throw new Exception("Missing some data!!");
            }

            $playerId = $input["id"];
            $playerName = $input["name"];
            $playerPosition = $input["position"];
            $playerTeam = $input["team"];

            try{
                $pdo -> beginTransaction();
                $stmtGet = $pdo -> prepare("SELECT * FROM soccer_player WHERE id = ?");
                $stmtGet -> execute([$playerId]);
                $currentData = $stmtGet -> fetch();

                if(!$currentData){
                    throw new Exception("Player not found");
                }

                $oldTeam = $currentData["team"];

                if($oldTeam != $playerTeam){
                    if($oldTeam){
                        $sqlHistory = "INSERT INTO player_history(name_id, team_id , date) VALUES (?, ?, NOW())";
                        $stmtHistory = $pdo -> prepare($sqlHistory);
                        $stmtHistory -> execute([$playerId, $oldTeam]);
                    }
                }

                $sqlUpdate = "UPDATE soccer_player SET name = ?, position=?, team=? WHERE id=?;";
                $stmtUpdate = $pdo -> prepare($sqlUpdate);
                $stmtUpdate -> execute([$playerName, $playerPosition , $playerTeam, $playerId]);

                $pdo -> commit();
                echo json_encode(["message" => "Player update succesfull"]);

            } catch(Exception $err){
                $pdo -> rollBack();
                throw $err;
            }
            
            break;

        case "DELETE":
            if(!isset($input["id"])){
                throw new exception("Missing id");
            }
            $sql = "DELETE FROM soccer_player WHERE  id = ?";
            $stmt = $pdo -> prepare($sql);
            $stmt -> execute([$input["id"]]);
            echo json_encode(["message" => "Player removed succesfully"]);
            break;

        default:
            echo json_encode(["message" => "Action not supported"]);
            break;
            

    }
} catch(Exception $err) {
    http_response_code(400);
    echo json_encode(["error" => $err -> getMessage()]);
}

?>