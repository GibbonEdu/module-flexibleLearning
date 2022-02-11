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

use Gibbon\Module\Reports\ReportTemplate;
use Gibbon\Module\Reports\Renderer\MpdfRenderer;
use Gibbon\Module\Reports\Renderer\ReportRendererInterface;
use Gibbon\Module\FlexibleLearning\Domain\UnitGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitBlockGateway;
use Gibbon\Module\Reports\ReportData;

include '../../gibbon.php';

$autoloader->addPsr4('Gibbon\\Module\\Reports\\', $session->get('absolutePath').'/modules/Reports/src');
$autoloader->register(true);

$URL = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address']).'/booklet_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Flexible Learning/booklet_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // Proceed!
    $partialFail = false;

    $unitGateway = $container->get(UnitGateway::class);
    $unitBlockGateway = $container->get(UnitBlockGateway::class);

    // Setup the data to be rendered
    $offlineUnits = $unitGateway->selectOfflineUnits()->fetchGrouped();

    foreach ($offlineUnits as $grpIndex => $units) {
        foreach ($units as $unitIndex => $unit) {
            $ids = ['flexibleLearningUnitID' => $unit['flexibleLearningUnitID']];
            $offlineUnits[$grpIndex][$unitIndex]['blocks'] = $unitBlockGateway->selectBlocksByUnit($unit['flexibleLearningUnitID'])->fetchAll();
        }
    }

    // Setup the template
    $filename = 'FlexibleLearningBooklet.pdf';
    $file = tmpfile();
    $path = stream_get_meta_data($file)['uri'];

    $template = $container->get(ReportTemplate::class);

    $template->addData([
        'absolutePath'    => $session->get('absolutePath'),
        'absoluteURL'     => $session->get('absoluteURL'),
        'customAssetPath' => '/modules/Flexible Learning',
        'stylesheet'      => 'booklet/stylesheet.twig.html',
    ]);

    $template->addData(['fonts' => [
            'caveat' => [
                'R' => 'Caveat-Regular.ttf',
            ],
        ]
    ]);

    // Add sections to the template
    $template->addSection('booklet/cover.twig.html')
             ->addData(['thing' => 'something']);
    
    $template->addSection('booklet/unit.twig.html')
             ->addDataSource('flexibleLearning', 'flexibleLearning');

    $template->addHeader('booklet/header.twig.html');
    $template->addFooter('booklet/footer.twig.html');

    // Setup the report data to pass to the renderer
    $reportData = new ReportData($ids);
    $reportData->addData('flexibleLearning', ['units' => $offlineUnits]);
    $reports = [$reportData];

    // Setup the renderer
    $renderer = $container->get(MpdfRenderer::class);
    // $renderer->setMode(ReportRendererInterface::OUTPUT_CONTINUOUS | ReportRendererInterface::OUTPUT_TWO_SIDED);

    // Render to a temp file (for now)
    $renderer->render($template, $reports, $path);

    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="'.htmlentities($filename).'"' );
    echo file_get_contents($path);
    exit;

    // $URL .= $partialFail
    //     ? '&return=error2'
    //     : '&return=success0';
    // header("Location: {$URL}");
}
