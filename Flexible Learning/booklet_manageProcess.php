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

use Gibbon\Domain\System\SettingGateway;
use Gibbon\Module\FlexibleLearning\Booklet;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;

include '../../gibbon.php';

$URL = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address']).'/booklet_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/booklet_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // Proceed!
    $partialFail = false;
    
    $flexibleLearningUnitIDList = $_POST['flexibleLearningUnitID'] ?? [];
    $settingGateway = $container->get(SettingGateway::class);
    $chapterPages = $settingGateway->getSettingByScope('Flexible Learning', 'bookletChapters');

    if (empty($flexibleLearningUnitIDList)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    }

    $booklet = $container->get(Booklet::class);
    $booklet->addData('bookletName', $settingGateway->getSettingByScope('Flexible Learning', 'bookletName'));
    $booklet->addData('bookletIntroduction', $settingGateway->getSettingByScope('Flexible Learning', 'bookletIntroduction'));
    $booklet->addData('insideMargins', $settingGateway->getSettingByScope('Flexible Learning', 'bookletMargins'));
    $booklet->addData('chapterPages', $chapterPages);

    $offlineUnits = $container->get(UnitGateway::class)->selectUnitsByID($flexibleLearningUnitIDList, $chapterPages == 'Y')->fetchGrouped();

    foreach ($offlineUnits as $grpIndex => $units) {
        foreach ($units as $unitIndex => $unit) {
            $booklet->addUnit($unit, $grpIndex);
        }
    }

    $path = $booklet->createTempFile();

    $booklet->render($path);
    $booklet->export($path, 'FlexibleLearningBooklet.pdf');
}
