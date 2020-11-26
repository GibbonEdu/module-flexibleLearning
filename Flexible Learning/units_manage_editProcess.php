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

use Gibbon\Services\Format;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;

$flexibleLearningUnitID = $_POST['flexibleLearningUnitID'] ?? '';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/units_manage_edit.php&flexibleLearningUnitID='.$flexibleLearningUnitID;

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


    $data = [
        'name'          => $_POST['name'] ?? '',
        'flexibleLearningCategoryID'         => $_POST['flexibleLearningCategoryID'] ?? '',
        'blurb'         => $_POST['blurb'] ?? '',
        'license'         => $_POST['license'] ?? '',
        'flexibleLearningMajorID1' => (!empty($_POST['flexibleLearningMajorID1'])) ? $_POST['flexibleLearningMajorID1'] : null ,
        'flexibleLearningMajorID2' => (!empty($_POST['flexibleLearningMajorID2'])) ? $_POST['flexibleLearningMajorID2'] : null ,
        'minor1'         => $_POST['minor1'] ?? '',
        'minor2'         => $_POST['minor2'] ?? '',
        'active'        => $_POST['active'] ?? '',
        'outline'         => $_POST['outline'] ?? '',
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
    }
    else {
      $values = $unitGateway->getUnitByID($flexibleLearningUnitID, $gibbon->session->get('gibbonPersonID'));
    }

    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    //Move attached file, if there is one
    $attachment = null;
    if (!empty($_FILES['file']['tmp_name'])) {
        $fileUploader = new Gibbon\FileUploader($pdo, $gibbon->session);
        $fileUploader->getFileExtensions('Graphics/Design');

        $file = $_FILES['file'] ?? null;

        // Upload the file, return the /uploads relative path
        $data['logo'] = $fileUploader->uploadFromPost($file, $name);

        if (empty($data['logo'])) {
            $partialFail = true;
        }

    }
    else {
      $data['logo']=$_POST['logo'];
    }

    // Create the record
    if (!$unitGateway->update($flexibleLearningUnitID, $data)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    //Update blocks
    $order = $_POST['order'] ?? [];
    $sequenceNumber = 0;
    $dataRemove = array();
    $whereRemove = '';
    if (count($order) < 0) {
        //Fail 3
        $URL .= '&addReturn=error3';
        header("Location: {$URL}");
    } else {
        if (is_array($order)) {
            foreach ($order as $i) {
                $title = '';
                if ($_POST["title$i"] != "Block $i") {
                    $title = $_POST["title$i"];
                }
                $type2 = '';
                if ($_POST["type$i"] != 'type (e.g. discussion, outcome)') {
                    $type2 = $_POST["type$i"];
                }
                $length = (!empty($_POST["length$i"]) && is_numeric($_POST["length$i"])) ? intval(trim($_POST["length$i"])) : null;
                $contents = trim($_POST["contents$i"]);
                $teachersNotes = $_POST["teachersNotes$i"];
                $flexibleLearningUnitBlockID = $_POST["flexibleLearningUnitBlockID$i"] ?? '' ;

                if ($flexibleLearningUnitBlockID != '') {
                    try {
                        $dataBlock = array('flexibleLearningUnitID' => $flexibleLearningUnitID, 'title' => $title, 'type' => $type2, 'length' => $length, 'contents' => $contents, 'teachersNotes' => $teachersNotes, 'sequenceNumber' => $sequenceNumber, 'flexibleLearningUnitBlockID' => $flexibleLearningUnitBlockID);
                        $sqlBlock = 'UPDATE flexibleLearningUnitBlock SET flexibleLearningUnitID=:flexibleLearningUnitID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber WHERE flexibleLearningUnitBlockID=:flexibleLearningUnitBlockID';
                        $resultBlock = $connection2->prepare($sqlBlock);
                        $resultBlock->execute($dataBlock);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    $dataRemove["flexibleLearningUnitBlockID$sequenceNumber"] = $flexibleLearningUnitBlockID;
                    $whereRemove .= "AND NOT flexibleLearningUnitBlockID=:flexibleLearningUnitBlockID$sequenceNumber ";
                } else {
                    try {
                        $dataBlock = array('flexibleLearningUnitID' => $flexibleLearningUnitID, 'title' => $title, 'type' => $type2, 'length' => $length, 'contents' => $contents, 'teachersNotes' => $teachersNotes, 'sequenceNumber' => $sequenceNumber);
                        $sqlBlock = 'INSERT INTO flexibleLearningUnitBlock SET flexibleLearningUnitID=:flexibleLearningUnitID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber';
                        $resultBlock = $connection2->prepare($sqlBlock);
                        $resultBlock->execute($dataBlock);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    $dataRemove["flexibleLearningUnitBlockID$sequenceNumber"] = $connection2->lastInsertId();
                    $whereRemove .= "AND NOT flexibleLearningUnitBlockID=:flexibleLearningUnitBlockID$sequenceNumber ";
                }

                ++$sequenceNumber;
            }
        }
    }

    //Remove orphaned blocks
    if ($whereRemove != '(') {
        try {
            $dataRemove['flexibleLearningUnitID'] = $flexibleLearningUnitID;
            $sqlRemove = "DELETE FROM flexibleLearningUnitBlock WHERE flexibleLearningUnitID=:flexibleLearningUnitID $whereRemove";
            $resultRemove = $connection2->prepare($sqlRemove);
            $resultRemove->execute($dataRemove);
        } catch (PDOException $e) {
            echo $e->getMessage();
            $partialFail = true;
        }
    }

    $URL .= !$flexibleLearningUnitID
        ? "&return=error2"
        : "&return=success0&editID=$flexibleLearningUnitID";

    header("Location: {$URL}");
  }
}
