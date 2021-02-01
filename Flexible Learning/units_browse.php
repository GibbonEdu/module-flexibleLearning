<?php

use Gibbon\View\View;
use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Tables\View\GridView;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\MajorGateway;
use Gibbon\Module\FlexibleLearning\Domain\CategoryGateway;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $name = $_GET['name'] ?? '';
    $major = $_GET['major'] ?? '';

    $templateView = new View($container->get('twig'));
    $unitGateway = $container->get(UnitGateway::class);
    $canManage = isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_manage.php');
    $roleCategory = getRoleCategory($gibbon->session->get('gibbonRoleIDCurrent'), $connection2);

    $criteria = $unitGateway->newQueryCriteria()
        ->searchBy($unitGateway->getSearchableColumns(), $name)
        ->sortBy(['sequenceNumber', 'name'])
        ->filterBy('major', $major)
        ->fromPOST();

    $units = $unitGateway->queryAllUnits($criteria, $gibbon->session->get('gibbonPersonID'), null, !$canManage ? $roleCategory : null);
    $categories = $container->get(CategoryGateway::class)->selectActiveCategories()->fetchAll();
    $majors = $container->get(MajorGateway::class)->selectMajors()->fetchKeyPair();
    $randomID = $unitGateway->getRandomUnit();

    $page->breadcrumbs->add(__m('Browse Units'));

    // FORM
    $form = Form::create('filter', $gibbon->session->get('absoluteURL') . '/index.php', 'get');
    $form->setTitle(__('Filter'));

    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/Flexible Learning/units_browse.php');

    $row = $form->addRow();
    $row->addLabel('name', __('Name'));
    $row->addTextField('name')->setValue($criteria->getSearchText());

    $row = $form->addRow();
    $row->addLabel('major', __('Major'));
    $row->addSelect('major')->fromArray($majors)->placeholder()->selected($major);

    $row = $form->addRow();
    $row->addSearchSubmit($gibbon->session, __('Clear Filters'));

    echo $form->getOutput();

    // GRID TABLE
    $gridRenderer = new GridView($container->get('twig'));
    $defaultImage = $gibbon->session->get('absoluteURL').'/themes/'.$gibbon->session->get('gibbonThemeName').'/img/anonymous_125.jpg';
    $viewUnitURL = "./index.php?q=/modules/Flexible Learning/units_browse_details.php&sidebar=true";

    $table = $container->get(DataTable::class)->setRenderer($gridRenderer);
    $table->setTitle(__('Units'));
    $table->setDescription($templateView->fetchFromTemplate('unitLegend.twig.html', ['categories' => $categories, 'randomID' => $randomID]));

    $table->addMetaData('gridClass', 'flex items-stretch -mx-1');
    $table->addMetaData('gridItemClass', 'foo');
    $table->addMetaData('hidePagination', true);

    $table->addColumn('logo')
        ->setClass('h-full pb-2')
        ->format(function ($unit) use (&$templateView, &$defaultImage, &$viewUnitURL) {
            return $templateView->fetchFromTemplate(
                'unitCard.twig.html',
                $unit + ['defaultImage' => $defaultImage, 'viewUnitURL' => $viewUnitURL, 'viewingAsUser' => 'Student']
            );
        });

    echo $table->render($units);
}
