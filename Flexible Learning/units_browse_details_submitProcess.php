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
use Gibbon\Contracts\Comms\Mailer;
use Gibbon\Domain\User\FamilyGateway;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\Students\StudentGateway;
use Gibbon\Domain\System\DiscussionGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitSubmissionGateway;

$flexibleLearningUnitID = $_POST['flexibleLearningUnitID'] ?? '';

$URL = $session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/units_browse_details.php&sidebar=true&flexibleLearningUnitID='.$flexibleLearningUnitID;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse_details.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $unitGateway = $container->get(UnitGateway::class);
    $unitSubmissionGateway = $container->get(UnitSubmissionGateway::class);
    $discussionGateway = $container->get(DiscussionGateway::class);  
    $expectFeedback =  $container->get(SettingGateway::class)->getSettingByScope('Flexible Learning', 'expectFeedback') == 'Y';

    $inviteParents = $_POST['inviteParents'] ?? 'N';
    $comment = $_POST['comment'] ?? '';
    $data = [
        'flexibleLearningUnitID' => $flexibleLearningUnitID,
        'gibbonPersonID'         => $session->get('gibbonPersonID'),
        'gibbonSchoolYearID'     => $session->get('gibbonSchoolYearID'),
        'evidenceType'           => $_POST['evidenceType'] ?? '',
        'status'                 => $expectFeedback ? 'Pending' : 'Complete',
    ];

    // Validate the required values are present
    if (empty($data['flexibleLearningUnitID']) || empty($data['gibbonPersonID']) || empty($data['gibbonSchoolYearID']) || empty($data['evidenceType']) || empty($comment)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    $values = $unitGateway->getByID($flexibleLearningUnitID);
    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Check for access to this action
    $roleCategory = getRoleCategory($session->get('gibbonRoleIDCurrent'), $connection2);
    $access = $values['available'.$roleCategory] ?? 'No';
    if ($access != 'Record') {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit;
    }

    // Validate the values are unique
    if (!$unitSubmissionGateway->unique($data, ['gibbonPersonID', 'flexibleLearningUnitID'])) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit;
    }

    // Check we have a file to upload
    if ($data['evidenceType'] == 'File' && empty($_FILES['file']['tmp_name'])) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    //Move attached file, if there is one
    if ($data['evidenceType'] == 'File') {
        $fileUploader = new FileUploader($pdo, $session);
        $file = $_FILES['file'] ?? null;

        // Upload the file, return the /uploads relative path
        $data['evidenceLocation'] = $fileUploader->uploadFromPost($file, $name);
    } elseif ($data['evidenceType'] == 'Link') {
        $data['evidenceLocation'] = $_POST['link'] ?? '';
    }

    // Check that the file upload/link is present
    if (empty($data['evidenceLocation'])) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Create the record
    $flexibleLearningUnitSubmissionID = $unitSubmissionGateway->insert($data);

    // Insert discussion records     
    $discussionGateway->insert([
        'foreignTable'         => 'flexibleLearningUnitSubmission',
        'foreignTableID'       => $flexibleLearningUnitSubmissionID,
        'gibbonModuleID'       => getModuleIDFromName($connection2, 'Flexible Learning'),
        'gibbonPersonID'       => $data['gibbonPersonID'],
        'gibbonPersonIDTarget' => $data['gibbonPersonID'],
        'comment'              => $comment,
        'type'                 => 'Evidence',
        'tag'                  => 'pending',
        'attachmentType'       => $data['evidenceType'],
        'attachmentLocation'   => $data['evidenceLocation'],
    ]);

    // Invite Parents to Comment
    if ($roleCategory == 'Student') {
        $student =  $container->get(StudentGateway::class)->selectActiveStudentByPerson($session->get('gibbonSchoolYearID'), $session->get('gibbonPersonID'))->fetch();
        $familyAdults = $container->get(FamilyGateway::class)->selectFamilyAdultsByStudent($session->get('gibbonPersonID'))->fetchAll();
        $familyAdults = array_filter($familyAdults, function ($adult) {
            return $adult['contactEmail'] == 'Y' && $adult['childDataAccess'] == 'Y';
        });

        if ($inviteParents == 'Y' && !empty($student) && !empty($familyAdults)) {
            $subject = __m('{name} has invited you to comment on their Flexible Learning', ['name' => $student['preferredName']]);
            $body = __m('{name} has recently completed the Flexible Learning unit {unit}. Click below to view and comment on their work.', ['name' => $student['preferredName'], 'unit' => $values['name']]);

            $mail = $container->get(Mailer::class);
            $mail->AddReplyTo($student['email'], Format::name('', $student['preferredName'], $student['surname'], 'Student', false, true));
            foreach ($familyAdults as $adult) {
                $mail->AddAddress($adult['email'], Format::name('', $adult['preferredName'], $adult['surname'], 'Parent', false, true));
            }

            $mail->setDefaultSender($subject);
            $mail->renderBody('mail/message.twig.html', [
                'title'  => __m('Invitation to Comment'),
                'body'   => $body,
                'button' => [
                    'url'  => "index.php?q=/modules/Flexible Learning/units_browse_details.php&flexibleLearningUnitID=$flexibleLearningUnitID&flexibleLearningUnitSubmissionID=$flexibleLearningUnitSubmissionID&sidebar=true",
                    'text' => __('View'),
                ],
            ]);

            $sent = $mail->Send();
        }
    }

    $URL .= !$flexibleLearningUnitSubmissionID
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
