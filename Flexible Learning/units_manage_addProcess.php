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

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/units_manage_add.php&gibbonDepartmentID='.$_GET['gibbonDepartmentID'].'&name='.$_GET['name'];

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_manage_add.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        //Fail 0
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        if (!(isset($_POST))) {
            //Fail 5
            $URL .= '&return=error5';
            header("Location: {$URL}");
        } else {
            //Proceed!
            //Validate Inputs
            $name = $_POST['name'] ?? '' ;
            $flexibleLearningCategoryID = $_POST['flexibleLearningCategoryID'] ?? '' ;
            $blurb = $_POST['blurb'] ?? '' ;
            $license = $_POST['license'] ?? '';
            $active = $_POST['active'] ?? '' ;
            $major1 = $_POST['major1'] ?? '' ;
            $major2 = $_POST['major2'] ?? '' ;
            $minor1 = $_POST['minor1'] ?? '' ;
            $minor2 = $_POST['minor2'] ?? '' ;
            $outline = $_POST['outline'] ?? '' ;

            if ($name == '' or $active == '') {
                //Fail 3
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                $partialFail = false;

                //Move attached file, if there is one
                $attachment = null;
                if (!empty($_FILES['file']['tmp_name'])) {
                    $fileUploader = new Gibbon\FileUploader($pdo, $gibbon->session);
                    $fileUploader->getFileExtensions('Graphics/Design');

                    $file = $_FILES['file'] ?? null;

                    // Upload the file, return the /uploads relative path
                    $attachment = $fileUploader->uploadFromPost($file, $name);

                    if (empty($attachment)) {
                        $partialFail = true;
                    }

                }

                // Write to database
                $data = array('name' => $name, 'flexibleLearningCategoryID' => $flexibleLearningCategoryID, 'logo' => $attachment, 'blurb' => $blurb, 'license' => $license, 'active' => $active, 'outline' => $outline, 'major1' => $major1, 'major2' => $major2, 'minor1' => $minor1, 'minor2' => $minor2, 'gibbonPersonIDCreator' => $gibbon->session->get('gibbonPersonID'), 'timestamp' => date('Y-m-d H:i:s'));
                $sql = 'INSERT INTO flexibleLearningUnit SET name=:name, flexibleLearningCategoryID=:flexibleLearningCategoryID, logo=:logo, blurb=:blurb, license=:license, active=:active, outline=:outline, major1=:major1, major2=:major2, minor1=:minor1, minor2=:minor2, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestamp=:timestamp';
                $inserted = $pdo->insert($sql, $data);

                if (empty($inserted)) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $AI = str_pad($inserted, 10, '0', STR_PAD_LEFT);

                // Write author to database
                $data = array('flexibleLearningUnitID' => $AI, 'gibbonPersonID' => $gibbon->session->get('gibbonPersonID'), 'surname' => $gibbon->session->get('surname'), 'preferredName' => $gibbon->session->get('preferredName'), 'website' => $gibbon->session->get('website'));
                $sql = 'INSERT INTO flexibleLearningUnitAuthor SET flexibleLearningUnitID=:flexibleLearningUnitID, gibbonPersonID=:gibbonPersonID, surname=:surname, preferredName=:preferredName, website=:website';

                $inserted = $pdo->insert($sql, $data);
                $partialFail &= !$inserted;

                //ADD BLOCKS
                $blockCount = ($_POST['blockCount'] - 1);
                $sequenceNumber = 0;
                if ($blockCount > 0) {
                    $order = $_POST['order'] ?? array();
                    foreach ($order as $i) {
                        $title = '';
                        if ($_POST["title$i"] != "Block $i") {
                            $title = $_POST["title$i"];
                        }
                        $type2 = '';
                        if ($_POST["type$i"] != 'type (e.g. discussion, outcome)') {
                            $type2 = $_POST["type$i"];
                        }
                        $length = isset($_POST["length$i"]) ? intval(trim($_POST["length$i"])) : null;
                        $contents = $_POST["contents$i"];
                        $teachersNotes = $_POST["teachersNotes$i"];

                        if ($title != '' or $contents != '') {

                            $dataBlock = array('flexibleLearningUnitID' => $AI, 'title' => $title, 'type' => $type2, 'length' => $length, 'contents' => $contents, 'teachersNotes' => $teachersNotes, 'sequenceNumber' => $sequenceNumber);
                            $sqlBlock = 'INSERT INTO flexibleLearningUnitBlock SET flexibleLearningUnitID=:flexibleLearningUnitID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber';

                            $inserted = $pdo->insert($sqlBlock, $dataBlock);
                            $partialFail &= !$inserted;
                            ++$sequenceNumber;
                        }
                    }
                }

                if ($partialFail == true) {
                    //Fail 6
                    $URL .= '&return=error6';
                    header("Location: {$URL}");
                } else {
                    //Success 0
                    $URL = $URL.'&return=success0&editID='.$AI;
                    header("Location: {$URL}");
                }
            }
        }
    }
}
