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

use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Module\FlexibleLearning\Domain\CategoryGateway;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/categories_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs
        ->add(__m('Manage Categories'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    // Query categories
    $categoryGateway = $container->get(categoryGateway::class);

    $criteria = $categoryGateway->newQueryCriteria()
        ->sortBy(['sequenceNumber', 'name'])
        ->fromPOST();

    $categories = $categoryGateway->queryCategories($criteria);

    // Render table
    $table = DataTable::createPaginated('categories', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Flexible Learning/categories_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function ($category, $row) {
        if ($category['active'] == 'N') $row->addClass('error');
        return $row;
    });

    $table->addDraggableColumn('flexibleLearningCategoryID', $gibbon->session->get('absoluteURL').'/modules/Flexible Learning/categories_manage_editOrderAjax.php');

    $table->addColumn('name', __('Name'))
        ->sortable(['flexibleLearningCategory.name']);
    $table->addColumn('description', __('Description'))
        ->sortable(['flexibleLearningCategory.description']);

    // ACTIONS
    $table->addActionColumn()
        ->addParam('flexibleLearningCategoryID')
        ->format(function ($category, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Flexible Learning/categories_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Flexible Learning/categories_manage_delete.php');
        });

    echo $table->render($categories);
}
