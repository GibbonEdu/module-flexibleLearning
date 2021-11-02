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
use Gibbon\Forms\Prefab\BulkActionForm;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Get action with highest precedence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        $page->addError(__('The highest grouped action cannot be determined.'));
        return;
    }

    $page->breadcrumbs->add(__m('Manage Units'));

    $name = $_GET['name'] ?? '';

    // QUERY
    $unitGateway = $container->get(UnitGateway::class);
    $criteria = $unitGateway->newQueryCriteria(true)
        ->searchBy($unitGateway->getSearchableColumns(), $name)
        ->sortBy('name')
        ->fromPOST();

    // FORM
    $form = Form::create('filter', $session->get('absoluteURL').'/index.php', 'get');
    $form->setTitle(__('Filter'));

    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/Flexible Learning/units_manage.php');

    $row = $form->addRow();
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->setValue($criteria->getSearchText());

    $row = $form->addRow();
        $row->addSearchSubmit($session, __('Clear Filters'));

    echo $form->getOutput();

    $units = $highestAction == 'Manage Units_all'
        ? $unitGateway->queryAllUnits($criteria, null, null, null, true)
        : $unitGateway->queryAllUnits($criteria, null, $session->get('gibbonPersonID'), null, true);

    // BULK ACTION FORM
    $form = BulkActionForm::create('bulkAction', $session->get('absoluteURL').'/modules/Flexible Learning/units_manageProcessBulk.php');

    $bulkActions = ['Export' => __('Export')];
    $col = $form->createBulkActionColumn($bulkActions);
        $col->addSubmit(__('Go'));

    // DATA TABLE
    $table = $form->addRow()->addDataTable('units', $criteria)->withData($units);
    $table->setTitle(__('View'));

    $table->addHeaderAction('add', __('Add'))
        ->addParam('name', $name)
        ->setURL('/modules/Flexible Learning/units_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function ($unit, $row) {
        if ($unit['active'] != 'Y') $row->addClass('error');
        return $row;
    });

    $table->addMetaData('bulkActions', $col);

    $table->addMetaData('filterOptions', [
        'active:Y'        => __('Active').': '.__('Yes'),
        'active:N'        => __('Active').': '.__('No')
    ]);

    $table->addColumn('name', __('Name'));

    $table->addColumn('majors', __('Major'))
        ->sortable(['major1', 'major2'])
        ->format(function ($unit) {
            $majors = [$unit['major1'], $unit['major2']];
            return implode(', ', array_filter($majors));
            });
    $table->addColumn('minors', __('Minor'))
        ->sortable(['minor1', 'minor2'])
            ->format(function ($unit) {
            $minors = [$unit['minor1'],$unit['minor2']];
            return implode(', ', array_filter($minors));
        });
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));

    // ACTIONS
    $canBrowseUnits = isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse.php');
    $table->addActionColumn()
        ->addParam('name', $name)
        ->addParam('flexibleLearningUnitID')
        ->format(function ($unit, $actions) use ($canBrowseUnits) {
            if ($canBrowseUnits) {
                $actions->addAction('view', __('View'))
                    ->addParam('sidebar', 'true')
                    ->addParam('showInactive', 'Y')
                    ->setURL('/modules/Flexible Learning/units_browse_details.php');
            }

            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Flexible Learning/units_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Flexible Learning/units_manage_delete.php');
        });

    echo $form->getOutput();

}
