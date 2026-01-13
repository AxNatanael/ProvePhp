<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Players Management</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .box { background: #28c7dc; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        input, select { padding: 8px; margin-right: 10px; margin-bottom: 10px; width: 200px; }
        button { padding: 8px 15px; cursor: pointer; background: #6a00ff; color: white; border: none; border-radius: 4px;}
        button.delete { background: #dc3545; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: white; }
        #msg { padding: 10px; margin-bottom: 10px; display: none; border-radius: 4px; }
        .error { background: #e7c0c0; color: #990000; }
        .success { background: #ccffcc; color: #006600; }
    </style>
</head>
<body>
    
    <?php
    include "db.php";

    $table = "soccer_player";
    $column = "position";
    
    function getEnumValues($pdo, $table, $column){
        $stmt = $pdo -> query ("SHOW COLUMNS FROM `$table` LIKE '$column' ");  //LIKE is special and need '' but FROM need `
        $result = $stmt -> fetch(PDO::FETCH_ASSOC);
        
        if(!$result) return [];
        
        $type = $result["Type"];
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        if(isset($matches[1])){
            $enumValues = str_getcsv($matches[1],',', "'");
            return $enumValues;
        }
    }
    
    $opzioni = getEnumValues($pdo,$table,$column);

    ?>

    <h2>Soccer Players</h2>
    <div id = "msg"></div>

    <div class = "box">
        <h3>Add new Player</h3>
        <input type="text" id="name" placeholder="FullName">

        <select id="position">
            <option value="">Select Position</option>
            <?php foreach ($opzioni as $valore): ?>
                <option value="<?php echo $valore; ?>">
                    <?php echo ucfirst($valore); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select id="team">
            <option value="">Loading teams....</option>
        </select>

        <button onclick="addPlayer()">Add Player</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Position</th>
                <th>team</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="playerTable"></tbody>
    </table>

<script>
    const API_URL = "api.php";

    document.addEventListener("DOMContentLoaded",() => {
        loadTeams();
        loadPlayers();
    });

    async function loadTeams() {
        try{
            const response = await fetch(API_URL + "?list_teams=1");
            const data = await response.json();
            const select = document.getElementById("team");
            select.innerHTML = "<option value=''>Select Team</option>";
            data.forEach(team => {
                select.innerHTML += `<option value="${team.id}">${team.name}</option>`
            });
        } catch(err){
            console.error("error loading teams", err);
        }
    }

    async function loadPlayers() {
        try{
            const response = await fetch(API_URL);
            const text = await response.text();
            try{ var data = JSON.parse(text);
            } catch (e) { throw new Error("Server error" + e ); }
            
            if (data.error) throw new Error(data.error);

            const tbody = document.getElementById("playerTable");
            tbody.innerHTML = "";

            data.forEach(player => {
                const playerTeam = player.team_name ? player.team_name : "No team";
                
                tbody.innerHTML += `
                    <tr>
                        <td>${player.id}</td>
                        <td>${player.name}</td>
                        <td>${player.position}</td>
                        <td>${playerTeam}</td>
                        <td>
                            <button class="delete" onclick="deletePlayer(${player.id})">Delete</button>
                        </td>
                    </tr>
                 `;
            });
        } catch(err){
            showMsg(err.message, 'error');
        }
    }
    
    async function addPlayer() {
        const name = document.getElementById("name").value;
        const position = document.getElementById("position").value;
        const teamId = document.getElementById("team").value;
        
        if(!name || !position || !teamId){ alert("missing some data!!"); return ; }

        try{
            const response = await fetch(API_URL, {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({name:name, position:position, team:teamId})    
            });

            const data = await response.json();
            if(data.error) throw new Error(data.error);

            showMsg(data.message, "success");
            loadPlayers();
            document.getElementById("name").value = "";
            document.getElementById("position").value = "";
            document.getElementById("team").value = "";
        } catch(err) {
            showMsg(err.message, "error");
        }   
    }

    async function deletePlayer(id) {
        if(!confirm("Delete The player ??")) return;
        try{
            const response = await fetch(API_URL, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await response.json();
            if(data.error) throw new Error(data.error);
            showMsg(data.message, "success");
            loadPlayers();
        } catch(err){
            showMsg(err.message, "error");
        }
    }


    function showMsg(text, type) {
        const div = document.getElementById('msg');
        div.style.display = 'block';
        div.className = type;
        div.innerText = text;
    }

</script>
</body>
</html>




