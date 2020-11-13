<?php

use Gibbon\View\View;
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

  //Data TABLE
  $table = DataTable::createDetails('unitDetails');

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
                  $relativeTime = Format::tooltip(__n('{count} hr', '{count} '.__m('hrs'), ceil($minutes / 60), ['count' => $hours]), $relativeTime);
              }

              $output = !empty($timing) ? $relativeTime : Format::small(__('N/A'));
          }

          return $output;
      });
      $table->addColumn('logo', '')
      ->addClass('row-span-3 text-right')
      ->format(function ($values) use ($gibbon) {
          if ($values['logo'] == null) {
              return "<img style='margin: 5px; height: 125px; width: 125px' class='user' src='".$gibbon->session->get('absoluteURL').'/themes/'.$gibbon->session->get('gibbonThemeName')."/img/anonymous_125.jpg'/><br/>";
          } else {
              return "<img style='margin: 5px; height: 125px; width: 125px' class='user' src='".$values['logo']."'/><br/>";
          }
      });
  $table->addColumn('category', __('Category'));
  $table->addColumn('majors', __('Majors'))
    ->sortable(['major1', 'major2'])
      ->format(function ($unit) {
          $majors = [$unit['major1'], $unit['major2']];
          return implode(', ', array_filter($majors));
        });
  $table->addColumn('minors', __('Minors'))
    ->sortable(['minor1', 'minor2'])
        ->format(function ($unit) {
          $minors = [$unit['minor1'],$unit['minor2']];
          return implode(', ', array_filter($minors));
      });

      echo $table->render([$values]);

      // SMART BLOCKS

      $dataBlocks = ['flexibleLearningUnitID' => $flexibleLearningUnitID];
      $sqlBlocks = 'SELECT * FROM flexibleLearningUnitBlock WHERE flexibleLearningUnitID=:flexibleLearningUnitID ORDER BY sequenceNumber';

      $blocks = $pdo->select($sqlBlocks, $dataBlocks)->fetchAll();

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
}
