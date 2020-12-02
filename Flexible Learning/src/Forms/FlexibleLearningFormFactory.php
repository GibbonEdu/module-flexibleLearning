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

namespace Gibbon\Module\FlexibleLearning\Forms;

use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Forms\OutputableInterface;
use Gibbon\Contracts\Database\Connection;
use Gibbon\Contracts\Services\Session;

/**
 * FlexibleLearningFormFactory
 *
 * @version v16
 * @since   v16
 */
class FlexibleLearningFormFactory extends DatabaseFormFactory
{
    /**
     * Create and return an instance of DatabaseFormFactory.
     * @return  object DatabaseFormFactory
     */
    public static function create(Connection $pdo = null)
    {
        return new FlexibleLearningFormFactory($pdo);
    }

    /**
     * Creates a fully-configured CustomBlocks input for Smart Blocks in the lesson planner.
     *
     * @param string $name
     * @param Session $session
     * @param string $guid
     * @return OutputableInterface
     */
    public function createFlexibleLearningSmartBlocks($name, $session, $guid) : OutputableInterface
    {
        $blockTemplate = $this->createSmartBlockTemplate($guid);

        // Create and initialize the Custom Blocks
        $customBlocks = $this->createCustomBlocks($name, $session)
            ->fromTemplate($blockTemplate)
            ->settings([
                'inputNameStrategy' => 'string',
                'addOnEvent'        => 'click',
                'sortable'          => true,
                'orderName'         => 'order',
            ])
            ->placeholder(__('Smart Blocks listed here...'))
            ->addBlockButton('showHide', __('Show/Hide'), 'plus.png');

        return $customBlocks;
    }

    /**
     * Creates a template for displaying Outcomes in a CustomBlocks input.
     *
     * @param string $guid
     * @return OutputableInterface
     */
    public function createSmartBlockTemplate($guid) : OutputableInterface
    {
        $blockTemplate = $this->createTable()->setClass('blank w-full');
            $row = $blockTemplate->addRow();
            $row->addTextField('title')
                ->setClass('w-3/4 title focus:bg-white')
                ->placeholder(__('Title'))
                ->append('<input type="hidden" id="flexibleLearningUnitBlockID" name="flexibleLearningUnitBlockID" value="">');

            $row = $blockTemplate->addRow()->addClass('w-3/4 flex justify-between mt-1');
                $row->addTextField('type')->placeholder(__('type (e.g. discussion, outcome)'))
                    ->setClass('w-full focus:bg-white mr-1');
                $row->addTextField('length')->placeholder(__('length (min)'))
                    ->setClass('w-24 focus:bg-white')->prepend('');

            $smartBlockTemplate = getSettingByScope($this->pdo->getConnection(), 'Planner', 'smartBlockTemplate');
            $col = $blockTemplate->addRow()->addClass('showHide w-full')->addColumn();
                $col->addLabel('contentsLabel', __('Block Contents'))->setClass('mt-3 -mb-2');
                $col->addTextArea('contents', $guid)->setRows(20)->addData('tinymce')->addData('media', '1')->setValue($smartBlockTemplate);

            $col = $blockTemplate->addRow()->addClass('showHide w-full')->addColumn();
                $col->addLabel('teachersNotesLabel', __('Teacher\'s Notes'))->setClass('mt-3 -mb-2');
                $col->addTextArea('teachersNotes', $guid)->setRows(20)->addData('tinymce')->addData('media', '1');

        return $blockTemplate;
    }
}
