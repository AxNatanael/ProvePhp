<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Players Management</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">

</head>
<body class="bg-light">
    
    <?php
    include "db.php";

    $table = "soccer_player";
    $column = "position";
    
    function getEnumValues($pdo, $table, $column){
        $stmt = $pdo -> query ("SHOW COLUMNS FROM `$table` LIKE '$column' "); 
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

    <div class="container mt-5">
        
        <h2 class="mb-4 text-center text-primary">
            <i class="bi bi-trophy-fill"></i> Players manager
        </h2>

        <div id="msg" class="alert d-none" role="alert"></div>

        <div class="card shadow-sm mb-5">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Add new Player</h5>
            </div>
            <div class="card-body">
                <div class="form-row align-items-end">
                    
                    <div class="form-group col-md-4">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" placeholder="Full name">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="position">Position</label>
                        <select id="position" class="custom-select">
                            <option value="">select field position</option>
                            <?php foreach ($opzioni as $valore): ?>
                                <option value="<?php echo $valore; ?>">
                                    <?php echo ucfirst($valore); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="team">team</label>
                        <select id="team" class="custom-select">
                            <option value="">teams loading...</option>
                        </select>
                    </div>

                    <div class="form-group col-md-2">
                        <button onclick="addPlayer()" class="btn btn-success btn-block">
                            <i class="bi bi-plus-circle"></i> add
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="myTable" class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Team</th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody id="playerTable">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editModalLabel">Modify Player</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="edit-id">
                    
                    <div class="form-group">
                        <label for="edit-name">Name</label>
                        <input type="text" class="form-control" id="edit-name">
                    </div>

                    <div class="form-group">
                        <label for="edit-position">Position</label>
                        <select id="edit-position" class="custom-select">
                            <?php foreach ($opzioni as $valore): ?>
                                <option value="<?php echo $valore; ?>">
                                    <?php echo ucfirst($valore); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit-team">Team</label>
                        <select id="edit-team" class="custom-select">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveEdit()">Save changes</button>
            </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>


<script>

    const API_URL = "api.php";
    let playersData = [];

    document.addEventListener("DOMContentLoaded",() => {
        loadTeams();
        loadPlayers();
    });

    async function loadTeams() {
        try{
            const response = await fetch(API_URL + "?list_teams=1");
            const data = await response.json();
            const selectAdd = document.getElementById("team");
            const selectEdit = document.getElementById("edit-team");
            
            let options = "<option value=''>Select a team</option>";
            data.forEach(team => {
                options += `<option value="${team.id}">${team.name}</option>`;
            });

            selectAdd.innerHTML = options;
            selectEdit.innerHTML = options;
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

            playersData = data;

            if ($.fn.DataTable.isDataTable('#myTable')) {
                $('#myTable').DataTable().destroy();
            }

            const tbody = document.getElementById("playerTable");
            tbody.innerHTML = "";

            data.forEach(player => {
                const playerTeam = player.team_name ? player.team_name : "<span class='text-muted font-italic'>Nessuna squadra</span>";
                
                tbody.innerHTML += `
                    <tr>
                        <td>${player.id}</td>
                        <td><strong>${player.name}</strong></td>
                        <td><span class="badge badge-info">${player.position}</span></td>
                        <td>${playerTeam}</td>
                        <td class="text-right">
                            <button class="btn btn-warning btn-sm" onclick="modifyPlayer(${player.id})">
                                <i class="bi bi-gear"></i> update
                            </button>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-danger btn-sm" onclick="deletePlayer(${player.id})">
                                <i class="bi bi-trash"></i> delete
                            </button>
                        </td>
                    </tr>
                 `;
            });

            $('#myTable').DataTable({
                "order": [[ 1, "asc" ]],
                dom: "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4'B><'col-sm-12 col-md-4'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success btn-sm', 
                        exportOptions: { columns: ':not(:last-child)' } 
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: { columns: ':not(:last-child)' }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="bi bi-filetype-csv"></i> CSV',
                        className: 'btn btn-info btn-sm',
                        exportOptions: { columns: ':not(:last-child)' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Stampa',
                        className: 'btn btn-secondary btn-sm',
                        exportOptions: { columns: ':not(:last-child)' }
                    }
                ]
            });

        } catch(err){
            showMsg(err.message, 'error');
        }
    }
    
    async function addPlayer() {
        const name = document.getElementById("name").value;
        const position = document.getElementById("position").value;
        const teamId = document.getElementById("team").value;
        
        if(!name || !position || !teamId){ 
            showMsg("missing fields", "error"); 
            return; 
        }

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
        if(!confirm("Delete Player ??")) return;
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

    function modifyPlayer(id) {
        const player = playersData.find(p => p.id == id);
        
        if (!player) {
            showMsg("Player not found!", "error");
            return;
        }

        document.getElementById("edit-id").value = player.id;
        document.getElementById("edit-name").value = player.name;
        document.getElementById("edit-position").value = player.position;
        document.getElementById("edit-team").value = player.team; 

        
        $('#editModal').modal('show');
    }

    
    async function saveEdit() {
        const id = document.getElementById("edit-id").value;
        const name = document.getElementById("edit-name").value;
        const position = document.getElementById("edit-position").value;
        const teamId = document.getElementById("edit-team").value;

        if(!name || !position || !teamId){ 
            alert("Please fill all fields"); 
            return; 
        }

        try {
            const response = await fetch(API_URL, {
                method: "PUT",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({
                    id: id,
                    name: name, 
                    position: position, 
                    team: teamId
                })    
            });

            const data = await response.json();
            if(data.error) throw new Error(data.error);

            $('#editModal').modal('hide');
            
            showMsg(data.message, "success");
            loadPlayers(); 

        } catch(err) {
            alert("Error: " + err.message);
        }
    }

    function showMsg(text, type) {
        const div = document.getElementById('msg');
        div.innerText = text;
        
        div.classList.remove('d-none');
        div.classList.remove('alert-success');
        div.classList.remove('alert-danger');

        if (type === 'success') {
            div.classList.add('alert-success');
        } else {
            div.classList.add('alert-danger');
        }

        setTimeout(() => {
            div.classList.add('d-none');
        }, 3000);
    }

</script>
</body>
</html>