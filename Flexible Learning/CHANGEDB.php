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

// v0.1.03
$count++;
$sql[$count][0] = "0.1.03";
$sql[$count][1] = "";

// v0.1.04
$count++;
$sql[$count][0] = "0.1.04";
$sql[$count][1] = "
UPDATE gibbonAction SET name='Manage Units_all', precedence=1, defaultPermissionTeacher='N', defaultPermissionSupport='N', description='Allows a user to manage all units within the system.' WHERE name='Manage Units' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning');end
INSERT INTO gibbonAction SET gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning'), name='Manage Units_my', precedence=0, category='Units', description='Allows a user to manage their own units.', URLList='units_manage.php,units_manage_add.php,units_manage_edit.php,units_manage_delete.php', entryURL='units_manage.php', entrySidebar='Y', menuShow='Y', defaultPermissionAdmin= 'N', defaultPermissionTeacher='Y', defaultPermissionStudent='N', defaultPermissionParent='N', defaultPermissionSupport='N', categoryPermissionStaff='Y', categoryPermissionStudent='Y', categoryPermissionParent='Y', categoryPermissionOther='Y';end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '2', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Flexible Learning' AND gibbonAction.name='Manage Units_my'));end

";

// v0.1.05
$count++;
$sql[$count][0] = "0.1.05";
$sql[$count][1] = "
UPDATE gibbonAction SET URLList='categories_manage.php,categories_manage_add.php,categories_manage_edit.php,categories_manage_editOrderAjax.php,categories_manage_delete.php' WHERE gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning') AND name='Manage Categories';end
";

// v0.1.06
$count++;
$sql[$count][0] = "0.1.06";
$sql[$count][1] = "";

// v0.1.07
$count++;
$sql[$count][0] = "0.1.07";
$sql[$count][1] = "";

// v0.1.08
$count++;
$sql[$count][0] = "0.1.08";
$sql[$count][1] = "";

// v0.2.00
$count++;
$sql[$count][0] = "0.2.00";
$sql[$count][1] = "
CREATE TABLE `flexibleLearningMajor` (`flexibleLearningMajorID` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT, `name` varchar(30) DEFAULT NULL, PRIMARY KEY (`flexibleLearningMajorID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;end
UPDATE gibbonAction SET category='Additional' WHERE gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning') AND name='Manage Categories';end
ALTER TABLE flexibleLearningUnit CHANGE major1 flexibleLearningMajorID1 int(8) unsigned zerofill DEFAULT NULL;end
ALTER TABLE flexibleLearningUnit CHANGE major2 flexibleLearningMajorID2 int(8) unsigned zerofill DEFAULT NULL;end
INSERT INTO gibbonAction SET gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning'), name='Manage Majors', precedence=0, category='Additional', description='Allows the user to manage unit majors', URLList='majors_manage.php,majors_manage_add.php,majors_manage_edit.php,majors_manage_delete.php', entryURL='majors_manage.php', entrySidebar='Y', menuShow='Y', defaultPermissionAdmin='Y', defaultPermissionTeacher='Y', defaultPermissionStudent='N', defaultPermissionParent='N', defaultPermissionSupport='Y', categoryPermissionStaff='Y', categoryPermissionStudent='Y', categoryPermissionParent='Y', categoryPermissionOther='Y';end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Flexible Learning' AND gibbonAction.name='Manage Majors'));end
";
