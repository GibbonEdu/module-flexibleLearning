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

use Gibbon\View\View;
use Gibbon\Forms\Form;
use Gibbon\FileUploader;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Tables\View\GridView;
use Gibbon\Domain\User\RoleGateway;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\Students\StudentGateway;
use Gibbon\Domain\System\DiscussionGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitBlockGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitSubmissionGateway;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse_details.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs
        ->add(__m('Browse Units'), 'units_browse.php')
        ->add(__m('Unit Details'));

    $name = $_GET['name'] ?? '';
    $flexibleLearningUnitID = $_GET['flexibleLearningUnitID'] ?? '';
    $flexibleLearningUnitSubmissionID = $_GET['flexibleLearningUnitSubmissionID'] ?? '';
    $roleGateway = $container->get(RoleGateway::class);
    $roleCategory = $roleGateway->getRoleCategory($session->get('gibbonRoleIDCurrent'));
    
    $unitGateway = $container->get(UnitGateway::class);
    $unitBlockGateway = $container->get(UnitBlockGateway::class);
    $submissionGateway = $container->get(UnitSubmissionGateway::class);
    $studentGateway = $container->get(StudentGateway::class);
    $settingGateway = $container->get(SettingGateway::class);

    $highestManageAction = getHighestGroupedAction($guid, '/modules/Flexible Learning/units_manage.php', $connection2);
    $highestUnitHistoryAction = getHighestGroupedAction($guid, '/modules/Flexible Learning/report_unitHistory.php', $connection2);

    $values = $unitGateway->getUnitByID($flexibleLearningUnitID);
    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $access = $values['available'.$roleCategory] ?? 'No';
    if (empty($highestManageAction) && $access != 'Read' && $access != 'Record') {
        $page->addError(__m('You do not have access to browse this unit.'));
        return;
    }

    // DETAILS TABLE
    $table = DataTable::createDetails('unitDetails');
    $table->addMetaData('gridClass', 'grid-cols-1 md:grid-cols-3 mb-4');

    if ($highestManageAction == 'Manage Units_all' || ($highestManageAction == 'Manage Units_my' && $values['gibbonPersonIDCreator'] == $session->get('gibbonPersonID'))) {
        $table->addHeaderAction('edit', __('Edit'))
            ->setURL('/modules/Flexible Learning/units_manage_edit.php')
            ->addParam('flexibleLearningUnitID', $flexibleLearningUnitID)
            ->addParam('name', $name)
            ->displayLabel();
    }

    $table->addHeaderAction('export', __('Download'))
        ->setURL("/modules/Flexible Learning/units_manage_exportProcess.php")
        ->addParam('flexibleLearningUnitID', $flexibleLearningUnitID)
        ->setIcon('delivery2')
        ->displayLabel()
        ->directLink();

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
        ->format(function ($values) use ($session) {
            if ($values['logo'] == null) {
                return "<img style='margin: 5px; height: 125px; width: 125px' class='user' src='" . $session->get('absoluteURL') . '/themes/' . $session->get('gibbonThemeName') . "/img/anonymous_125.jpg'/><br/>";
            } else {
                return "<img style='margin: 5px; height: 125px; width: 125px' class='user' src='" . $values['logo'] . "'/><br/>";
            }
        });

    $table->addColumn('category', __('Category'));

    $table->addColumn('author', __('Author'))
        ->format(function ($person) use ($guid, $connection2, $session) {
            if ($person['status'] == 'Full' && isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php')) {
                return "<a href=" . $session->get('absoluteURL') . "/index.php?q=/modules/Staff/staff_view_details.php&gibbonPersonID=" . $person['gibbonPersonIDCreator'] . ">" . Format::name('', $person['preferredName'], $person['surname'], 'Staff', false, true) . "</a>";
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
    if ($highestUnitHistoryAction == 'Unit History_all' && !empty($flexibleLearningUnitSubmissionID)) {
        // Highest level access, view any discussion
        $submission = $submissionGateway->getByID($flexibleLearningUnitSubmissionID);
    } elseif ($highestUnitHistoryAction == 'Unit History_myChildren' && !empty($flexibleLearningUnitSubmissionID)) {
        // Parents can only view submissions for their children
        $submission = $submissionGateway->getByID($flexibleLearningUnitSubmissionID);
        $children = $studentGateway
            ->selectAnyStudentsByFamilyAdult($session->get('gibbonSchoolYearID'), $session->get('gibbonPersonID'))
            ->fetchGroupedUnique();

        if (empty($children[$submission['gibbonPersonID']])) {
            unset($submission);
        }
    } else {
        // Everyone else can only view their own submissions
        $submission = $submissionGateway->selectBy(['gibbonPersonID' => $session->get('gibbonPersonID'), 'flexibleLearningUnitID' => $flexibleLearningUnitID])->fetch();
    }

    if (!empty($submission)) {
        $logs = $submissionGateway->selectUnitSubmissionDiscussion($submission['flexibleLearningUnitSubmissionID'])->fetchAll();

        echo $page->fetchFromTemplate('ui/discussion.twig.html', [
            'title' => __('Comments'),
            'discussion' => $logs
        ]);

        // Add a comment
        if ($highestUnitHistoryAction == 'Unit History_myChildren') {
            $form = Form::create('parentComment', $session->get('absoluteURL').'/modules/Flexible Learning/units_browse_details_commentProcess.php');
            $form->setClass('blank my-4');
            $form->addHiddenValue('address', $session->get('address'));
            $form->addHiddenValue('flexibleLearningUnitID', $flexibleLearningUnitID);
            $form->addHiddenValue('flexibleLearningUnitSubmissionID', $submission['flexibleLearningUnitSubmissionID']);

            $commentBox = $form->getFactory()->createColumn()->addClass('flex flex-col');
            $commentBox->addTextArea('comment')
                ->placeholder(__m('Leave a comment'))
                ->setClass('flex w-full')
                ->setRows(3);
            $commentBox->addSubmit(__m('Add Comment'))->setColor('gray')
                ->onClick('document.getElementById("parentComment").submit()')
                ->setClass('text-right');

            $form->addRow()->addClass('-mt-4')->addContent($page->fetchFromTemplate('ui/discussion.twig.html', [
                'discussion' => [[
                    'surname' => $session->get('surname'),
                    'preferredName' => $session->get('preferredName'),
                    'image_240' => $session->get('image_240'),
                    'comment' => $commentBox->getOutput(),
                ]]
            ]));

            echo $form->getOutput();
        }
    }

    // SMART BLOCKS
    $blocks = $unitBlockGateway->selectBlocksByUnit($flexibleLearningUnitID)->fetchAll();

    if ($values['outline'] != null) {
      $outlineBlock = [
        'flexibleLearningUnitBlockID' => 0,
        'flexibleLearningUnitID' => $flexibleLearningUnitID,
        'title' => 'Unit Outline',
        'type' => null,
        'length' => null,
        'contents' => $values['outline'],
        'teachersNotes' => null,
        'sequenceNumber' => null];
      array_unshift($blocks, $outlineBlock);
    };
    if (empty($blocks)) {
        echo Format::alert(__('There are no records to display.'));
    } else {
        $templateView = $container->get(View::class);
        $resourceContents = '';

        $blockCount = 0;
        foreach ($blocks as $block) {
            echo $templateView->fetchFromTemplate('unitBlock.twig.html', $block + [
                'roleCategory' => $roleCategory,
                'gibbonPersonID' => $session->get('username') ?? '',
                'blockCount' => $blockCount
            ]);
            $resourceContents .= $block['contents'];
            $blockCount++;
        }
    }

    // Cancel out here if we only have read access
    if ($access != 'Record') return;

    $expectFeedback = $settingGateway->getSettingByScope('Flexible Learning', 'expectFeedback') == 'Y';
    $feedbackOnMessage = $settingGateway->getSettingByScope('Flexible Learning', 'feedbackOnMessage');
    $feedbackOffMessage = $settingGateway->getSettingByScope('Flexible Learning', 'feedbackOffMessage');

    $submissionLog = $logs[0] ?? [];
    if (!empty($submission)) {
        // UPDATE EVIDENCE
        $form = Form::create('submit', $session->get('absoluteURL').'/modules/Flexible Learning/units_browse_details_updateProcess.php');
        $form->addHiddenValue('address', $session->get('address'));
        $form->addHiddenValue('flexibleLearningUnitID', $flexibleLearningUnitID);
        $form->addHiddenValue('flexibleLearningUnitSubmissionID', $submission['flexibleLearningUnitSubmissionID'] ?? '');
        $form->addHiddenValue('gibbonDiscussionID', $submissionLog['gibbonDiscussionID'] ?? '');

        $form->addRow()->addHeading(__m('Record your Journey'));
        $form->addRow()->addContent(Format::alert(__m('You have already recorded evidence for this unit. You can optionally use the form below to update your submission.'), 'success'));
    } else {
        // SUBMIT EVIDENCE
        $form = Form::create('submit', $session->get('absoluteURL').'/modules/Flexible Learning/units_browse_details_submitProcess.php');
        $form->addHiddenValue('address', $session->get('address'));
        $form->addHiddenValue('flexibleLearningUnitID', $flexibleLearningUnitID);

        $form->addRow()->addHeading(__m('Record your Journey'));
        $form->addRow()->addContent(Format::alert(__m($expectFeedback ? $feedbackOnMessage : $feedbackOffMessage), $expectFeedback ? 'message' : 'warning'));
    }

    $row = $form->addRow();
        $row->addLabel('comment', __('Comment'))->description(__m('Leave a brief reflective comment on this unit<br/>and what you learned.'));
        $row->addTextArea('comment')->setRows(4)->required()->setValue($submissionLog['comment'] ?? '');

    $types = ['Link' => __('Link'), 'File' => __('File')];
    $row = $form->addRow();
        $row->addLabel('evidenceType', __('Type'));
        $row->addRadio('evidenceType')->fromArray($types)->inline()->required()->checked($submission['evidenceType'] ?? 'Link');

    // File
    $form->toggleVisibilityByClass('evidenceFile')->onRadio('evidenceType')->when('File');
    $row = $form->addRow()->addClass('evidenceFile');
        $row->addLabel('file', __('File'));
        $uploader = $row->addFileUpload('file')
            ->accepts($container->get(FileUploader::class)->getFileExtensions())
            ->required();

    if (!empty($submission) && $submission['evidenceType'] == 'File') {
        $uploader->setAttachment('evidenceLocation', $session->get('absoluteURL'), $submission['evidenceLocation'] ?? '');
    }

    // Link
    $form->toggleVisibilityByClass('evidenceLink')->onRadio('evidenceType')->when('Link');
    $row = $form->addRow()->addClass('evidenceLink');
        $row->addLabel('link', __('Link'));
        $link = $row->addURL('link')->maxLength(255)->required();

    if (!empty($submission) && $submission['evidenceType'] == 'Link') {
        $link->setValue($submission['evidenceLocation'] ?? '');
    }

    if ($roleCategory == 'Student' && empty($submission)) {
        $row = $form->addRow();
        $row->addLabel('inviteParents', __('Email Invitation'))->description(__m('Checking this box will send your parents an email to let them know about your work and invite them to comment on it.'));
        $row->addCheckbox('inviteParents')->setValue('Y')->description(__m('Invite my parents to comment'));
    }

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
