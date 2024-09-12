<?php

use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitBlockGateway;
use Gibbon\Comms\NotificationEvent;
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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/units_manage_add.php&gibbonDepartmentID='.$_GET['gibbonDepartmentID'].'&name='.$_GET['name'];

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_manage_add.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (!isset($_POST)) {
        //Fail 5
        $URL .= '&return=error5';
        header("Location: {$URL}");
        return;
    }

    // Proceed!
    $unitGateway = $container->get(UnitGateway::class);
    $unitBlockGateway = $container->get(UnitBlockGateway::class);
    $partialFail = false;

    $data = [
        'name'                       => $_POST['name'] ?? '',
        'flexibleLearningCategoryID' => $_POST['flexibleLearningCategoryID'] ?? '',
        'blurb'                      => $_POST['blurb'] ?? '',
        'license'                    => $_POST['license'] ?? '',
        'logo'                       => $_POST['logo'] ?? '',
        'flexibleLearningMajorID1'   => (!empty($_POST['flexibleLearningMajorID1'])) ? $_POST['flexibleLearningMajorID1'] : null,
        'flexibleLearningMajorID2'   => (!empty($_POST['flexibleLearningMajorID2'])) ? $_POST['flexibleLearningMajorID2'] : null,
        'minor1'                     => $_POST['minor1'] ?? '',
        'minor2'                     => $_POST['minor2'] ?? '',
        'active'                     => $_POST['active'] ?? '',
        'outline'                    => $_POST['outline'] ?? '',
        'availableStudent'           => $_POST['availableStudent'] ?? 'No',
        'availableStaff'             => $_POST['availableStaff'] ?? 'No',
        'availableParent'            => $_POST['availableParent'] ?? 'No',
        'availableOther'             => $_POST['availableOther'] ?? 'No',
        'gibbonPersonIDCreator'      => $_POST['gibbonPersonIDCreator'] ?? $session->get('gibbonPersonID'),
    ];

    // Validate the required values are present
    if (empty($data['name']) || empty($data['active']) || empty($data['flexibleLearningMajorID1'])) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
        return;
    }

    //Move attached file, if there is one
    if (!empty($_FILES['file']['tmp_name'])) {
        $fileUploader = new Gibbon\FileUploader($pdo, $session);
        $fileUploader->getFileExtensions('Graphics/Design');

        $file = $_FILES['file'] ?? null;

        // Upload the file, return the /uploads relative path
        $data['logo'] = $fileUploader->uploadFromPost($file, $data, $data['name']);

        if (empty($data['logo'])) {
            $partialFail = true;
        }
    }

    // Create the record
    if (!$flexibleLearningUnitID = $unitGateway->insert($data)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Notify when a new unit has been created

    $event = new NotificationEvent('Flexible Learning', 'New Flexible Learning Unit');

    $event->setNotificationText(__('A new Flexible Learning Unit, {name} has been created.', [
      'name' => $data['name']
    ]));

    $event->setActionLink("/index.php?q=/modules/Flexible Learning/units_browse_details.php&sidebar=true&flexibleLearningUnitID=$flexibleLearningUnitID");

    // Send all notifications
                    $sendReport = $event->sendNotifications($pdo, $session);

    // ADD BLOCKS
    $order = $_POST['order'] ?? [];
    $sequenceNumber = 0;

    foreach ($order as $i) {
        $data = [
            'flexibleLearningUnitID' => $flexibleLearningUnitID,
            'title'                  => $_POST["title$i"] ?? '',
            'type'                   => $_POST["type$i"] ?? '',
            'length'                 => !empty($_POST["length$i"]) ? intval(trim($_POST["length$i"])) : null,
            'contents'               => $_POST["contents$i"] ?? '',
            'teachersNotes'          => $_POST["teachersNotes$i"] ?? '',
            'sequenceNumber'         => $sequenceNumber,
        ];

        if (empty($data['title']) && empty($data['contents'])) continue;

        $partialFail &= !$unitBlockGateway->insert($data);
        $sequenceNumber++;
    }

    $URL .= $partialFail
        ? '&return=error6'
        : '&return=success0&editID='.$flexibleLearningUnitID;

    header("Location: {$URL}");
}
