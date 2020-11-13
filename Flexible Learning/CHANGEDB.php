<?php
// USE ;end TO SEPARATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = [];
$count = 0;

// v0.1.00
$sql[$count][0] = "0.1.00";
$sql[$count][1] = "";

// v0.1.01
$count++;
$sql[$count][0] = "0.1.01";
$sql[$count][1] = "";

// v0.1.02
$count++;
$sql[$count][0] = "0.1.02";
$sql[$count][1] = "
UPDATE gibbonAction SET URLList='units_manage.php,units_manage_add.php,units_manage_edit.php,units_manage_delete.php' WHERE gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning') AND name='Manage Units';end
UPDATE gibbonAction SET URLList='units_browse.php,units_browse_details.php' WHERE gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning') AND name='Browse Units';end
";
