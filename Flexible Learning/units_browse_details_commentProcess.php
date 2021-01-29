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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

require_once '../../gibbon.php';

use Gibbon\FileUploader;
use Gibbon\Services\Format;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\Students\StudentGateway;
use Gibbon\Domain\System\DiscussionGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitSubmissionGateway;

$flexibleLearningUnitID = $_POST['flexibleLearningUnitID'] ?? '';
$flexibleLearningUnitSubmissionID = $_POST['flexibleLearningUnitSubmissionID'] ?? '';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/units_browse_details.php&sidebar=true&flexibleLearningUnitID='.$flexibleLearningUnitID.'&flexibleLearningUnitSubmissionID='.$flexibleLearningUnitSubmissionID;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/report_unitHistory.php', 'Unit History_myChildren') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $unitSubmissionGateway = $container->get(UnitSubmissionGateway::class);
    $studentGateway = $container->get(StudentGateway::class);

    // Validate the required values are present
    $comment = $_POST['comment'] ?? '';
    $comment = trim(preg_replace('/^<p>|<\/p>$/i', '', $comment));

    if (empty($comment)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }
    
    // Validate the database relationships exist
    $submission = $unitSubmissionGateway->getByID($flexibleLearningUnitSubmissionID);
    if (empty($submission)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Validate that this parent has access to this student
    $children = $studentGateway
        ->selectAnyStudentsByFamilyAdult($gibbon->session->get('gibbonSchoolYearID'), $gibbon->session->get('gibbonPersonID'))
        ->fetchGroupedUnique();

    if (empty($children[$submission['gibbonPersonID']])) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit;
    }

    // Insert discussion records
    $discussionGateway = $container->get(DiscussionGateway::class);          
    $inserted = $discussionGateway->insert([
        'foreignTable'         => 'flexibleLearningUnitSubmission',
        'foreignTableID'       => $flexibleLearningUnitSubmissionID,
        'gibbonModuleID'       => getModuleIDFromName($connection2, 'Flexible Learning'),
        'gibbonPersonID'       => $gibbon->session->get('gibbonPersonID'),
        'gibbonPersonIDTarget' => $submission['gibbonPersonID'],
        'comment'              => $comment,
        'type'                 => 'Comment',
        'tag'                  => 'dull',
    ]);

    $URL .= !$inserted
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
