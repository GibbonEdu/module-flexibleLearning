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

use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Module\FlexibleLearning\Domain\MajorGateway;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/majors_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs
        ->add(__m('Manage Majors'));

    // Query majors
    $majorGateway = $container->get(MajorGateway::class);

    $criteria = $majorGateway->newQueryCriteria()
        ->sortBy(['name'])
        ->fromPOST();

    $majors = $majorGateway->queryMajors($criteria);

    // Render table
    $table = DataTable::createPaginated('majors', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Flexible Learning/majors_manage_add.php')
        ->displayLabel();

    $table->addColumn('name', __('Name'))
        ->sortable(['flexibleLearningMajor.name']);

    // ACTIONS
    $table->addActionColumn()
        ->addParam('flexibleLearningMajorID')
        ->format(function ($major, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Flexible Learning/majors_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Flexible Learning/majors_manage_delete.php');
        });

    echo $table->render($majors);
}
