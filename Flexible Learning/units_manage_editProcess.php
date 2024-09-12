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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

require_once '../../gibbon.php';

use Gibbon\Services\Format;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitBlockGateway;

$flexibleLearningUnitID = $_POST['flexibleLearningUnitID'] ?? '';

$URL = $session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/units_manage_edit.php&flexibleLearningUnitID='.$flexibleLearningUnitID;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
  $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
  if ($highestAction == false) {
      //Fail 0
      $URL .= "&return=error0$params";
      header("Location: {$URL}");
  } else {
    // Proceed!
    $unitGateway = $container->get(UnitGateway::class);
    $unitBlockGateway = $container->get(UnitBlockGateway::class);
    $partialFail = false;

    $data = [
        'name'                       => $_POST['name'] ?? '',
        'flexibleLearningCategoryID' => $_POST['flexibleLearningCategoryID'] ?? '',
        'blurb'                      => $_POST['blurb'] ?? '',
        'license'                    => $_POST['license'] ?? '',
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
        'gibbonPersonIDCreator'      => $_POST['gibbonPersonIDCreator'] ?? '',
    ];

    // Validate the required values are present
    if (empty($data['name']) || empty($data['flexibleLearningCategoryID']) || empty($data['blurb']) || empty($data['active']) || empty($data['flexibleLearningMajorID1'])) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    if ($highestAction == 'Manage Units_all') {
      $values = $unitGateway->getUnitByID($flexibleLearningUnitID);
    } else {
      $values = $unitGateway->getUnitByID($flexibleLearningUnitID, $session->get('gibbonPersonID'));
    }

    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    //Move attached file, if there is one
    $attachment = null;
    if (!empty($_FILES['file']['tmp_name'])) {
        $fileUploader = new Gibbon\FileUploader($pdo, $session);
        $fileUploader->getFileExtensions('Graphics/Design');

        $file = $_FILES['file'] ?? null;

        // Upload the file, return the /uploads relative path
        $data['logo'] = $fileUploader->uploadFromPost($file, $data, $data['name']);

        if (empty($data['logo'])) {
            $partialFail = true;
        }

    } else {
      $data['logo']=$_POST['logo'];
    }

    // Create the record
    if (!$unitGateway->update($flexibleLearningUnitID, $data)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Update blocks
    $order = $_POST['order'] ?? [];
    $blockIDs = [];
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

        $flexibleLearningUnitBlockID = $_POST["flexibleLearningUnitBlockID$i"] ?? '';

        if (!empty($flexibleLearningUnitBlockID)) {
            $partialFail &= !$unitBlockGateway->update($flexibleLearningUnitBlockID, $data);
        } else {
            $flexibleLearningUnitBlockID = $unitBlockGateway->insert($data);
            $partialFail &= !$flexibleLearningUnitBlockID;
        }

        $blockIDs[] = str_pad($flexibleLearningUnitBlockID, 12, '0', STR_PAD_LEFT);
        $sequenceNumber++;
    }

    // Remove orphaned blocks
    if (!empty($blockIDs)) {
        $data = ['flexibleLearningUnitID' => $flexibleLearningUnitID, 'blockIDs' => implode(',', $blockIDs)];
        $sql = "DELETE FROM flexibleLearningUnitBlock WHERE flexibleLearningUnitID=:flexibleLearningUnitID AND NOT FIND_IN_SET(flexibleLearningUnitBlockID, :blockIDs)";
        $pdo->statement($sql, $data);
    }

    $URL .= $partialFail
        ? "&return=warning1"
        : "&return=success0";

    header("Location: {$URL}");
  }
}
