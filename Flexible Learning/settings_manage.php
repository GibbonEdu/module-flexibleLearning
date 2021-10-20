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

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/settings_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $settingGateway = $container->get(SettingGateway::class);

    $form = Form::create('settings', $session->get('absoluteURL').'/modules/'.$session->get('module').'/settings_manageProcess.php');
    $form->addHiddenValue('address', $session->get('address'));

    $setting = $settingGateway->getSettingByScope('Flexible Learning', 'expectFeedback', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->required()->selected($setting['value']);

    $form->toggleVisibilityByClass('feedbackOn')->onSelect('expectFeedback')->when('Y');
    $form->toggleVisibilityByClass('feedbackOff')->onSelect('expectFeedback')->when('N');

    $setting = $settingGateway->getSettingByScope('Flexible Learning', 'feedbackOnMessage', true);
    $row = $form->addRow()->addClass('feedbackOn');
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->required()->setValue($setting['value']);

    $setting = $settingGateway->getSettingByScope('Flexible Learning', 'feedbackOffMessage', true);
    $row = $form->addRow()->addClass('feedbackOff');
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->required()->setValue($setting['value']);

    $setting = $settingGateway->getSettingByScope('Flexible Learning', 'unitOutlineTemplate', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __m($setting['nameDisplay']))->description(__m($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value'])->setRows(8);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
