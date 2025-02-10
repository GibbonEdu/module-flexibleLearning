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

use Gibbon\Services\Format;
use Gibbon\Module\FlexibleLearning\Domain\MajorGateway;

require_once '../../gibbon.php';

$flexibleLearningMajorID = $_POST['flexibleLearningMajorID'] ?? '';

$URL = $session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/majors_manage_edit.php&flexibleLearningMajorID='.$flexibleLearningMajorID;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/majors_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {

    // Proceed!
    $majorGateway = $container->get(MajorGateway::class);

    $data = [
        'name'          => $_POST['name'] ?? '',
    ];

    // Validate the required values are present
    if (empty($flexibleLearningMajorID) || empty($data['name']) ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    if (!$majorGateway->exists($flexibleLearningMajorID)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Validate that this record is unique
    if (!$majorGateway->unique($data, ['name'], $flexibleLearningMajorID)) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit;
    }

    // Update the record
    $updated = $majorGateway->update($flexibleLearningMajorID, $data);

    $URL .= !$updated
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
