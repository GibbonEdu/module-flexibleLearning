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

use Gibbon\Services\Format;
use Gibbon\Module\FlexibleLearning\Domain\CategoryGateway;

require_once '../../gibbon.php';

$flexibleLearningCategoryID = $_POST['flexibleLearningCategoryID'] ?? '';

$URL = $session->get('absoluteURL').'/index.php?q=/modules/Flexible Learning/categories_manage_edit.php&flexibleLearningCategoryID='.$flexibleLearningCategoryID;

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/categories_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {

    // Proceed!
    $categoryGateway = $container->get(CategoryGateway::class);

    $data = [
        'name'          => $_POST['name'] ?? '',
        'description'   => $_POST['description'] ?? '',
        'color'         => $_POST['color'] ?? '',
        'active'        => $_POST['active'] ?? '',
    ];

    // Validate the required values are present
    if (empty($flexibleLearningCategoryID) || empty($data['name']) || empty($data['description']) || empty($data['color']) || empty($data['active']) ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    if (!$categoryGateway->exists($flexibleLearningCategoryID)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Validate that this record is unique
    if (!$categoryGateway->unique($data, ['name'], $flexibleLearningCategoryID)) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit;
    }

    // Update the record
    $updated = $categoryGateway->update($flexibleLearningCategoryID, $data);

    $URL .= !$updated
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
