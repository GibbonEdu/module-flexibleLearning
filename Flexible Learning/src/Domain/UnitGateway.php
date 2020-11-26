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
    public function queryAllUnits(QueryCriteria $criteria, $gibbonPersonID = null)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['flexibleLearningUnit.*', 'flexibleLearningMajor1.name AS major1', 'flexibleLearningMajor2.name AS major2', 'flexibleLearningCategory.color', 'flexibleLearningCategory.name AS category', 'gibbonPerson.preferredName', 'gibbonPerson.surname', 'gibbonPerson.status', "(SELECT SUM(length) FROM flexibleLearningUnitBlock WHERE flexibleLearningUnitID=flexibleLearningUnit.flexibleLearningUnitID) as length"])
            ->innerJoin('flexibleLearningCategory', 'flexibleLearningCategory.flexibleLearningCategoryID=flexibleLearningUnit.flexibleLearningCategoryID')
            ->leftJoin('flexibleLearningMajor AS flexibleLearningMajor1', 'flexibleLearningUnit.flexibleLearningMajorID1=flexibleLearningMajor1.flexibleLearningMajorID')
            ->leftJoin('flexibleLearningMajor AS flexibleLearningMajor2', 'flexibleLearningUnit.flexibleLearningMajorID2=flexibleLearningMajor2.flexibleLearningMajorID')
            ->innerJoin('gibbonPerson', 'gibbonPerson.gibbonPersonID=flexibleLearningUnit.gibbonPersonIDCreator');

          if (!is_null($gibbonPersonID)) {
            $query->where('gibbonPersonIDCreator=:gibbonPersonID')
              ->bindValue('gibbonPersonID', $gibbonPersonID);
          }

        return $this->runQuery($query, $criteria);
    }

    public function getUnitByID($flexibleLearningUnitID, $gibbonPersonID = null)
    {
        $query = $this
            ->newSelect()
            ->from($this->getTableName())
            ->cols(['flexibleLearningUnit.*','flexibleLearningMajor1.name AS major1', 'flexibleLearningMajor2.name AS major2', 'flexibleLearningCategory.color', 'flexibleLearningCategory.name AS category','gibbonPerson.preferredName', 'gibbonPerson.surname', 'gibbonPerson.status', "(SELECT SUM(length) FROM flexibleLearningUnitBlock WHERE flexibleLearningUnitID=flexibleLearningUnit.flexibleLearningUnitID) as length"])
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

}
