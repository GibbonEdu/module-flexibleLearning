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
UPDATE gibbonAction SET category='Manage' WHERE gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning') AND (name='Manage Categories' OR name LIKE 'Manage Units%');end
ALTER TABLE flexibleLearningUnit CHANGE major1 flexibleLearningMajorID1 int(8) unsigned zerofill DEFAULT NULL;end
ALTER TABLE flexibleLearningUnit CHANGE major2 flexibleLearningMajorID2 int(8) unsigned zerofill DEFAULT NULL;end
INSERT INTO gibbonAction SET gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning'), name='Manage Majors', precedence=0, category='Manage', description='Allows the user to manage unit majors', URLList='majors_manage.php,majors_manage_add.php,majors_manage_edit.php,majors_manage_delete.php', entryURL='majors_manage.php', entrySidebar='Y', menuShow='Y', defaultPermissionAdmin='Y', defaultPermissionTeacher='N', defaultPermissionStudent='N', defaultPermissionParent='N', defaultPermissionSupport='N', categoryPermissionStaff='Y', categoryPermissionStudent='Y', categoryPermissionParent='Y', categoryPermissionOther='Y';end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Flexible Learning' AND gibbonAction.name='Manage Majors'));end
";

// v0.3.00
$count++;
$sql[$count][0] = "0.3.00";
$sql[$count][1] = "
INSERT INTO `gibbonSetting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES ('Flexible Learning', 'expectFeedback', 'Expect Feedback', 'When enabled, participants can expect to receive feedback on their submissions.', 'N');end
INSERT INTO `gibbonSetting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES ('Flexible Learning', 'feedbackOnMessage', 'Feedback Message', 'A message to display to participants when they can expect to receive feedback.', 'Submissions to units will be collected and shared with teachers. Students can expect to receive feedback on their work.');end
INSERT INTO `gibbonSetting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES ('Flexible Learning', 'feedbackOffMessage', 'No Feedback Message', 'A message to display to participants when they should not expect to receive feedback.', 'Feedback is optional and teachers will not be notified of new submissions. Students should not expect to receive feedback. They may choose to approach a teacher and request feedback.');end
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `entrySidebar`, `menuShow`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning'), 'Manage Settings', 0, 'Admin', 'Allows a privileged user to manage Flexible Learning settings.', 'settings_manage.php','settings_manage.php', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N');end
INSERT INTO `gibbonPermission` (`gibbonRoleID` ,`gibbonActionID`) VALUES (001, (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Flexible Learning' AND gibbonAction.name='Manage Settings'));end
CREATE TABLE `flexibleLearningUnitSubmission` ( `flexibleLearningUnitSubmissionID` INT(12) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT , `flexibleLearningUnitID` INT(10) UNSIGNED ZEROFILL NOT NULL , `gibbonPersonID` INT(10) UNSIGNED ZEROFILL NOT NULL , `gibbonSchoolYearID` INT(3) UNSIGNED ZEROFILL NOT NULL , `status` ENUM('Pending','Complete') NOT NULL DEFAULT 'Complete' , `evidenceType` ENUM('File','Link') NULL DEFAULT 'Link' , `evidenceLocation` TEXT NULL , `timestampSubmitted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , `timestampFeedback` TIMESTAMP NULL , `gibbonPersonIDFeedback` INT(10) NULL , PRIMARY KEY (`flexibleLearningUnitSubmissionID`)) ENGINE = InnoDB;end
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `entrySidebar`, `menuShow`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning'),'Work Pending Feedback', 0, 'Reports', 'Allows a user to see all work for which feedback has been requested, and is still pending.', 'report_workPendingFeedback.php,units_browse_details_feedback.php','report_workPendingFeedback.php', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N');end
INSERT INTO `gibbonPermission` (`gibbonRoleID` ,`gibbonActionID`) VALUES (001, (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Flexible Learning' AND gibbonAction.name='Work Pending Feedback'));end
INSERT INTO `gibbonPermission` (`gibbonRoleID` ,`gibbonActionID`) VALUES (002, (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Flexible Learning' AND gibbonAction.name='Work Pending Feedback'));end
UPDATE `gibbonAction` SET category='Learning' WHERE gibbonAction.name='Browse Units' AND gibbonAction.gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning');end
UPDATE `gibbonAction` SET category='Admin' WHERE gibbonAction.name LIKE 'Manage %' AND gibbonAction.gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning');end
";

// v0.3.01
$count++;
$sql[$count][0] = "0.3.01";
$sql[$count][1] = "";


$array = [
    'sourceModuleName' => 'Flexible Learning',
    'sourceModuleAction' => 'Unit History_all',
    'sourceModuleInclude' => 'hook_studentProfile_unitHistory.php',
];

// v0.4.00
$count++;
$sql[$count][0] = "0.4.00";
$sql[$count][1] = "
INSERT INTO `gibbonHook` (`name`, `type`, `options`, gibbonModuleID) VALUES ('Flexible Learning', 'Student Dashboard', '".serialize($array)."', (SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning'));end
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `entrySidebar`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning'), 'My Unit History', 0, 'Learning', 'Allows a student to see all the units they have studied and are studying.', 'report_unitHistory_my.php','report_unitHistory_my.php', 'Y', 'N', 'N', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'Y');end
INSERT INTO `gibbonPermission` (`gibbonRoleID` ,`gibbonActionID`) VALUES (003, (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Flexible Learning' AND gibbonAction.name='My Unit History'));end
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `entrySidebar`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning'), 'Unit History_all', 1, 'Reports', 'Allows a user to see all units undertaken by any participant.', 'report_unitHistory.php','report_unitHistory.php', 'Y', 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N');end
INSERT INTO `gibbonPermission` (`gibbonRoleID` ,`gibbonActionID`) VALUES (001, (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Flexible Learning' AND gibbonAction.name='Unit History_all'));end
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `entrySidebar`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning'), 'Unit History_myChildren', 0, 'Learning', 'Allows a user to see all units undertaken by their own children.', 'report_unitHistory.php','report_unitHistory.php', 'Y', 'N', 'N', 'N', 'Y', 'N', 'N', 'N', 'Y', 'N');end
INSERT INTO `gibbonPermission` (`gibbonRoleID` ,`gibbonActionID`) VALUES (004, (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Flexible Learning' AND gibbonAction.name='Unit History_myChildren'));end
";
