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
use Gibbon\Module\FlexibleLearning\Forms\FlexibleLearningFormFactory;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_manage_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        $page->addError(__('The highest grouped action cannot be determined.'));
    } else {
        $flexibleLearningUnitID = $_GET['flexibleLearningUnitID'];
        $name = $_GET['name'] ?? '';

        //Proceed!
        $urlParams = compact('name');

        $page->breadcrumbs
             ->add(__m('Manage Units'), 'units_manage.php', $urlParams)
             ->add(__m('Edit Unit'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        if (empty($flexibleLearningUnitID)) {
            $page->addError(__('You have not specified one or more required parameters.'));
            return;
        }

        $values = $container->get(UnitGateway::class)->getByID($flexibleLearningUnitID);

        if (empty($values)) {
            $page->addError(__('The specified record cannot be found.'));
            return;
        }

        if ( $name != '') {
            echo "<div class='linkTop'>";
            echo "<a href='".$gibbon->session->get('absoluteURL')."/index.php?q=/modules/Flexible Learning/units_manage.php&name=$name'>".__($guid, 'Back to Search Results').'</a>';
            echo '</div>';
        }

        $form = Form::create('action', $gibbon->session->get('absoluteURL').'/modules/'.$gibbon->session->get('module')."/units_manage_editProcess.php?&name=$name");
        $form->setFactory(FlexibleLearningFormFactory::create($pdo));

        $form->addHiddenValue('address', $gibbon->session->get('address'));
        $form->addHiddenValue('flexibleLearningUnitID',$flexibleLearningUnitID);

        // UNIT BASICS
        $form->addRow()->addHeading(__m('Unit Basics'));

        $row = $form->addRow();
            $row->addLabel('name', __('Name'));
            $row->addTextField('name')->maxLength(40)->required();

        $sql = "SELECT flexibleLearningCategoryID AS value, name FROM flexibleLearningCategory WHERE active='Y' ORDER BY name";
        $row = $form->addRow();
            $row->addLabel('flexibleLearningCategoryID', __('Category'));
            $row->addSelect('flexibleLearningCategoryID')->fromQuery($pdo, $sql, [])->required()->placeholder();

        $row = $form->addRow();
            $row->addLabel('blurb', __('Blurb'));
            $row->addTextArea('blurb')->required();

        $licences = array(
            "Copyright" => __("Copyright"),
            "Creative Commons BY" => __("Creative Commons BY"),
            "Creative Commons BY-SA" => __("Creative Commons BY-SA"),
            "Creative Commons BY-SA-NC" => __("Creative Commons BY-SA-NC"),
            "Public Domain" => __("Public Domain")
        );
        $row = $form->addRow()->addClass('advanced');
            $row->addLabel('license', __('License'))->description(__('Under what conditions can this work be reused?'));
            $row->addSelect('license')->fromArray($licences)->placeholder();

            $row = $form->addRow();
                $row->addLabel('file', __m('Logo'))->description(__m('125px x 125px'));
                $row->addFileUpload('file')
                    ->accepts('.jpg,.jpeg,.gif,.png')
                    ->setAttachment('logo', $gibbon->session->get('absoluteURL'), $values['logo']);

        $row = $form->addRow();
            $row->addLabel('active', __('Active'));
            $row->addYesNo('active')->required();

        //MAJORS AND MINORS
        $sql = "SELECT major1, major2, minor1, minor2 FROM flexibleLearningUnit WHERE active='Y'";
        $result = $pdo->executeQuery(array(), $sql);
        $options = array();
        while ($option=$result->fetch()){
          $options[]=$option['major1'];
          $options[]=$option['major2'];
          $options[]=$option['minor1'];
          $options[]=$option['minor2'];
        }
        $form->addRow()->addHeading(__m('Majors and Minors'));
        $row = $form->addRow();
            $row->addLabel('major1', __('Major 1'));
            $row->addTextField('major1')->autocomplete($options)->required();
        $row = $form->addRow();
            $row->addLabel('major2', __('Major 2'));
            $row->addTextField('major2')->autocomplete($options);
        $row = $form->addRow();
            $row->addLabel('minor1', __('Minor 1'));
            $row->addTextField('minor1')->autocomplete($options);
        $row = $form->addRow();
            $row->addLabel('minor2', __('Minor 2'));
            $row->addTextField('minor2')->autocomplete($options);


        // UNIT OUTLINE
        $form->addRow()->addHeading(__m('Unit Outline'))->append(__m('The contents of this field are viewable to all users, SO AVOID CONFIDENTIAL OR SENSITIVE DATA!'));

        $row = $form->addRow();
            $column = $row->addColumn();
            $column->addLabel('outline', __('Unit Outline'));
            $column->addEditor('outline', $guid)->setRows(30)->showMedia();

            // SMART BLOCKS
            $form->addRow()->addHeading(__('Smart Blocks'))->append(__('Smart Blocks aid unit planning by giving teachers help in creating and maintaining new units, splitting material into smaller units which can be deployed to lesson plans. As well as predefined fields to fill, Smart Units provide a visual view of the content blocks that make up a unit. Blocks may be any kind of content, such as discussion, assessments, group work, outcome etc.'));
            $blockCreator = $form->getFactory()
                ->createButton('addNew')
                ->setValue(__('Click to create a new block'))
                ->addClass('addBlock');

            $row = $form->addRow();
                $customBlocks = $row->addFlexibleLearningSmartBlocks('smart', $gibbon->session, $guid)
                    ->addToolInput($blockCreator);

            $dataBlocks = array('flexibleLearningUnitID' => $flexibleLearningUnitID);
            $sqlBlocks = 'SELECT * FROM flexibleLearningUnitBlock WHERE flexibleLearningUnitID=:flexibleLearningUnitID ORDER BY sequenceNumber';
            $resultBlocks = $pdo->select($sqlBlocks, $dataBlocks);

            while ($rowBlocks = $resultBlocks->fetch()) {
                $smart = array(
                    'title' => $rowBlocks['title'],
                    'type' => $rowBlocks['type'],
                    'length' => $rowBlocks['length'],
                    'contents' => $rowBlocks['contents'],
                    'teachersNotes' => $rowBlocks['teachersNotes'],
                    'flexibleLearningUnitBlockID' => $rowBlocks['flexibleLearningUnitBlockID']
                );
                $customBlocks->addBlock($rowBlocks['flexibleLearningUnitBlockID'], $smart);
            }

            $form->loadAllValuesFrom($values);


            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
    }
}
?>
