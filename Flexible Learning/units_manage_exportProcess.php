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

use Gibbon\Services\Format;
use Gibbon\Module\FlexibleLearning\Booklet;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;

require_once '../../gibbon.php';

$flexibleLearningUnitID = $_GET['flexibleLearningUnitID'] ?? '';
$flexibleLearningUnitID = preg_replace('/[^0-9]/', '', $flexibleLearningUnitID);

$URL = $session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/units_browse_details.php&flexibleLearningUnitID='.$flexibleLearningUnitID;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse_details.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {

    $unit = $container->get(UnitGateway::class)->getByID($flexibleLearningUnitID);
    if (empty($flexibleLearningUnitID) || empty($unit)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }
  
    $booklet = $container->get(Booklet::class);
    $booklet->addUnit($unit);

    $path = $booklet->createTempFile();
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $unit['name']).'.pdf';

    $booklet->render($path);
    $booklet->export($path, $filename);
}
