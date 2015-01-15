<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * custom light configuration
 */
$MCEcss = 'custom-light.css';
$MCEselector = "textarea.content,textarea.desc,textarea.extracontent";
$MCEspecial = "
  style_formats: [
		{title: 'Block formats',
			items: [
				{title: 'heading 4', block: 'h4'},
				{title: 'heading 5', block: 'h5'},
				{title: 'heading 6', block: 'h6'},
				{title: 'preformatted', block: 'pre'},
				{title: 'code', block: 'code'},
				{title: 'paragraph', block: 'p'}
			]
			},
		{title: 'styles',
			items: [
				{title: 'articlebox (center)', inline: 'span', classes: 'articlebox'},
				{title: 'articlebox-left', inline: 'span', classes: 'articlebox-left'},
				{title: 'articlebox warningnote(center)', inline: 'span', classes: 'articlebox warningnote'},
				{title: 'articlebox-left warning', inline: 'span', classes: 'articlebox-left warningnote'},
				{title: 'inlinecode', inline: 'span', classes: 'inlinecode'},
				{title: 'table_of_content_list', inline: 'span', classes: 'table_of_content_list'}
			]
		}
  ]
  ";
$MCEplugins = "advlist autolink lists link image charmap anchor " .
				"searchreplace visualchars visualblocks code fullscreen " .
				"insertdatetime media table contextmenu paste tinyzenpage directionality ";

$MCEtoolbars[1] = "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | ltr rtl tinyzenpage";

$MCEstatusbar = true;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
