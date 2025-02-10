<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http:// www.gnu.org/licenses/>.
*/

// This file describes the module, including database tables

// Basic variables
$name        = 'Flexible Learning';
$description = 'View, create and manage Flexible Learning units.';
$entryURL    = "units_browse.php";
$type        = "Additional";
$category    = 'Learn';
$version     = '1.3.00';
$author      = "Gibbon Foundation";
$url         = "https://gibbonedu.org";

// Module tables & gibbonSettings entries
$moduleTables[] = "CREATE TABLE `flexibleLearningUnit` (
  `flexibleLearningUnitID` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `course` varchar(50) DEFAULT NULL,
  `name` varchar(40) NOT NULL,
  `logo` text,
  `active` enum('Y','N') DEFAULT 'Y',
  `offline` ENUM('N','Y') NOT NULL DEFAULT 'N',
  `grouping` varchar(255) NOT NULL,
  `gibbonYearGroupIDMinimum` int(3) unsigned zerofill DEFAULT NULL,
  `blurb` text NOT NULL,
  `outline` text NOT NULL,
  `flexibleLearningCategoryID` int(11) unsigned zerofill DEFAULT NULL,
  `license` varchar(50) DEFAULT NULL,
  `flexibleLearningMajorID1` int(8) unsigned zerofill DEFAULT NULL,
  `flexibleLearningMajorID2` int(8) unsigned zerofill DEFAULT NULL,
  `minor1` varchar(30) DEFAULT NULL,
  `minor2` varchar(30) DEFAULT NULL,
  `availableStudent` ENUM('No','Read','Record') NOT NULL DEFAULT 'Record',
  `availableStaff` ENUM('No','Read','Record') NOT NULL DEFAULT 'Read',
  `availableParent` ENUM('No','Read','Record') NOT NULL DEFAULT 'Read',
  `availableOther` ENUM('No','Read','Record') NOT NULL DEFAULT 'Read',
  `gibbonPersonIDCreator` int(10) unsigned zerofill NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`flexibleLearningUnitID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$moduleTables[] = "CREATE TABLE `flexibleLearningUnitBlock` (
  `flexibleLearningUnitBlockID` int(12) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `flexibleLearningUnitID` int(10) unsigned zerofill NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `length` varchar(3) NULL DEFAULT NULL,
  `contents` text NOT NULL,
  `teachersNotes` text NOT NULL,
  `sequenceNumber` int(4) NOT NULL,
  PRIMARY KEY (`flexibleLearningUnitBlockID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$moduleTables[] = "CREATE TABLE `flexibleLearningCategory` (
  `flexibleLearningCategoryID` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `color` varchar(7) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `description` text,
  `sequenceNumber` int(2) DEFAULT NULL,
  `active` enum('Y','N') DEFAULT 'Y',
  PRIMARY KEY (`flexibleLearningCategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$moduleTables[] = "CREATE TABLE `flexibleLearningMajor` (
  `flexibleLearningMajorID` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`flexibleLearningMajorID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$moduleTables[] = "CREATE TABLE `flexibleLearningUnitSubmission` (
    `flexibleLearningUnitSubmissionID` INT(12) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `flexibleLearningUnitID` INT(10) UNSIGNED ZEROFILL NOT NULL,
    `gibbonPersonID` INT(10) UNSIGNED ZEROFILL NOT NULL,
    `gibbonSchoolYearID` INT(3) UNSIGNED ZEROFILL NOT NULL,
    `status` ENUM('Pending','Complete') NOT NULL DEFAULT 'Complete',
    `evidenceType` ENUM('File','Link') NULL DEFAULT 'Link',
    `evidenceLocation` TEXT NULL,
    `timestampSubmitted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `timestampFeedback` TIMESTAMP NULL,
    `gibbonPersonIDFeedback` INT(10) NULL,
    PRIMARY KEY (`flexibleLearningUnitSubmissionID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

// Add gibbonSettings entries
$gibbonSetting[] = "INSERT INTO `gibbonSetting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES ('Flexible Learning', 'expectFeedback', 'Expect Feedback', 'When enabled, students can expect to receive feedback on their submissions.', 'N');";
$gibbonSetting[] = "INSERT INTO `gibbonSetting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES ('Flexible Learning', 'feedbackOnMessage', 'Feedback Message', 'A message to display to participants when they can expect to receive feedback.', 'Submissions to units will be collected and shared with teachers. Students can expect to receive feedback on their work.');";
$gibbonSetting[] = "INSERT INTO `gibbonSetting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES ('Flexible Learning', 'feedbackOffMessage', 'No Feedback Message', 'A message to display to participants when they should not expect to receive feedback.', 'Feedback is optional and teachers will not be notified of new submissions. Students should not expect to receive feedback. They may choose to approach a teacher and request feedback.');";
$gibbonSetting[] = "INSERT INTO `gibbonNotificationEvent` (`event`, `moduleName`, `actionName`, `type`, `scopes`, `active`) VALUES ('New Flexible Learning Unit', 'Flexible Learning', 'Manage Units', 'Core', 'All', 'Y');";
$gibbonSetting[] = "INSERT INTO `gibbonSetting` (`scope` ,`name` ,`nameDisplay` ,`description` ,`value`) VALUES ('Flexible Learning', 'unitOutlineTemplate', 'Unit Outline Template', 'An HTML template to be used as the default for all new units.', '');";
$gibbonSetting[] = "INSERT INTO `gibbonSetting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES ('Flexible Learning', 'bookletName', 'Booklet Name', 'The name of the booklet on the front cover', 'Offline Activity Booklet');";
$gibbonSetting[] = "INSERT INTO `gibbonSetting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES ('Flexible Learning', 'bookletIntroduction', 'Introduction', 'This text will be displayed on the second page of the booklet', '');";
$gibbonSetting[] = "INSERT INTO `gibbonSetting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES ('Flexible Learning', 'bookletChapters', 'Include chapter pages?', '', 'N');";
$gibbonSetting[] = "INSERT INTO `gibbonSetting` (`scope`, `name`, `nameDisplay`, `description`, `value`) VALUES ('Flexible Learning', 'bookletMargins', 'Inside margins', 'For booklet printing, when side or saddle stitched', '20');";

// Action rows
$actionRows[] = [
    'name'                      => 'Manage Units_all', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '1',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Admin', // Optional: subgroups for the right hand side module menu
    'description'               => 'Allows a user to manage all units within the system.', // Text description
    'URLList'                   => 'units_manage.php,units_manage_add.php,units_manage_edit.php,units_manage_delete.php', // List of pages included in this action
    'entryURL'                  => 'units_manage.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'Y', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'N', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'N', // Default permission for built in role Student
    'defaultPermissionParent'   => 'N', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'N', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'N', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'N', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'N', // Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Manage Units_my', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Admin', // Optional: subgroups for the right hand side module menu
    'description'               => 'Allows a user to manage their own units.', // Text description
    'URLList'                   => 'units_manage.php,units_manage_add.php,units_manage_edit.php,units_manage_delete.php', // List of pages included in this action
    'entryURL'                  => 'units_manage.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'N', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'Y', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'N', // Default permission for built in role Student
    'defaultPermissionParent'   => 'N', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'N', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'Y', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'Y', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'Y', // Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Manage Categories', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Admin', // Optional: subgroups for the right hand side module menu
    'description'               => 'Allows a user to manage unit categories.', // Text description
    'URLList'                   => 'categories_manage.php,categories_manage_add.php,categories_manage_edit.php,categories_manage_editOrderAjax.php,categories_manage_delete.php', // List of pages included in this action
    'entryURL'                  => 'categories_manage.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'Y', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'Y', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'N', // Default permission for built in role Student
    'defaultPermissionParent'   => 'N', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'Y', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'N', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'N', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'N', // Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Browse Units', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Learning', // Optional: subgroups for the right hand side module menu
    'description'               => 'Browse the selection of Flexible Learning units in a grid.', // Text description
    'URLList'                   => 'units_browse.php,units_browse_details.php', // List of pages included in this action
    'entryURL'                  => 'units_browse.php', // The landing action for the page.
    'entrySidebar'              => 'N', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'Y', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'Y', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'N', // Default permission for built in role Student
    'defaultPermissionParent'   => 'N', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'Y', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'Y', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'Y', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'Y', // Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Manage Majors', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Admin', // Optional: subgroups for the right hand side module menu
    'description'               => 'Allows the user to manage unit majors.', // Text description
    'URLList'                   => 'majors_manage.php,majors_manage_add.php,majors_manage_edit.php,majors_manage_delete.php', // List of pages included in this action
    'entryURL'                  => 'majors_manage.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'Y', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'N', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'N', // Default permission for built in role Student
    'defaultPermissionParent'   => 'N', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'N', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'N', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'N', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'N', // Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Manage Settings', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Admin', // Optional: subgroups for the right hand side module menu
    'description'               => 'Allows a privileged user to manage Flexible Learning settings.', // Text description
    'URLList'                   => 'settings_manage.php', // List of pages included in this action
    'entryURL'                  => 'settings_manage.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'Y', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'N', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'N', // Default permission for built in role Student
    'defaultPermissionParent'   => 'N', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'N', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'N', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'N', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'N', // Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Printable Booklet', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Admin', // Optional: subgroups for the right hand side module menu
    'description'               => 'Enables creating a printable PDF of offline units.', // Text description
    'URLList'                   => 'booklet_manage.php', // List of pages included in this action
    'entryURL'                  => 'booklet_manage.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'Y', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'N', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'N', // Default permission for built in role Student
    'defaultPermissionParent'   => 'N', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'N', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'N', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'N', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'N', // Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Work Pending Feedback', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Reports', // Optional: subgroups for the right hand side module menu
    'description'               => 'Allows a user to see all work for which feedback has been requested, and is still pending.', // Text description
    'URLList'                   => 'report_workPendingFeedback.php,units_browse_details_feedback.php', // List of pages included in this action
    'entryURL'                  => 'report_workPendingFeedback.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'Y', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'Y', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'N', // Default permission for built in role Student
    'defaultPermissionParent'   => 'N', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'N', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'Y', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'N', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'N', // Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Unit History_all', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '1',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Reports', // Optional: subgroups for the right hand side module menu
    'description'               => 'Allows a user to see all units undertaken by any participant.', // Text description
    'URLList'                   => 'report_unitHistory.php,hook_studentProfile_unitHistory.php', // List of pages included in this action
    'entryURL'                  => 'report_unitHistory.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'Y', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'Y', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'N', // Default permission for built in role Student
    'defaultPermissionParent'   => 'N', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'N', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'N', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'N', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'N', // Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Unit History_myChildren', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Learning', // Optional: subgroups for the right hand side module menu
    'description'               => 'Allows a user to see all units undertaken by their own children.', // Text description
    'URLList'                   => 'report_unitHistory.php,hook_studentProfile_unitHistory.php', // List of pages included in this action
    'entryURL'                  => 'report_unitHistory.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'N', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'N', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'N', // Default permission for built in role Student
    'defaultPermissionParent'   => 'Y', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'N', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'N', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'N', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'Y', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'N', // Should this action be available to user roles in the Other category?
];


$actionRows[] = [
    'name'                      => 'My Unit History', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Learning', // Optional: subgroups for the right hand side module menu
    'description'               => 'Allows a user to see all the units they have studied and are studying.', // Text description
    'URLList'                   => 'report_unitHistory_my.php,hook_studentProfile_unitHistory.php', // List of pages included in this action
    'entryURL'                  => 'report_unitHistory_my.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
    'menuShow'                  => 'Y', // Whether or not this action shows up in menus or if it's hidden
    'defaultPermissionAdmin'    => 'N', // Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'N', // Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'Y', // Default permission for built in role Student
    'defaultPermissionParent'   => 'N', // Default permission for built in role Parent
    'defaultPermissionSupport'  => 'N', // Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', // Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'Y', // Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'Y', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'Y', // Should this action be available to user roles in the Other category?
];

// Hooks
$array = [
    'sourceModuleName' => 'Flexible Learning',
    'sourceModuleAction' => 'Unit History_all,Unit History_myChildren,My Unit History',
    'sourceModuleInclude' => 'hook_studentProfile_unitHistory.php',
];
$hooks[] = "INSERT INTO `gibbonHook` (`name`, `type`, `options`, gibbonModuleID) VALUES ('Flexible Learning', 'Student Profile', '".serialize($array)."', (SELECT gibbonModuleID FROM gibbonModule WHERE name='Flexible Learning'));";
