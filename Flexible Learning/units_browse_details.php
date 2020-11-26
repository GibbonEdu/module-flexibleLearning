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

use Gibbon\View\View;
use Gibbon\Forms\Form;
use Gibbon\FileUploader;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Tables\View\GridView;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitBlockGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitSubmissionGateway;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse_details.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {

    $name = $_GET['name'] ?? '';
    $flexibleLearningUnitID = $_GET['flexibleLearningUnitID'] ?? '';

    $roleCategory = getRoleCategory($gibbon->session->get('gibbonRoleIDCurrent'), $connection2);

    $values = $container->get(UnitGateway::class)->getUnitByID($flexibleLearningUnitID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    // Breadcrumbs
    $page->breadcrumbs
        ->add(__m('Browse Units'), 'units_browse.php')
        ->add(__m('Unit Details'));

    // Edit links
    if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse_details.php') == true) {
        echo "<div class='linkTop'>";
        echo "<a href='" . $gibbon->session->get('absoluteURL') . "/index.php?q=/modules/Flexible Learning/units_manage_edit.php&flexibleLearningUnitID=$flexibleLearningUnitID&name=$name'>" . __('Edit') . "<img style='margin: 0 0 -4px 3px' title='" . __('Edit') . "' src='./themes/" . $gibbon->session->get('gibbonThemeName') . "/img/config.png'/></a>";
        echo '</div>';
    }

    // DATA TABLE
    $table = DataTable::createDetails('unitDetails');
    $table->addMetaData('gridClass', 'grid-cols-1 md:grid-cols-3 mb-6');
    $table->addColumn('name', '')->addClass('text-lg font-bold');
    $table->addColumn('time', __m('Time'))
        ->format(function ($values) use ($connection2, $flexibleLearningUnitID) {
            $output = '';
            $timing = null;
            $blocks = getBlocksArray($connection2, $flexibleLearningUnitID);
            if ($blocks != false) {
                foreach ($blocks as $block) {
                    if ($block[0] == $values['flexibleLearningUnitID']) {
                        if (is_numeric($block[2])) {
                            $timing += $block[2];
                        }
                    }
                }
            }
            if (is_null($timing)) {
                $output = __('N/A');
            } else {
                $minutes = intval($timing);
                $relativeTime = __n('{count} min', '{count} mins', $minutes);
                if ($minutes > 60) {
                    $hours = round($minutes / 60, 1);
                    $relativeTime = Format::tooltip(__n('{count} hr', '{count} ' . __m('hrs'), ceil($minutes / 60), ['count' => $hours]), $relativeTime);
                }

                $output = !empty($timing) ? $relativeTime : Format::small(__('N/A'));
            }

            return $output;
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

    // SMART BLOCKS
    $unitBlockGateway = $container->get(UnitBlockGateway::class);
    $blocks = $unitBlockGateway->selectBlocksByUnit($flexibleLearningUnitID)->fetchAll();

    if (empty($blocks)) {
        echo Format::alert(__('There are no records to display.'));
    } else {
        $templateView = $container->get(View::class);
        $resourceContents = '';

        $blockCount = 0;
        foreach ($blocks as $block) {
            echo $templateView->fetchFromTemplate('unitBlock.twig.html', $block + [
                'roleCategory' => $roleCategory,
                'gibbonPersonID' => $gibbon->session->get('username') ?? '',
                'blockCount' => $blockCount
            ]);
            $resourceContents .= $block['contents'];
            $blockCount++;
        }
    }

    $submissionGateway = $container->get(UnitSubmissionGateway::class);
    $settingGateway = $container->get(SettingGateway::class);

    $submission = $submissionGateway->selectBy(['gibbonPersonID' => $gibbon->session->get('gibbonPersonID'), 'flexibleLearningUnitID' => $flexibleLearningUnitID])->fetch();

    $expectFeedback = $settingGateway->getSettingByScope('Flexible Learning', 'expectFeedback');
    $feedbackOnMessage = $settingGateway->getSettingByScope('Flexible Learning', 'feedbackOnMessage');
    $feedbackOffMessage = $settingGateway->getSettingByScope('Flexible Learning', 'feedbackOffMessage');

    if ($expectFeedback == 'Y') {
        echo Format::alert(__m($feedbackOnMessage), 'message');
    } else {
        echo Format::alert(__m($feedbackOffMessage), 'warning');
    }

    if (!empty($submission)) {
        // ALREADY SUBMITTED
        echo Format::alert(__m('Nice work! You have already submitted evidence for this unit.'), 'success');
    } else {
        // SUBMIT EVIDENCE
        $form = Form::create('submit', $gibbon->session->get('absoluteURL').'/modules/Flexible Learning/units_browse_details_submitProcess.php');

        $form->addHiddenValue('address', $gibbon->session->get('address'));
        $form->addHiddenValue('flexibleLearningUnitID', $flexibleLearningUnitID);

        $form->addRow()->addHeading(__('Submit your Evidence'));

        $row = $form->addRow();
            $row->addLabel('comment', __('Comment'))->description(__m('Leave a brief reflective comment on this unit<br/>and what you learned.'));
            $row->addTextArea('comment')->setRows(4)->required();

        $types = ['Link' => __('Link'), 'File' => __('File')];
        $row = $form->addRow();
            $row->addLabel('evidenceType', __('Type'));
            $row->addRadio('evidenceType')->fromArray($types)->inline()->required()->checked('Link');

        // File
        $form->toggleVisibilityByClass('evidenceFile')->onRadio('type')->when('File');
        $row = $form->addRow()->addClass('evidenceFile');
            $row->addLabel('file', __('Submit File'));
            $row->addFileUpload('file')->accepts($container->get(FileUploader::class)->getFileExtensions())->required();

        // Link
        $form->toggleVisibilityByClass('evidenceLink')->onRadio('type')->when('Link');
        $row = $form->addRow()->addClass('evidenceLink');
            $row->addLabel('link', __('Submit Link'));
            $row->addURL('link')->maxLength(255)->required();

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();    
    }
}
