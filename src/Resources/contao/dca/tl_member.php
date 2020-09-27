<?php

/**
 * This file is part of a markocupic Contao Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Formulartest
 * @license    MIT
 * @see        https://github.com/markocupic/contao-pet-to-member-bundle
 *
 */

// Add fields to palette
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('pets_legend', 'personal_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('pets', 'pets_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member');

// Add Fields
$GLOBALS['TL_DCA']['tl_member']['fields']['pets'] = [
    'exclude'   => true,
    'search'    => true,
    'inputType' => 'multiColumnWizard',
    'eval'      => [
        'mandatory'    => false,
        'columnFields' => [
            'species' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_member']['species'],
                'exclude'   => true,
                'inputType' => 'text',
                'filter'    => true,
                'eval'      => [
                    'style'              => 'min-width:100px',
                    'mandatory'          => false,
                ],
            ],
        ],
    ],
    'sql'       => "blob NULL",
];

