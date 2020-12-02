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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Module\FlexibleLearning\Tables\UnitHistory;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/hook_studentProfile_unitHistory.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    include './modules/Flexible Learning/src/Tables/UnitHistory.php';
    include './modules/Flexible Learning/src/Domain/UnitSubmissionGateway.php';

    $table = $container->get(UnitHistory::class)->create($gibbonPersonID);
    echo $table->getOutput();
}
