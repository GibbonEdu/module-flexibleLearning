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

class UnitGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'flexibleLearningUnit';
    private static $primaryKey = 'flexibleLearningUnitID';
    private static $searchableColumns = ['flexibleLearningUnit.name'];

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryAllUnits(QueryCriteria $criteria, $gibbonPersonID = null, $gibbonPersonIDCreator = null, $roleCategory = null, $showInactive = false)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['flexibleLearningUnit.*', 'flexibleLearningMajor1.name AS major1', '(timestamp > CURRENT_DATE - interval 7 day) as new', 'flexibleLearningMajor2.name AS major2', 'flexibleLearningCategory.color', 'flexibleLearningCategory.name AS category', 'gibbonPerson.preferredName', 'gibbonPerson.surname', 'gibbonPerson.status', "(SELECT SUM(length) FROM flexibleLearningUnitBlock WHERE flexibleLearningUnitID=flexibleLearningUnit.flexibleLearningUnitID) as length"])
            ->innerJoin('flexibleLearningCategory', 'flexibleLearningCategory.flexibleLearningCategoryID=flexibleLearningUnit.flexibleLearningCategoryID')
            ->innerJoin('gibbonPerson', 'gibbonPerson.gibbonPersonID=flexibleLearningUnit.gibbonPersonIDCreator')
            ->leftJoin('flexibleLearningMajor AS flexibleLearningMajor1', 'flexibleLearningUnit.flexibleLearningMajorID1=flexibleLearningMajor1.flexibleLearningMajorID')
            ->leftJoin('flexibleLearningMajor AS flexibleLearningMajor2', 'flexibleLearningUnit.flexibleLearningMajorID2=flexibleLearningMajor2.flexibleLearningMajorID');

        if (!empty($gibbonPersonID)) {
            $query->cols(['flexibleLearningUnitSubmissionID as submitted'])
                ->leftJoin('flexibleLearningUnitSubmission', 'flexibleLearningUnitSubmission.flexibleLearningUnitID=flexibleLearningUnit.flexibleLearningUnitID AND flexibleLearningUnitSubmission.gibbonPersonID=:gibbonPersonID')
                ->bindValue('gibbonPersonID', $gibbonPersonID);
        }

        if (!empty($gibbonPersonIDCreator)) {
            $query->where('gibbonPersonIDCreator=:gibbonPersonIDCreator')
                ->bindValue('gibbonPersonIDCreator', $gibbonPersonIDCreator);
        }

        if (!empty($roleCategory)) {
            switch ($roleCategory) {
                case 'Student':
                    $query->where("flexibleLearningUnit.availableStudent<>'No'");
                    break;

                case 'Parent':
                    $query->where("flexibleLearningUnit.availableParent<>'No'");
                    break;

                case 'Staff':
                    $query->where("flexibleLearningUnit.availableStaff<>'No'");
                    break;

                case 'Other':
                    $query->where("flexibleLearningUnit.availableOther<>'No'");
                    break;
            }
        }

        if (!$showInactive) {
          $query->where("flexibleLearningUnit.active='Y'");
        }

        $criteria->addFilterRules([
            'active' => function ($query, $active) {
                return $query
                    ->where('flexibleLearningUnit.active = :active')
                    ->bindValue('active', $active);
            },
            'major' => function ($query, $major) {
                return $query
                    ->where('(flexibleLearningMajor1.flexibleLearningMajorID = :major OR flexibleLearningMajor2.flexibleLearningMajorID = :major)')
                    ->bindValue('major', $major);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function getUnitByID($flexibleLearningUnitID, $gibbonPersonID = null)
    {
        $query = $this
            ->newSelect()
            ->from($this->getTableName())
            ->cols(['flexibleLearningUnit.*', 'flexibleLearningMajor1.name AS major1', 'flexibleLearningMajor2.name AS major2', 'flexibleLearningCategory.color', 'flexibleLearningCategory.name AS category', 'gibbonPerson.preferredName', 'gibbonPerson.surname', 'gibbonPerson.status', "(SELECT SUM(length) FROM flexibleLearningUnitBlock WHERE flexibleLearningUnitID=flexibleLearningUnit.flexibleLearningUnitID) as length"])
            ->innerJoin('flexibleLearningCategory', 'flexibleLearningCategory.flexibleLearningCategoryID=flexibleLearningUnit.flexibleLearningCategoryID')
            ->leftJoin('flexibleLearningMajor AS flexibleLearningMajor1', 'flexibleLearningUnit.flexibleLearningMajorID1=flexibleLearningMajor1.flexibleLearningMajorID')
            ->leftJoin('flexibleLearningMajor AS flexibleLearningMajor2', 'flexibleLearningUnit.flexibleLearningMajorID2=flexibleLearningMajor2.flexibleLearningMajorID')
            ->innerJoin('gibbonPerson', 'gibbonPerson.gibbonPersonID=flexibleLearningUnit.gibbonPersonIDCreator')
            ->where('flexibleLearningUnitID=:flexibleLearningUnitID')
            ->bindValue('flexibleLearningUnitID', $flexibleLearningUnitID);

        if (!is_null($gibbonPersonID)) {
            $query->where('gibbonPersonIDCreator=:gibbonPersonID')
                ->bindValue('gibbonPersonID', $gibbonPersonID);
        }

        return $this->runSelect($query)->fetch();
    }

    public function selectAllUnits()
    {
        $sql = "SELECT flexibleLearningCategory.name as groupBy, flexibleLearningUnitID as value, flexibleLearningUnit.name
            FROM flexibleLearningUnit
            JOIN flexibleLearningCategory ON (flexibleLearningCategory.flexibleLearningCategoryID=flexibleLearningUnit.flexibleLearningCategoryID)
            ORDER BY flexibleLearningCategory.sequenceNumber, flexibleLearningUnit.name";

        return $this->db()->select($sql);
    }

    public function getRandomUnit()
    {
        $sql = "SELECT flexibleLearningUnitID
          FROM flexibleLearningUnit
          WHERE active='Y'
          ORDER BY RAND()
          LIMIT 1
          ";

        return $this->db()->selectOne($sql);
    }

}
