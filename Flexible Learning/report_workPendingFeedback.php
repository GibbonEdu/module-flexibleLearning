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
use Gibbon\Tables\DataTable;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitSubmissionGateway;

$highestAction = getHighestGroupedAction($guid, '/modules/Flexible Learning/report_workPendingFeedback.php', $connection2);

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/report_workPendingFeedback.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
}
else {
    // Proceed!
    $page->breadcrumbs->add(__m('Work Pending Feedback'));
    $flexibleLearningUnitID = $_GET['flexibleLearningUnitID'] ?? '';
    $gibbonPersonID = $_GET['gibbonPersonID'] ?? '';
    $myUnits = $_GET['myUnits'] ?? '';

    $units = $container->get(UnitGateway::class)->selectAllUnits();

    // FORM
    $form = Form::create('search', $gibbon->session->get('absoluteURL').'/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setTitle(__('Filter'));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/Flexible Learning/report_workPendingFeedback.php');

    $row = $form->addRow();
        $row->addLabel('flexibleLearningUnitID', __m('Unit'));
        $row->addSelect('flexibleLearningUnitID')->fromResults($units)->placeholder()->selected($flexibleLearningUnitID);

    $row = $form->addRow();
        $row->addLabel('gibbonPersonID', __m('Participant'));
        $row->addSelectUsers('gibbonPersonID')->placeholder()->selected($gibbonPersonID)->photo(true, 'small');

    $row = $form->addRow();
        $row->addLabel('myUnits', __m('My Units'))->description(__m('Only show pending feedback for units you authored.'));
        $row->addCheckbox('myUnits')->setValue('Y')->checked($myUnits);

    $row = $form->addRow();
        $row->addSearchSubmit($gibbon->session, __('Clear Search'));

    echo $form->getOutput();

    // CRITERIA
    $unitGateway = $container->get(UnitGateway::class);
    $unitSubmissionGateway = $container->get(UnitSubmissionGateway::class);

    $criteria = $unitSubmissionGateway->newQueryCriteria()
        ->sortBy('timestampSubmitted')
        ->filterBy('flexibleLearningUnitID', $flexibleLearningUnitID)
        ->filterBy('gibbonPersonID', $gibbonPersonID)
        ->filterBy('myUnits', $myUnits == 'Y' ? $gibbon->session->get('gibbonPersonID') : '')
        ->fromPOST();

    $submissions = $unitSubmissionGateway->queryPendingFeedback($criteria, $gibbon->session->get('gibbonSchoolYearID'));
    
    // DATA TABLE
    $table = DataTable::createPaginated('pending', $criteria);
    $table->setTitle(__('View'));

    $table->modifyRows(function ($values, $row) {
        $row->addClass('pending');
        return $row;
    });

    $table->addColumn('unit', __m('Unit'))
        ->format(function($values) {
            $url = './index.php?q=/modules/Flexible Learning/units_browse_details.php&flexibleLearningUnitID=' . $values['flexibleLearningUnitID'] . '&sidebar=true';
             return Format::link($url, $values['unit']);
        });

    $table->addColumn('participant', __m('Participant'))
        ->sortable('gibbonPersonID')
        ->format(function($values) {
            if ($values['category'] == 'Student') {
                $url = './index.php?q=/modules/Students/student_view_details.php&gibbonPersonID='.$values['gibbonPersonID'];
                return Format::link($url, Format::name('', $values['preferredName'], $values['surname'], 'Student', true));
            } else {
                return Format::name('', $values['preferredName'], $values['surname'], 'Student', true);
            }
        });

    $table->addColumn('status', __('Status'));

    $table->addColumn('timestampSubmitted', __('When'))->format(Format::using('relativeTime', 'timestampSubmitted'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('flexibleLearningUnitSubmissionID')
        ->addParam('flexibleLearningUnitID')
        ->addParam('sidebar', true)
        ->format(function ($values, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Flexible Learning/units_browse_details_feedback.php');
        });

    echo $table->render($submissions);
}
