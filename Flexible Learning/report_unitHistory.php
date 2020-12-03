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

use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Domain\Students\StudentGateway;
use Gibbon\Module\FlexibleLearning\Tables\UnitHistory;

$highestAction = getHighestGroupedAction($guid, '/modules/Flexible Learning/report_unitHistory.php', $connection2);

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/report_unitHistory.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} elseif (empty($highestAction)) {
    // Check the action with highest precedence
    $page->addError(__('The highest grouped action cannot be determined.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__m('Unit History'));

    $gibbonSchoolYearID = $gibbon->session->get('gibbonSchoolYearID');
    $studentGateway = $container->get(StudentGateway::class);

    if ($highestAction == 'Unit History_all') {
        // Can view all students
        $gibbonPersonID = $_REQUEST['gibbonPersonID'] ?? '';
        $participant = $container->get(UserGateway::class)->getByID($gibbonPersonID);
        
    } elseif ($highestAction == 'Unit History_myChildren') {
        // Can view family children
        $children = $studentGateway
            ->selectAnyStudentsByFamilyAdult($gibbonSchoolYearID, $gibbon->session->get('gibbonPersonID'))
            ->fetchAll();
        $children = Format::nameListArray($children, 'Student', false, true);
        $gibbonPersonID = $_REQUEST['gibbonPersonID'] ?? key($children);

        if (!empty($children[$gibbonPersonID])) {
            $participant = $container->get(UserGateway::class)->getByID($gibbonPersonID);
        }
    }

    // FORM
    $form = Form::create('filter', $gibbon->session->get('absoluteURL').'/index.php', 'get');
    $form->setTitle(__('Filter'));

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/Flexible Learning/report_unitHistory.php');

    if ($highestAction == 'Unit History_all') {
        $row = $form->addRow();
        $row->addLabel('gibbonPersonID', __m('Participant'));
        $row->addSelectUsers('gibbonPersonID', $gibbonSchoolYearID, ['includeStudents' => true])->placeholder()->selected($gibbonPersonID);
    } elseif ($highestAction == 'Unit History_myChildren') {
        $row = $form->addRow();
        $row->addLabel('gibbonPersonID', __m('Participant'));
        $row->addSelectPerson('gibbonPersonID')->fromArray($children)->selected($gibbonPersonID);
    }

    $row = $form->addRow();
        $row->addSearchSubmit($gibbon->session, __('Clear Filters'));

    echo $form->getOutput();
    
    if (empty($gibbonPersonID)) {
        return;
    }

    if (empty($participant)) {
        $page->addError(__('You do not have access to this action.'));
        return;
    }

    $table = $container->get(UnitHistory::class)->create($gibbonPersonID, $highestAction == 'Unit History_all');
    echo $table->getOutput();
}
