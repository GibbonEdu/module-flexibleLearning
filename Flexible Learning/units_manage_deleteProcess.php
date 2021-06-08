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

use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitBlockGateway;

require_once '../../gibbon.php';

$flexibleLearningUnitID = $_POST['flexibleLearningUnitID'] ?? '';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/units_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} elseif (empty($flexibleLearningUnitID)) {
    $URL .= '&return=error1';
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

      // Validate the database relationships exist
      $values = $highestAction == 'Manage Units_all'
        ? $unitGateway->getUnitByID($flexibleLearningUnitID)
        : $unitGateway->getUnitByID($flexibleLearningUnitID, $gibbon->session->get('gibbonPersonID'));

      if (empty($values)) {
          $URL .= '&return=error2';
          header("Location: {$URL}");
          exit;
      }

      $unitBlockGateway->deleteWhere(['flexibleLearningUnitID' => $flexibleLearningUnitID]);
      $deleted = $unitGateway->delete($flexibleLearningUnitID);

      $URL .= !$deleted
          ? '&return=error2'
          : '&return=success0';

      header("Location: {$URL}");
    }
}
