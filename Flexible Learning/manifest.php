<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

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
$version     = '0.1.03';
$author      = 'Harry Merrett';
$url         = '';

// Module tables & gibbonSettings entries
$moduleTables[] = "CREATE TABLE `flexibleLearningUnit` (
  `flexibleLearningUnitID` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `course` varchar(50) DEFAULT NULL,
  `name` varchar(40) NOT NULL,
  `logo` text,
  `active` enum('Y','N') DEFAULT 'Y',
  `grouping` varchar(255) NOT NULL,
  `gibbonYearGroupIDMinimum` int(3) unsigned zerofill DEFAULT NULL,
  `blurb` text NOT NULL,
  `outline` text NOT NULL,
  `flexibleLearningCategoryID` int(11) unsigned zerofill DEFAULT NULL,
  `license` varchar(50) DEFAULT NULL,
  `major1` varchar(30) DEFAULT NULL,
  `major2` varchar(30) DEFAULT NULL,
  `minor1` varchar(30) DEFAULT NULL,
  `minor2` varchar(30) DEFAULT NULL,
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

// Add gibbonSettings entries
// /$gibbonSetting[] = "";

// Action rows
$actionRows[] = [
    'name'                      => 'Manage Units_all', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '1',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Units', // Optional: subgroups for the right hand side module menu
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
    'categoryPermissionParent'  => 'Y', // Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'Y', // Should this action be available to user roles in the Other category?
];

$actionRows[] = [
    'name'                      => 'Manage Units_my', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Units', // Optional: subgroups for the right hand side module menu
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
    'category'                  => 'Units', // Optional: subgroups for the right hand side module menu
    'description'               => 'test', // Text description
    'URLList'                   => 'categories_manage.php,categories_manage_add.php,categories_manage_edit.php,categories_manage_editOrderAjax.php','categories_manage_delete.php', // List of pages included in this action
    'entryURL'                  => 'categories_manage.php', // The landing action for the page.
    'entrySidebar'              => 'Y', // Whether or not there's a sidebar on entry to the action
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
    'name'                      => 'Browse Units', // The name of the action (appears to user in the right hand side module menu)
    'precedence'                => '0',// If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Units', // Optional: subgroups for the right hand side module menu
    'description'               => 'test', // Text description
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

// Hooks
//$hooks[] = ''; // Serialised array to create hook and set options. See Hooks documentation online.
