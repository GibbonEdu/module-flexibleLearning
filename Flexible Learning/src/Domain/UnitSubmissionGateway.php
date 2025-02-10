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

namespace Gibbon\Module\FlexibleLearning\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class UnitSubmissionGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'flexibleLearningUnitSubmission';
    private static $primaryKey = 'flexibleLearningUnitSubmissionID';
    private static $searchableColumns = [''];


    public function querySubmissionsByPerson(QueryCriteria $criteria, $gibbonPersonID)
    {
        $query = $this
            ->newQuery()
            ->cols(['flexibleLearningUnit.name AS unit', 'flexibleLearningUnit.flexibleLearningUnitID', 'gibbonPerson.gibbonPersonID', 'gibbonPerson.surname AS surname', 'gibbonPerson.preferredName AS preferredName', 'flexibleLearningUnitSubmission.*',  'gibbonRole.category', 'gibbonSchoolYear.name as schoolYear'])
            ->from('flexibleLearningUnit')
            ->innerJoin('flexibleLearningUnitSubmission', 'flexibleLearningUnitSubmission.flexibleLearningUnitID=flexibleLearningUnit.flexibleLearningUnitID')
            ->innerJoin('gibbonPerson', 'flexibleLearningUnitSubmission.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->innerJoin('gibbonRole', 'gibbonPerson.gibbonRoleIDPrimary=gibbonRole.gibbonRoleID')
            ->innerJoin('gibbonSchoolYear', 'gibbonSchoolYear.gibbonSchoolYearID=flexibleLearningUnitSubmission.gibbonSchoolYearID')
            ->where('flexibleLearningUnitSubmission.gibbonPersonID=:gibbonPersonID')
            ->bindValue('gibbonPersonID', $gibbonPersonID);

        return $this->runQuery($query, $criteria);
    }

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryPendingFeedback(QueryCriteria $criteria, $gibbonSchoolYearID, $gibbonPersonID = null)
    {
        $query = $this
            ->newQuery()
            ->cols(['flexibleLearningUnit.name AS unit', 'flexibleLearningUnit.flexibleLearningUnitID', 'gibbonPerson.gibbonPersonID', 'gibbonPerson.surname AS surname', 'gibbonPerson.preferredName AS preferredName', 'flexibleLearningUnitSubmission.*',  'gibbonRole.category'])
            ->from('flexibleLearningUnit')
            ->innerJoin('flexibleLearningUnitSubmission', 'flexibleLearningUnitSubmission.flexibleLearningUnitID=flexibleLearningUnit.flexibleLearningUnitID')
            ->innerJoin('gibbonPerson', 'flexibleLearningUnitSubmission.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->innerJoin('gibbonRole', 'gibbonPerson.gibbonRoleIDPrimary=gibbonRole.gibbonRoleID')
            ->where('flexibleLearningUnitSubmission.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID);

        if (!empty($gibbonPersonID)) {
            $query->where('flexibleLearningUnitSubmission.gibbonPersonID=:gibbonPersonID')
                ->bindValue('gibbonPersonID', $gibbonPersonID);
        }

        $criteria->addFilterRules([
            'myUnits' => function ($query, $gibbonPersonIDCreator) {
                if (empty($gibbonPersonIDCreator)) return $query;

                return $query
                    ->where('flexibleLearningUnit.gibbonPersonIDCreator=:gibbonPersonIDCreator')
                    ->bindValue('gibbonPersonIDCreator', $gibbonPersonIDCreator);
            },
            'flexibleLearningUnitID' => function ($query, $flexibleLearningUnitID) {
                return $query
                    ->where('flexibleLearningUnit.flexibleLearningUnitID=:flexibleLearningUnitID ')
                    ->bindValue('flexibleLearningUnitID', $flexibleLearningUnitID);
            },
            'gibbonPersonID' => function ($query, $gibbonPersonID) {
                return $query
                    ->where('flexibleLearningUnitSubmission.gibbonPersonID=:gibbonPersonID ')
                    ->bindValue('gibbonPersonID', $gibbonPersonID);
            },
            'status' => function ($query, $status) {
                return $query
                    ->where('flexibleLearningUnitSubmission.status=:status')
                    ->bindValue('status', ucfirst($status));
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function selectUnitSubmissionDiscussion($flexibleLearningUnitSubmissionID)
    {
        $query = $this
            ->newSelect()
            ->cols(['gibbonDiscussion.gibbonDiscussionID', 'gibbonDiscussion.comment', 'gibbonDiscussion.type', 'gibbonDiscussion.tag', 'gibbonDiscussion.attachmentType', 'gibbonDiscussion.attachmentLocation', 'gibbonPerson.gibbonPersonID', 'gibbonPerson.title', 'gibbonPerson.surname', 'gibbonPerson.preferredName', 'gibbonPerson.image_240', 'gibbonPerson.username', 'gibbonPerson.email', 'gibbonRole.category', 'gibbonDiscussion.timestamp'])
            ->from('gibbonDiscussion')
            ->innerJoin('gibbonPerson', 'gibbonDiscussion.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->innerJoin('gibbonRole', 'gibbonRole.gibbonRoleID=gibbonPerson.gibbonRoleIDPrimary')
            ->where('gibbonDiscussion.foreignTable = :foreignTable')
            ->bindValue('foreignTable', 'flexibleLearningUnitSubmission')
            ->where('gibbonDiscussion.foreignTableID = :foreignTableID')
            ->bindValue('foreignTableID', $flexibleLearningUnitSubmissionID);

        return $this->runSelect($query);
    }
}
