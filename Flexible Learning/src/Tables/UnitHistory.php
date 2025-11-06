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

namespace Gibbon\Module\FlexibleLearning\Tables;

use Gibbon\View\View;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\FlexibleLearning\Domain\UnitSubmissionGateway;

/**
 * UnitHistory
 *
 * @version v21
 * @since   v21
 */
class UnitHistory 
{
    protected $submissionGateway;
    protected $templateView;

    public function __construct(UnitSubmissionGateway $submissionGateway, View $templateView)
    {
        $this->submissionGateway = $submissionGateway;
        $this->templateView = $templateView;
    }

    public function create($gibbonPersonID, $canGiveFeedback = false)
    {
        $criteria = $this->submissionGateway->newQueryCriteria(true)
            ->sortBy(['timestampSubmitted'], 'DESC')
            ->fromPOST();

        $submissions = $this->submissionGateway->querySubmissionsByPerson($criteria, $gibbonPersonID);
        
        $table = DataTable::createPaginated('unitHistory', $criteria)->withData($submissions);

        $table->addExpandableColumn('commentStudent')
            ->format(function ($values) {
                if ($values['status'] == 'Current' || $values['status'] == 'Current - Pending') return;

                $logs = $this->submissionGateway->selectUnitSubmissionDiscussion($values['flexibleLearningUnitSubmissionID'])->fetchAll();

                return $this->templateView->fetchFromTemplate('ui/discussion.twig.html', [
                    'discussion' => $logs
                ]);
            });

        $table->addColumn('schoolYear', __('School Year'))->description(__('Date'))
            ->format(function($values) {
                return $values['schoolYear'].'<br/>'.Format::small(Format::date($values['timestampSubmitted']));
            });

        $table->addColumn('unit', __('Unit'))
            ->format(function($values) {
                $url = './index.php?q=/modules/Flexible Learning/units_browse_details.php&flexibleLearningUnitID=' . $values['flexibleLearningUnitID'] . '&flexibleLearningUnitSubmissionID='.$values['flexibleLearningUnitSubmissionID'].'&sidebar=true';
                return Format::link($url, $values['unit']);
            });

        $table->addColumn('timestampSubmitted', __('When'))->format(Format::using('relativeTime', 'timestampSubmitted'));

        $table->addColumn('status', __('Status'));

        $table->addColumn('evidence', __('Evidence'))
            ->notSortable()
            ->width('10%')
            ->format(function ($values) {
                if (empty($values['evidenceLocation'])) return;

                $url = $values['evidenceType'] == 'Link'
                    ? $values['evidenceLocation']
                    : './'.$values['evidenceLocation'];

                return Format::link($url, __('View'), ['target' => '_blank']);
            });

        // ACTIONS
        $table->addActionColumn()
            ->addParam('flexibleLearningUnitSubmissionID')
            ->addParam('flexibleLearningUnitID')
            ->addParam('sidebar', true)
            ->format(function ($values, $actions) use ($canGiveFeedback) {
                if ($canGiveFeedback) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Flexible Learning/units_browse_details_feedback.php');
                } else {
                    $actions->addAction('view', __('View'))
                        ->setURL('/modules/Flexible Learning/units_browse_details.php');
                }
                
            });

        return $table;
    }
}
