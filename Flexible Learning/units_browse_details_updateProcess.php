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
use Gibbon\Domain\System\DiscussionGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitSubmissionGateway;

$flexibleLearningUnitID = $_POST['flexibleLearningUnitID'] ?? '';
$flexibleLearningUnitSubmissionID = $_POST['flexibleLearningUnitSubmissionID'] ?? '';
$gibbonDiscussionID = $_POST['gibbonDiscussionID'] ?? '';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/units_browse_details.php&sidebar=true&flexibleLearningUnitID='.$flexibleLearningUnitID;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse_details.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $unitGateway = $container->get(UnitGateway::class);
    $unitSubmissionGateway = $container->get(UnitSubmissionGateway::class);
    $discussionGateway = $container->get(DiscussionGateway::class);  
    
    $comment = $_POST['comment'] ?? '';
    $data = [
        'evidenceType'     => $_POST['evidenceType'] ?? '',
        'evidenceLocation' => $_POST['evidenceLocation'] ?? $_POST['link'] ?? '',
    ];
    
    // Validate the required values are present
    if (empty($flexibleLearningUnitID) || empty($flexibleLearningUnitSubmissionID) || empty($gibbonDiscussionID) || empty($comment)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    $submission = $unitSubmissionGateway->getByID($flexibleLearningUnitSubmissionID);
    if (empty($submission) || $submission['gibbonPersonID'] != $gibbon->session->get('gibbonPersonID')) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    $discussion = $discussionGateway->getByID($gibbonDiscussionID);
    if (empty($discussion) || $discussion['gibbonPersonID'] != $gibbon->session->get('gibbonPersonID')) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Move attached file, if there is one
    if ($data['evidenceType'] == 'File' && !empty($_FILES['file']['tmp_name'])) {
        $fileUploader = new FileUploader($pdo, $gibbon->session);
        $file = $_FILES['file'] ?? null;
        $data['evidenceLocation'] = $fileUploader->uploadFromPost($file, $name);
    }

    // Update the submission
    $unitSubmissionGateway->update($flexibleLearningUnitSubmissionID, $data);

    // Update the discussion to match
    $discussionGateway->update($gibbonDiscussionID, [
        'comment' => $_POST['comment'] ?? '',
        'attachmentType' => $data['evidenceType'],
        'attachmentLocation' => $data['evidenceLocation'],
        'timestamp' => date('Y-m-d H:i:s'),
    ]);

    $URL .= empty($data['evidenceLocation'])
        ? "&return=warning1"
        : "&return=success0";

    header("Location: {$URL}");
}
