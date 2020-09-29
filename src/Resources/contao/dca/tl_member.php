<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Add fields to palette
PaletteManipulator::create()
	->addLegend('pets_legend', 'personal_legend', PaletteManipulator::POSITION_AFTER)
	->addField('pets', 'pets_legend', PaletteManipulator::POSITION_APPEND)
	->applyToPalette('default', 'tl_member');

// Add Fields
$GLOBALS['TL_DCA']['tl_member']['fields']['pets'] = array(
	'exclude'   => true,
	'search'    => true,
	'inputType' => 'multiColumnWizard',
	'eval'      => array(
		'mandatory'    => false,
		'columnFields' => array(
			'species' => array(
				'label'     => &$GLOBALS['TL_LANG']['tl_member']['species'],
				'exclude'   => true,
				'inputType' => 'text',
				'filter'    => true,
				'eval'      => array(
					'style'              => 'min-width:100px',
					'mandatory'          => false,
				),
			),
		),
	),
	'sql'       => "blob NULL",
);
