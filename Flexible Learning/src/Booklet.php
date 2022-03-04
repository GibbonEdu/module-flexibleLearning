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

namespace Gibbon\Module\FlexibleLearning;

use Gibbon\Contracts\Services\Session;
use Gibbon\Module\Reports\ReportTemplate;
use Gibbon\Module\Reports\ReportData;
use Gibbon\Module\Reports\Renderer\MpdfRenderer;
use Gibbon\Module\FlexibleLearning\Domain\UnitBlockGateway;
use League\Container\ContainerAwareTrait;
use League\Container\ContainerAwareInterface;
use Gibbon\Services\Format;
use Gibbon\Module\Reports\Renderer\ReportRendererInterface;

class Booklet implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $unitBlockGateway;
    protected $units = [];
    protected $unitCount = 0;
    protected $config = [];

    public function __construct(UnitBlockGateway $unitBlockGateway)
    {
        // Override the ini to keep this process alive
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 1800);
        set_time_limit(1800);

        $this->unitBlockGateway = $unitBlockGateway;
    }

    public function addData($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function addUnit($unit, $group = '')
    {
        $time = 0;
        $blocks = $this->unitBlockGateway->selectBlocksByUnit($unit['flexibleLearningUnitID'])->fetchAll();
        foreach ($blocks as $index => $block) {
            $blocks[$index]['contents'] = $this->processBlockContents($block['contents']);
            // $time += intval($block['length']);
        }

        // $minutes = intval($time);
        // $time = !empty($time) ? __n('{count} min', '{count} mins', $minutes) : '';
        // if ($minutes > 60) {
        //     $hours = round($minutes / 60, 1);
        //     $time = __n('{count} hr', '{count} '.__m('hrs'), ceil($minutes / 60), ['count' => $hours]);
        // }

        $this->units[$group][] = $unit + ['blocks' => $blocks, 'time' => $time];
        $this->unitCount++;
    }

    public function render($path)
    {
        $session = $this->getContainer()->get(Session::class);
        $autoloader = $this->getContainer()->get('autoloader');

        $autoloader->addPsr4('Gibbon\\Module\\Reports\\', $session->get('absolutePath').'/modules/Reports/src');
        $autoloader->register(true);

        // Setup the template
        $template = $this->getContainer()->get(ReportTemplate::class);

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

        $makeBooklet = $this->unitCount > 1;
        $this->config['bookletName'] = $makeBooklet ? $this->config['bookletName'] : 'Flexible Learning';
        $this->config['marginLeft'] = $this->config['insideMargins'] ?? 10;
        $this->config['marginRight'] = 10;

        // Add sections to the template
        if ($makeBooklet) {
            $template->addSection('booklet/cover.twig.html');
            $template->addSection('booklet/intro.twig.html');
        }
        
        $template->addSection('booklet/unit.twig.html')
                 ->addDataSource('flexibleLearning', 'flexibleLearning');

        $template->addHeader('booklet/header.twig.html', 0);
        // $template->addHeader('booklet/blank.twig.html', 1);
        // $template->addHeader('booklet/header.twig.html', 2);

        $template->addFooter('booklet/footer.twig.html', 0);

        if ($makeBooklet) {
            $template->addSection('booklet/back.twig.html');
        }

        $template->addData($this->config);

        // Setup the report data to pass to the renderer
        $reportData = new ReportData([]);
        $reportData->addData('flexibleLearning', ['units' => $this->units]);
        $reports = [$reportData];

        // Setup the renderer
        $renderer = $this->getContainer()->get(MpdfRenderer::class);
        $renderer->setMode(ReportRendererInterface::OUTPUT_MIRROR);

        // Render to a temp file (for now)
        $renderer->render($template, $reports, $path);
    }

    public function export($path, $filename = null)
    {
        $filename = $filename ?? basename($path);

        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.htmlentities($filename).'"' );
        echo file_get_contents($path);
        exit;
    }

    public function createTempFile()
    {
        $file = tmpfile();
        return stream_get_meta_data($file)['uri'];
    }

    private function processBlockContents($contents)
    {
        $contents = str_replace([
            '<table',
            '</table>',
        ], [
            '<columnbreak><columns column-count="1"><table style="font-size: 9pt;"',
            '</table><columns column-count="2" vAlign="" v-align="">',
        ], $contents);

        $contents = preg_replace('/(?:<p><\/p>)?\s*\n*(?:<p[^>]*>)?\s*\n*<iframe[^>]*src="([^"]*)"[^>]*>[^<]*<\/iframe>\s*\n*(?:<\/p>)?/i', '<div class="video-link"><a href="$1">$1</a></div><br/>', $contents);

        return $contents;
    }
}
