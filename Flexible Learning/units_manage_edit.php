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
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Forms\FlexibleLearningFormFactory;

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
        return;
    }

    $flexibleLearningUnitID = $_GET['flexibleLearningUnitID'];
    $name = $_GET['name'] ?? '';

    //Proceed!
    $urlParams = compact('name');
    $unitGateway = $container->get(UnitGateway::class);

    $page->breadcrumbs
            ->add(__m('Manage Units'), 'units_manage.php', $urlParams)
            ->add(__m('Edit Unit'));

    if (empty($flexibleLearningUnitID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $highestAction == 'Manage Units_all'
        ? $unitGateway->getUnitByID($flexibleLearningUnitID)
        : $unitGateway->getUnitByID($flexibleLearningUnitID, $session->get('gibbonPersonID'));

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

   
    $form = Form::create('action', $session->get('absoluteURL').'/modules/'.$session->get('module')."/units_manage_editProcess.php?&name=$name");
    $form->setFactory(FlexibleLearningFormFactory::create($pdo));

    $form->addHiddenValue('address', $session->get('address'));
    $form->addHiddenValue('flexibleLearningUnitID',$flexibleLearningUnitID);

    if (!empty($name)) {
        $form->addHeaderAction('back', __('Back to Search Results'))
            ->setURL('/modules/Flexible Learning/units_manage.php')
            ->addParam('name', $name)
            ->setIcon('search')
            ->displayLabel()
            ->append(' | ');
    }

    if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse_details.php')) {
        $form->addHeaderAction('view', __('View'))
            ->setURL("/modules/Flexible Learning/units_browse_details.php")
            ->addParam('flexibleLearningUnitID', $flexibleLearningUnitID)
            ->addParam('name', $name)
            ->addParam('sidebar', 'true')
            ->displayLabel()
            ->append(' | ');
    }

    $form->addHeaderAction('export', __('Download'))
        ->setURL("/modules/Flexible Learning/units_manage_exportProcess.php")
        ->addParam('flexibleLearningUnitID', $flexibleLearningUnitID)
        ->setIcon('delivery2')
        ->displayLabel()
        ->directLink();
    
    $settingGateway = $container->get(SettingGateway::class);

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

    $highestManageAction = getHighestGroupedAction($guid, '/modules/Flexible Learning/units_manage.php', $connection2);

    $row = $form->addRow();
        $row->addLabel('gibbonPersonIDCreator', __('Author'));
        $row->addSelectUsers('gibbonPersonIDCreator')
            ->photo(true, 'small')
            ->required()
            ->readonly($highestManageAction == 'Manage Units_my');

    $licenses = array(
        "Copyright" => __("Copyright"),
        "Creative Commons BY" => __("Creative Commons BY"),
        "Creative Commons BY-SA" => __("Creative Commons BY-SA"),
        "Creative Commons BY-SA-NC" => __("Creative Commons BY-SA-NC"),
        "Public Domain" => __("Public Domain")
    );

    $row = $form->addRow();
        $row->addLabel('license', __('License'))->description(__('Under what conditions can this work be reused?'));
        $row->addSelect('license')->fromArray($licenses)->placeholder();

    $row = $form->addRow();
        $row->addLabel('file', __m('Logo'))->description(__m('125px x 125px'));
        $row->addFileUpload('file')
            ->accepts('.jpg,.jpeg,.gif,.png')
            ->setAttachment('logo', $session->get('absoluteURL'), $values['logo']);

    //MAJORS AND MINORS
    $form->addRow()->addHeading(__m('Majors & Minors'))->append(__m('These help indicate what topics the unit is about.'));

    $sql = "(SELECT minor1, minor2, NULL AS major1, NULL AS major2 FROM flexibleLearningUnit WHERE active='Y') UNION (SELECT name AS major1, name AS major2, NULL as minor1, NULL as minor2 FROM flexibleLearningMajor)";
    $result = $pdo->executeQuery(array(), $sql);
    $options = array();
    while ($option=$result->fetch()){
        $options[]=$option['major1'];
        $options[]=$option['major2'];
        $options[]=$option['minor1'];
        $options[]=$option['minor2'];
    }
    $options = array_unique($options);

    $sql = "SELECT flexibleLearningMajorID AS value, name FROM flexibleLearningMajor ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('flexibleLearningMajorID1', __('Major 1'));
        $row->addSelect('flexibleLearningMajorID1')->fromQuery($pdo, $sql, [])->required()->placeholder();
    $row = $form->addRow();
        $row->addLabel('flexibleLearningMajorID2',__('Major 2'));
        $row->addSelect('flexibleLearningMajorID2')->fromQuery($pdo, $sql, [])->placeholder();
    $row = $form->addRow();
        $row->addLabel('minor1', __('Minor 1'));
        $row->addTextField('minor1')->autocomplete($options);
    $row = $form->addRow();
        $row->addLabel('minor2', __('Minor 2'));
        $row->addTextField('minor2')->autocomplete($options);

    $row = $form->addRow();
        $row->addLabel('offline', __('Offline Friendly'))->description(__('Is this an activity or project that can be completed offline? It should not include links and should have minimal material requirements.'));
        $row->addYesNo('offline');

    // ACCESS
    $form->addRow()->addHeading(__m('Access'))->append(__m('Users with permission to manage units can override avaiability preferences.'));

    $access = [
        'No' => __m('No'),
        'Read' => __m('Read Only'),
        'Record' => __m('Read & Record'),
    ];
    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
        $row->addYesNo('active')->required();

    $row = $form->addRow();
        $row->addLabel('availableStudent', __m('Available To Students'))->description(__m('Should students be able to browse and record evidence?'));
        $row->addSelect('availableStudent')->fromArray($access)->required();

    $row = $form->addRow();
        $row->addLabel('availableStaff', __m('Available To Staff'))->description(__m('Should staff be able to browse and record evidence?'));
        $row->addSelect('availableStaff')->fromArray($access)->required();

    $row = $form->addRow();
        $row->addLabel('availableParent', __m('Available To Parents'))->description(__m('Should parents be able to browse and record evidence?'));
        $row->addSelect('availableParent')->fromArray($access)->required();

    $row = $form->addRow();
        $row->addLabel('availableOther', __m('Available To Others'))->description(__m('Should other users be able to browse and record evidence?'));
        $row->addSelect('availableOther')->fromArray($access)->required();


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
        $customBlocks = $row->addFlexibleLearningSmartBlocks('smart', $session, $guid, $settingGateway)
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
