<?php
foreach ($stmt as $row) {
?><div class="main-column-projects-project">
        <i class="material-icons">label</i>
        <p><?php echo $row[1]; ?></p>
        <i class="material-icons">delete_forever</i>
    </div><?php
        }
            ?>



<?php

include("../DB.php");

const dsn = 'mysql:dbname=todo_2021_7;host=localhost;port=3306';
const username = 'root';
const password = '';
//$pdo = new PDO(DB::dsn, DB::username, DB::password);
$pdo = new PDO(dsn, username, password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT `project_id`, `project_name` FROM `project` WHERE `project_status` = 1";
$stmt = $pdo->query($sql);



?>