<?php

use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Tables\View\GridView;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/units_browse.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {

  $name = $_GET['name'] ?? '';

  $unitGateway = $container->get(UnitGateway::class);
  $criteria = $unitGateway->newQueryCriteria()
      ->searchBy($unitGateway->getSearchableColumns(), $name)
      ->sortBy('sequenceNumber','name')
      ->filterBy('showInactive', 'Y')
      ->fromPOST();

  $units = $unitGateway->queryAllUnits($criteria);

  // FORM
  $form = Form::create('filter', $gibbon->session->get('absoluteURL').'/index.php', 'get');
  $form->setTitle(__('Filter'));

  $form->setClass('noIntBorder fullWidth');
  $form->addHiddenValue('q', '/modules/Flexible Learning/units_browse.php');

  $row = $form->addRow();
      $row->addLabel('name', __('Name'));
      $row->addTextField('name')->setValue($criteria->getSearchText());

  $row = $form->addRow();
      $row->addSearchSubmit($gibbon->session, __('Clear Filters'));

  echo $form->getOutput();

  // TABLE
  $gridRenderer = $container->get(GridView::class);
  $table = $container->get(DataTable::class)->setRenderer($gridRenderer);
  $table=DataTable::create('FlexibleLearning');
  $table->setRenderer($gridRenderer);
  $table->setTitle(__('Units'));
  $table->addMetaData('gridClass', 'rounded-sm border');
  $table->addMetaData('gridItemClass', 'w-1/3 sm:w-1/4 md:w-1/6 text-center');


  $table->addColumn('unit')
  ->addClass('h-full')
  ->format(function($units) use ($gibbon, $name) {
      $return = null;
      $background = ($units['color']) ? "background-color: ".$units['color'] : '';
      $return .= "<a class='h-full block text-black no-underline' href='".$gibbon->session->get('absoluteURL')."/index.php?q=/modules/Flexible Learning/units_browse_details.php&sidebar=true&flexibleLearningUnitID=".$units['flexibleLearningUnitID']."&name=$name'><div title='".str_replace("'", "&#39;", $units['blurb'])."' class='h-full text-center pb-8' style='".$background."'>";
      $return .= ($units['logo'] != '') ? "<img class='pt-10 pb-2 h-32' src='".$gibbon->session->get('absoluteURL').'/'.$units['logo']."'/><br/>":"<img class='pt-10 pb-2' style='max-width: 65px' src='".$gibbon->session->get('absoluteURL').'/themes/'.$gibbon->session->get('gibbonThemeName')."/img/anonymous_240_square.jpg'/><br/>";
      $return .= "<span class='font-bold underline'>".$units['name']."</span><br/>";
      $return .= "<span class='text-sm italic'>".$units['category']."</span><br/>";
      $return .= "</div></a>";

      return $return;
  });

  echo $table->render($units);
}
