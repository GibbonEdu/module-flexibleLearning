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
use Gibbon\Services\Format;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/booklet_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__m('Printable Booklet'));

    $unitGateway = $container->get(UnitGateway::class);
    $offlineUnits = $unitGateway->selectBy(['active' => 'Y', 'offline' => 'Y'], ['flexibleLearningUnitID'])->fetchAll();

    $form = Form::create('booklet', $session->get('absoluteURL').'/modules/'.$session->get('module').'/booklet_manageProcess.php');
    $form->addHiddenValue('address', $session->get('address'));

    $form->addRow()->addContent(Format::alert(__m('There are {count} active offline-friendly unit(s) that will be included in the booklet.', ['count' => count($offlineUnits)]), 'message'));
    
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit(__m('Generate PDF'));

    echo $form->getOutput();


    $settingGateway = $container->get(SettingGateway::class);

    $form = Form::create('settings', $session->get('absoluteURL').'/modules/'.$session->get('module').'/booklet_manageSettingsProcess.php');
    $form->addHiddenValue('address', $session->get('address'));
    $form->setTitle(__m('Printable Booklet Settings'));

    // $setting = $settingGateway->getSettingByScope('Flexible Learning', 'expectFeedback', true);
    // $row = $form->addRow();
    //     $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    //     $row->addYesNo($setting['name'])->required()->selected($setting['value']);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
