<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Management</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .box { background: #f4f4f4; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        input, select { padding: 8px; margin-right: 10px; margin-bottom: 10px; width: 200px; }
        button { padding: 8px 15px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px;}
        button.delete { background: #dc3545; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: white; }
        #msg { padding: 10px; margin-bottom: 10px; display: none; border-radius: 4px; }
        .error { background: #ffcccc; color: #990000; }
        .success { background: #ccffcc; color: #006600; }
    </style>
</head>
<body>

    <h2>Student Register</h2>
    <div id="msg"></div>

    <div class="box">
        <h3>Add New Student</h3>
        <input type="text" id="name" placeholder="Full Name">
        
        <select id="class_id">
            <option value="">Loading classes...</option>
        </select>
        
        <input type="date" id="dob">
        <button onclick="addStudent()">Save Student</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Class Name</th>
                <th>Date of Birth</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="studentTable"></tbody>
    </table>

<script>
    const API_URL = 'api.php';
   
    document.addEventListener('DOMContentLoaded', () => {  // Load Students AND load the Class Dropdown on startup
        loadClasses();
        loadStudents();
    });

    
    async function loadClasses() {  //Load the list of classes for the dropdown
        try {
            
            const response = await fetch(API_URL + '?list_classes=1');
            const data = await response.json();
            
            const select = document.getElementById('class_id');
            select.innerHTML = '<option value="">Select a Class...</option>';
            
            data.forEach(cls => {  // Populate the dropdown
                select.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
            });

        } catch (err) {
            console.error("Error loading classes:", err);
        }
    }

    async function loadStudents() {
        try {
            const response = await fetch(API_URL); 
            const text = await response.text(); 
            try { var data = JSON.parse(text); } 
            catch (e) { throw new Error("Server Error: " + text); }

            if (data.error) throw new Error(data.error);

            const tbody = document.getElementById('studentTable');
            tbody.innerHTML = '';

            data.forEach(student => {
                // Here 'class_name' comes from the LEFT JOIN in PHP
                const className = student.class_name ? student.class_name : 'No Class';

                tbody.innerHTML += `
                    <tr>
                        <td>${student.id}</td>
                        <td>${student.name}</td>
                        <td><b>${className}</b></td> 
                        <td>${student.dob}</td>
                        <td>
                            <button class="delete" onclick="deleteStudent(${student.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        } catch (err) {
            showMsg(err.message, 'error');
        }
    }

    async function addStudent() {
        const name = document.getElementById('name').value;
        const classId = document.getElementById('class_id').value; // We get the ID (1, 2...), not the name
        const dob = document.getElementById('dob').value;

        if(!name || !classId || !dob) { alert("missing some data !!"); return; }

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name, class_id: classId, dob: dob })
            });
            
            const data = await response.json();
            if (data.error) throw new Error(data.error);

            showMsg(data.message, 'success');
            loadStudents(); 
            document.getElementById('name').value = '';
        } catch (err) {
            showMsg(err.message, 'error');
        }
    }

    async function deleteStudent(id) {
        if(!confirm("Delete this student?")) return;
        try {
            const response = await fetch(API_URL, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            showMsg(data.message, 'success');
            loadStudents();
        } catch (err) {
            showMsg(err.message, 'error');
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