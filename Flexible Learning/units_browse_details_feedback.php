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
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitSubmissionGateway;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse_details_feedback.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__m('Work Pending Feedback'), 'report_workPendingFeedback.php')
        ->add(__('Add Feedback'));

    // Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        $page->addError(__('The highest grouped action cannot be determined.'));
        return;
    }

    $flexibleLearningUnitSubmissionID = $_REQUEST['flexibleLearningUnitSubmissionID'];
    $flexibleLearningUnitID = $_REQUEST['flexibleLearningUnitID'];

    $settingGateway = $container->get(SettingGateway::class);
    $unitGateway = $container->get(UnitGateway::class);
    $submissionGateway = $container->get(UnitSubmissionGateway::class);

    $values = $unitGateway->getUnitByID($flexibleLearningUnitID);
    $submission = $submissionGateway->getByID($flexibleLearningUnitSubmissionID);
    if (empty($values) || empty($submission)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    // DETAILS TABLE
    $table = DataTable::createDetails('unitDetails');
    $table->addMetaData('gridClass', 'grid-cols-1 md:grid-cols-3 mb-4');

    $table->addHeaderAction('view', __('View'))
        ->setURL('/modules/Flexible Learning/units_browse_details.php')
        ->addParam('flexibleLearningUnitID', $flexibleLearningUnitID)
        ->addParam('sidebar', 'true')
        ->displayLabel();

    if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse_details.php')) {
        $table->addHeaderAction('edit', __('Edit'))
            ->setURL('/modules/Flexible Learning/units_manage_edit.php')
            ->addParam('flexibleLearningUnitID', $flexibleLearningUnitID)
            ->displayLabel()
            ->prepend(' | ');
    }

    $table->addColumn('name', '')->addClass('text-lg font-bold');
    $table->addColumn('time', __m('Time'))
        ->format(function ($values) {
            $minutes = intval($values['length']);
            $relativeTime = __n('{count} min', '{count} mins', $minutes);
            if ($minutes > 60) {
                $hours = round($minutes / 60, 1);
                $relativeTime = Format::tooltip(__n('{count} hr', '{count} '.__m('hrs'), ceil($minutes / 60), ['count' => $hours]), $relativeTime);
            }

            return !empty($values['length'])
                ? $relativeTime
                : Format::small(__('N/A'));
        });

    $table->addColumn('logo', '')
        ->addClass('row-span-3 text-right')
        ->format(function ($values) use ($gibbon) {
            if ($values['logo'] == null) {
                return "<img style='margin: 5px; height: 125px; width: 125px' class='user' src='" . $gibbon->session->get('absoluteURL') . '/themes/' . $gibbon->session->get('gibbonThemeName') . "/img/anonymous_125.jpg'/><br/>";
            } else {
                return "<img style='margin: 5px; height: 125px; width: 125px' class='user' src='" . $values['logo'] . "'/><br/>";
            }
        });

    $table->addColumn('category', __('Category'));

    $table->addColumn('author', __('Author'))
        ->format(function ($person) use ($guid, $connection2, $gibbon) {
            if ($person['status'] == 'Full' && isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php')) {
                return "<a href=" . $gibbon->session->get('absoluteURL') . "/index.php?q=/modules/Staff/staff_view_details.php&gibbonPersonID=" . $person['gibbonPersonIDCreator'] . ">" . Format::name('', $person['preferredName'], $person['surname'], 'Staff', false, true) . "</a>";
            } else {
                return Format::name('', $person['preferredName'], $person['surname'], 'Staff', false, true);
            }
        });

    $table->addColumn('majors', __('Majors'))
        ->sortable(['major1', 'major2'])
        ->format(function ($unit) {
            $majors = [$unit['major1'], $unit['major2']];
            return implode(', ', array_filter($majors));
        });

    $table->addColumn('minors', __('Minors'))
        ->sortable(['minor1', 'minor2'])
        ->format(function ($unit) {
            if ($unit['minor1'] != null or $unit['minor2'] != null) {
                $minors = [$unit['minor1'], $unit['minor2']];
                return implode(', ', array_filter($minors));
            } else {
                return "N/A";
            }
        });

    echo $table->render([$values]);

    // DISCUSSION
    $logs = $submissionGateway->selectUnitSubmissionDiscussion($flexibleLearningUnitSubmissionID)->fetchAll();
    
    echo $page->fetchFromTemplate('ui/discussion.twig.html', [
        'title' => __('Comments'),
        'discussion' => $logs
    ]);

    $expectFeedback = $settingGateway->getSettingByScope('Flexible Learning', 'expectFeedback') == 'Y' || $submission['status'] == 'Pending';
    $feedbackOnMessage = $settingGateway->getSettingByScope('Flexible Learning', 'feedbackOnMessage');
    $feedbackOffMessage = $settingGateway->getSettingByScope('Flexible Learning', 'feedbackOffMessage');

    // FEEDBACK
    $form = Form::create('submit', $gibbon->session->get('absoluteURL').'/modules/Flexible Learning/units_browse_details_feedbackProcess.php');
    $form->addClass('mt-8');
    $form->addHiddenValue('address', $gibbon->session->get('address'));
    $form->addHiddenValue('flexibleLearningUnitID', $flexibleLearningUnitID);
    $form->addHiddenValue('flexibleLearningUnitSubmissionID', $flexibleLearningUnitSubmissionID);

    $form->addRow()->addHeading(__('Share Feedback'));

    $col = $form->addRow()->addColumn();
        $col->addContent(Format::alert(__m($expectFeedback ? $feedbackOnMessage : $feedbackOffMessage), $expectFeedback ? 'message' : 'warning').'</br>');
        $col->addLabel('comment', __('Comment'));
        $col->addEditor('comment', $guid)->setRows(10)->showMedia()->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();   
}
