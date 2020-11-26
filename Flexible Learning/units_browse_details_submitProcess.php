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
use Gibbon\Module\FlexibleLearning\Domain\UnitSubmissionGateway;

$flexibleLearningUnitID = $_POST['flexibleLearningUnitID'] ?? '';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/units_browse_details.php&flexibleLearningUnitID='.$flexibleLearningUnitID;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse_details.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $unitGateway = $container->get(UnitGateway::class);
    $unitSubmissionGateway = $container->get(UnitSubmissionGateway::class);

    $data = [
        'flexibleLearningUnitID' => $flexibleLearningUnitID,
        'gibbonPersonID'         => $gibbon->session->get('gibbonPersonID'),
        'gibbonSchoolYearID'     => $gibbon->session->get('gibbonSchoolYearID'),
        'evidenceType'           => $_POST['evidenceType'] ?? '',
        'status'                 => 'Complete',
    ];

    // Validate the required values are present
    if (empty($data['flexibleLearningUnitID']) || empty($data['gibbonPersonID']) || empty($data['gibbonSchoolYearID']) || empty($data['evidenceType'])) {
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

    // Validate the values are unique
    if (!$unitSubmissionGateway->unique($data, ['gibbonPersonID', 'flexibleLearningUnitID'])) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit;
    }

    // Create the record
    $inserted = $unitSubmissionGateway->insert($data);

    $URL .= !$inserted
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
