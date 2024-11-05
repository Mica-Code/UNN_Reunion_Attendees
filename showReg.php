<?php
include_once('db.php');

$sql = "SELECT * FROM students";
$result = $conn->query($sql);

$yesCount = $noCount = $totalCount = 0;

while($useHere = $result->fetch_assoc()){
    if($useHere['designGenerated'] == 1){
        $yesCount++;
    }
    else{
        $noCount++;
    }
    $totalCount++;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UNN Reunion Attendees</title>
    <!-- Include DataTables CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
</head>
<body>
    <h1>UNN Reunion Attendees</h1>
    <button id="delete-selected" style="
    padding: 8px;
    margin: 20px 0px;
    background-color: #851515;
    font-size: 14px;
    color: white;
    border: none;
    border-radius: 5px;
">Delete Multiple Records</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<span style="font-size:24px"><strong> Summary:</strong></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<span style="font-size:20px; color:green"><strong>Total Yes = <?= $yesCount; ?></strong></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<span style="font-size:20px; color:red"><strong>Total No = <?= $noCount; ?></strong></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<span style="font-size:20px; color:black"><strong>Total On List = <?= $totalCount; ?></strong></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <table id="example" class="display">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>SN</th>
                <th>ID</th>
                <th>FullName</th>
                <th>Is Design Generated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $sql = "SELECT * FROM students";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td><input type='checkbox' class='row-select' data-id='" . $row["studentID"] . "'></td>
                            <td>" . $row["studentID"] . "</td>
                            <td>" . $row["studentID"] . "</td>
                            <td>" . $row["FullName"] . "</td>
                            <td>" . (($row["designGenerated"] == 0) ? 'No' : 'Yes') . "</td>
                            <td><button class='delete-btn' style='
    color: white;
    padding: 7px 10px;
    background-color: #851515;
    border-radius: 5px;
    border: none;
' data-id='" . $row["studentID"] . "'>Delete</button></td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No data found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            var table = $('#example').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excelHtml5', 'pdf'
                ]
            });

            // Handle individual row delete
            $('#example').on('click', '.delete-btn', function() {
                    var studentID = $(this).data('id');
                    // AJAX call to delete the row from the database
                    if (confirm('Are you sure you want to delete this record?')) {
                    $.ajax({
                        url: 'delete.php',
                        type: 'POST',
                        data: { id: studentID },
                        success: function(response) {
                            if (response == 'success') {
                                // Remove the row from the table
                                table.row($(this).parents('tr')).remove().draw();
                                alert('Delete was successful.');
                            } else {
                                alert('Failed to delete the record.');
                            }
                        }.bind(this)
                    });
                }
            });

            // Handle select all checkbox
            $('#select-all').on('click', function() {
                var rows = table.rows({ 'search': 'applied' }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            // Handle delete selected rows
            $('#delete-selected').on('click', function() {
                var ids = [];
                table.$('input.row-select:checked').each(function() {
                    ids.push($(this).data('id'));
                });
                if (ids.length > 0) {
                    if (confirm('Are you sure you want to delete the selected records?')) {
                        // AJAX call to delete the rows from the database
                        $.ajax({
                            url: 'delete_multiple.php',
                            type: 'POST',
                            data: { ids: ids },
                            success: function(response) {
                                if (response == 'success') {
                                    // Remove the rows from the table
                                    table.rows('.selected').remove().draw();
                                    alert('Delete was successful.');
                                } else {
                                    alert('Failed to delete the records.');
                                }
                            }
                        });
                    }
                }
            });

            // Add selected class to selected rows
            $('#example tbody').on('click', 'input.row-select', function() {
                $(this).closest('tr').toggleClass('selected');
            });
        });
    </script>
</body>
</html>
