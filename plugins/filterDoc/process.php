<?php

/**
 * This is processfilters guts
 *
 * @package plugins/filterDoc
 */
require_once(CORE_SERVERPATH . 'setup/setup-functions.php');

function processFilters() {
	global $_zp_resident_files;

	$uses = $filterDescriptions = $classes = $subclasses = array();


	$htmlfile = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/filter list.html';
	$filterdesc = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/filter descriptions.txt';
	if (file_exists($filterdesc)) {
		$t = file_get_contents($filterdesc);
		$t = explode("\n", $t);
		foreach ($t as $d) {
			$d = trim($d);
			if (!empty($d) && $d[0] != '#') {
				$f = explode(':=', $d);
				$f[] = ''; //	be sure there is at least two elements
				$filter = trim($f[0], '.');
				if ($filter[0] == '*') {
					$classes = array('class' => NULL, 'subclass' => NULL);
				} else if ($filter[0] == '!') {
					$uses[substr($filter, 1)] = $f[1];
					continue;
				} else {
					$classes = explode('>', $filter);
					$filter = array_pop($classes);
					if (empty($classes)) {
						$classes = array('class' => NULL, 'subclass' => NULL);
					}
				}
				$filterDescriptions[$filter] = array('class' => array_shift($classes), 'subclass' => array_shift($classes), 'desc' => trim($f[1]));
			}
		}
	}

	$stdExclude = Array('Thumbs.db', 'readme.md', 'data');
	if (CASE_INSENSITIVE) {
		$serverpath = strtolower(SERVERPATH);
	} else {
		$serverpath = SERVERPATH;
	}
	getResidentZPFiles($serverpath . '/' . CORE_FOLDER, array_merge($stdExclude, array('functions-filter.php', 'deprecated-functions.php')));
	getResidentZPFiles($serverpath . '/' . THEMEFOLDER, $stdExclude);

	$filterlist = array();
	$registerList = array();

	foreach ($_zp_resident_files as $key => $file) {
		if (getSuffix($file) == 'php') {
			$size = filesize($file);
			$text = file_get_contents($file);
			$script = str_replace($serverpath . '/', '', $file);
			$script = str_replace(CORE_FOLDER . '/' . PLUGIN_FOLDER . '/', '<em>plugin</em>/', $script);
			$script = str_replace(CORE_FOLDER . '/', '<!--sort first-->/', $script);
			$script = str_replace(THEMEFOLDER . '/', '<em>theme</em>/', $script);
			preg_match_all('~(zp_apply_filter|zp_register_filter)\((.*?)\)[^,|\)]~', $text, $matches);
			if (!empty($matches)) {
				foreach ($matches[2] as $which => $paramsstr) {
					preg_match_all('~([^,]+\(.*\))|([^,]+)~u', $paramsstr, $parameters);
					$parameters = $parameters[0];
					foreach ($parameters as $key => $param) {
						$parameters[$key] = trim($param);
					}
					$filtername = myunQuote(trim(array_shift($parameters)));
					if (!array_key_exists($filtername, $filterlist)) {
						$filterlist[$filtername]['filter'] = $filtername;
					}

					if ($matches[1][$which] == 'zp_apply_filter') {
						$filterlist[$filtername]['applies'][] = $script;
						$filterlist[$filtername]['params'] = $parameters;
					} else {
						$filterlist[$filtername]['users'] = $script;
					}
				}
			}
		}
	}

	$filterCategories = array();
	$newfilterlist = array();
	foreach ($filterlist as $key => $filterData) {
		$calls = array();
		$class = '';
		$subclass = '';

		if (isset($filterData['applies']) && count($filterData['applies'])) {
			sort($filterData['applies']);
			$lastscript = $filterData['applies'][0];
			$count = 0;
			foreach ($filterData['applies'] as $script) {
				if (!$class) {
					if (isset($filterDescriptions[$key]['class']) && $filterDescriptions[$key]['class']) {
						//	class and subclass defined by filter descriptions file
						$class = $filterDescriptions[$key]['class'];
						$subclass = $filterDescriptions[$key]['subclass'];
					} else {
						//	make an educated guess
						$basename = basename($script);
						if (strpos($script, '<em>theme</em>') !== false || strpos($key, 'theme') !== false) {
							$class = 'Theme';
							$subclass = 'Script';
						} else if (strpos($basename, 'user') !== false || strpos($basename, 'auth') !== false ||
										strpos($basename, 'logon') !== false || strpos($key, 'login') !== false) {
							$class = 'User_management';
							$subclass = 'Miscellaneous';
						} else if (strpos($key, 'upload') !== false) {
							$class = 'Upload';
							$subclass = 'Miscellaneous';
						} else if (strpos($key, 'texteditor') !== false) {
							$class = 'Miscellaneous';
							$subclass = 'Miscellaneous';
						} else if (strpos($basename, 'class') !== false) {
							$class = 'Object';
							if (strpos($basename, 'zenpage') !== false) {
								$class = 'Object';
								$subclass = 'CMS';
							} else {
								if (!$subclass) {
									switch ($basename) {
										case 'classes.php':
											$subclass = 'Root_class';
											break;
										case 'load_objectClasses.php':
										case 'class-gallery.php':
											$subclass = 'Miscellaneous';
											break;
										case 'class-album.php':
										case 'class-image.php':
										case 'class-textobject.php':
										case 'class-textobject_core.php':
										case 'class-Anyfile.php';
										case 'class-video.php':
										case 'Class-WEBdocs.php':
											$subclass = 'Media';
											break;
										case 'class-comment.php':
											$subclass = 'Comments';
											break;
										case 'class-search.php':
											$subclass = 'Search';
											break;
									}
									if (strpos($key, 'image') !== false || strpos($key, 'album') !== false) {
										$subclass = 'Media';
									}
								}
							}
						} else if (strpos($script, 'admin') !== false) {
							$class = 'Admin';
							if (strpos($script, 'zenpage') !== false) {
								$subclass = 'CMS';
							} else if (strpos($basename, 'comment') !== false || strpos($key, 'comment')) {
								$subclass = 'Comment';
							} else if (strpos($basename, 'edit') !== false || strpos($key, 'album') !== false || strpos($key, 'image') !== false) {
								$subclass = 'Media';
							}
						} else if (strpos($script, 'template') !== false) {
							$class = 'Template';
						} else if (strpos($basename, 'zenpage') !== false || strpos($key, 'category') !== false || strpos($key, 'article') !== false || strpos($key, 'page') !== false) {
							$class = 'CMS';
						} else if (strpos($basename, 'comment') !== false || strpos($key, 'comment') !== false) {
							$class = 'Comment';
						} else if (strpos($basename, 'edit') !== false || strpos($key, 'album') !== false || strpos($key, 'image') !== false) {
							$class = 'Media';
						} else {
							$class = 'Miscellaneous';
						}
						if (!$subclass) {
							$subclass = 'Miscellaneous';
						}
					}
					if (!array_key_exists($key, $filterDescriptions)) {
						$filterDescriptions[$key]['desc'] = '';
					}
					$filterDescriptions[$key]['class'] = $class;
					$filterDescriptions[$key]['subclass'] = $subclass;

					if (!array_key_exists($class, $filterCategories)) {
						$filterCategories[$class] = array('class' => $class, 'subclass' => '', 'count' => 0);
					}
					if (!array_key_exists($class . '_' . $subclass, $filterCategories)) {
						$filterCategories[$class . '_' . $subclass] = array('class' => $class, 'subclass' => $subclass, 'count' => $filterCategories[$class]['count'] ++);
					}
					if ($class && !array_key_exists('*' . $class, $filterDescriptions)) {
						$filterDescriptions['*' . $class] = array('class' => NULL, 'subclass' => NULL, 'desc' => '');
					}
					if ($subclass && !array_key_exists('*' . $class . '.' . $subclass, $filterDescriptions)) {
						$filterDescriptions['*' . $class . '.' . $subclass] = array('class' => NULL, 'subclass' => NULL, 'desc' => '');
					}
				}

				if ($script == $lastscript) {
					$count ++;
				} else {
					if ($count > 1) {
						$count = " ($count)";
					} else {
						$count = '';
					}
					if ($lastscript) {
						$calls[] = $lastscript . $count;
					}
					$count = 1;
					$lastscript = $script;
				}
			}
			if ($count > 0) {
				if ($count > 1) {
					$count = " ($count)";
				} else {
					$count = '';
				}
				if ($lastscript) {
					$calls[] = $lastscript . $count;
				}
			}
			if (!isset($filterDescriptions[$key])) {
				$filterDescriptions[$key]['desc'] = '';
				$filterDescriptions[$key]['class'] = 'Miscellaneous';
				$filterDescriptions[$key]['subclass'] = 'Miscellaneous';
			}
		}
		$newparms = array();
		if (isset($filterData['params'])) {
			foreach ($filterData['params'] as $param) {
				if ($param == 'true' || $param == 'false') {
					$newparm = 'bool';
				} else if (substr($param, 0, 5) == 'array') {
					$newparm = 'array';
				} else if (is_numeric($param)) {
					$newparm = 'number';
				} else if ($param[0] == '$') {
					$newparm = 'var';
				} else {
					$newparm = 'string';
				}

				$newparms[] = $newparm;
			}
		}
		$newfilterlist[$key] = array('filter' => $key, 'calls' => $calls, 'users' => array(), 'params' => $newparms, 'desc' => '*Edit Description*', 'class' => $class, 'subclass' => $subclass);
	}

	$newfilterlist = sortMultiArray($newfilterlist, array('class', 'subclass', 'filter'), false, false);

	$f = fopen($htmlfile, 'w');
	$class = $subclass = NULL;

	fwrite($f, "<!-- Begin filter descriptions -->\n");
	$ulopen = false;
	foreach ($newfilterlist as $filter) {
		if (array_key_exists($filter['filter'], $filterDescriptions) && $filterDescriptions[$filter['filter']]['desc'] != '*dummy') {
			if ($class !== $filter['class']) {
				$class = $filter['class'];
				if (array_key_exists('*' . $class, $filterDescriptions)) {
					$classhead = '<p>' . $filterDescriptions['*' . $class]['desc'] . '</p>';
				} else {
					$classhead = '';
				}
				if ($subclass) {
					fwrite($f, "\t\t\t</ul><!-- filterdetail -->\n");
				}
				fwrite($f, "\t" . '<h5><span id="' . $class . '"></span>' . $class . " filters</h5>\n");
				fwrite($f, "\t" . '<!-- classhead ' . $class . ' -->' . $classhead . "<!--e-->\n");
				$subclass = NULL;
			}
			if ($subclass !== $filter['subclass']) { // new subclass
				if (!is_null($subclass)) {
					fwrite($f, "\t\t\t</ul><!-- filterdetail -->\n");
				}
				$subclass = $filter['subclass'];
				if (array_key_exists('*' . $class . '.' . $subclass, $filterDescriptions)) {
					$subclasshead = '<p>' . $filterDescriptions['*' . $class . '.' . $subclass]['desc'] . '</p>';
				} else {
					$subclasshead = '';
				}
				if (isset($filterCategories[$class]['count']) && $filterCategories[$class]['count'] > 1) { //	Class doc is adequate.
					fwrite($f, "\t\t\t" . '<h6 class="filter"><span id="' . $class . '_' . $subclass . '"></span>' . $subclass . "</h6>\n");
					fwrite($f, "\t\t\t" . '<!-- subclasshead ' . $class . '.' . $subclass . ' -->' . $subclasshead . "<!--e-->\n");
				}
				fwrite($f, "\t\t\t" . '<ul class="filterdetail">' . "\n");
			}
			fwrite($f, "\t\t\t\t" . '<li class="filterdetail">' . "\n");
			fwrite($f, "\t\t\t\t\t" . '<p class="filterdef"><span class="inlinecode"><strong>' . html_encode($filter['filter']) . '</strong></span>(<em>' . html_encode(implode(', ', $filter['params'])) . "</em>)</p>\n");
			if (array_key_exists($filter['filter'], $filterDescriptions)) {
				$filter['desc'] = '<p>' . $filterDescriptions[$filter['filter']]['desc'] . '</p>';
			}
			fwrite($f, "\t\t\t\t\t" . '<!-- description(' . $class . '.' . $subclass . ')-' . $filter['filter'] . ' -->' . $filter['desc'] . "<!--e-->\n");

			$user = array_shift($filter['users']);
			if ($user) {
				fwrite($f, "\t\t\t\t\t" . '<p class="handlers">For example see ' . mytrim($user) . '</p>' . "\n");
			}
			fwrite($f, "\t\t\t\t\t" . '<p class="calls">Invoked from:' . "</p>\n");
			fwrite($f, "\t\t\t\t\t<ul><!-- calls -->\n");
			$calls = $filter['calls'];
			if (empty($calls)) {
				if (isset($uses[$filter['filter']])) {
					$calls[] = $uses[$filter['filter']];
				}
			}
			$limit = 4;
			foreach ($calls as $call) {
				$limit --;
				if ($limit > 0) {
					fwrite($f, "\t\t\t\t\t\t" . '<li class="call_list">' . mytrim($call) . "</li>\n");
				} else {
					fwrite($f, "\t\t\t\t\t\t<li>...</li>\n");
					break;
				}
			}
			fwrite($f, "\t\t\t\t\t" . "</ul><!-- calls -->\n");
			if ($limit > 0) {
				fwrite($f, "\t\t\t\t\t" . '<br />');
			}

			fwrite($f, "\t\t\t\t" . '</li><!-- filterdetail -->' . "\n");
		}
	}

	fwrite($f, "\t\t\t" . '</ul><!-- filterdetail -->' . "\n");
	fwrite($f, "<!-- End filter descriptions -->\n");
	fclose($f);

	$filterCategories = sortMultiArray($filterCategories, array('class', 'subclass'), false, false);

	$indexfile = $serverpath . '/' . USER_PLUGIN_FOLDER . '/filterDoc/filter list_index.html';
	$f = fopen($indexfile, 'w');
	fwrite($f, "\t<ul>\n");
	$liopen = $ulopen = false;
	foreach ($filterCategories as $element) {
		$class = $element['class'];
		$subclass = $element['subclass'];
		if ($subclass == '') { // this is a new class element
			$count = $element['count'];
			if ($ulopen) {
				fwrite($f, "\t\t</ul>\n");
				$ulopen = false;
			}
			if ($liopen) {
				fwrite($f, "\t\t</li>\n");
				$liopen = false;
			}
			fwrite($f, "\t\t" . '<li><a title="' . $class . ' filters" href="#' . $class . '">' . $class . " filters</a>\n");
			$liopen = true;
		} else {
			if ($class != $subclass) {
				if ($count > 1) {
					if (!$ulopen) {
						fwrite($f, "\t\t<ul>\n");
						$ulopen = true;
					}
					fwrite($f, "\t\t\t\t" . '<li><a title="' . $subclass . ' ' . $class . ' filters" href="#' . $class . '_' . $subclass . '">' . $subclass . "</a></li>\n");
				} else {
					unset($filterDescriptions['*' . $class . '.' . $subclass]);
				}
			}
		}
	}
	if ($ulopen) {
		fwrite($f, "\t\t</ul>\n");
	}
	if ($liopen) {
		fwrite($f, "\t\t</li>\n");
	}
	fwrite($f, "\t</ul>\n");
	fclose($f);

	$unseen = $unused = $descriptions = array();
	foreach ($filterDescriptions as $filter => $desc) {
		if ($filter[0] != '*') {
			if (!isset($filterlist[$filter]['users'])) {
				$unused[$filter] = $filter;
			}
			if (!isset($filterlist[$filter]['applies'])) {
				$unseen[$filter] = $filter;
			}
		}
		if (!empty($desc['class'])) {
			$filter = $desc['class'] . '>' . $desc['subclass'] . '>' . $filter;
		}
		$descriptions[$filter] = $desc;
	}

	ksort($descriptions);

	$f = fopen($filterdesc, 'w');
	if (!empty($unseen)) {
		ksort($unseen);
		fwrite($f, "#These filters appear not to be applied.\n");
		foreach ($unseen as $filter) {
			if (isset($uses[$filter]) && $uses[$filter]) {
				$used = ':=' . $uses[$filter];
			} else {
				$used = '';
			}
			fwrite($f, '!' . $filter . $used . "\n");
		}
		fwrite($f, "\n");
	}
	if (!empty($unused)) {
		ksort($unused);
		fwrite($f, "#These filters appear not to be registered.\n");
		foreach ($unused as $filter) {
			if (isset($uses[$filter]) && $uses[$filter]) {
				$used = ':=' . $uses[$filter];
			} else {
				$used = '';
			}
			fwrite($f, '!' . $filter . $used . "\n");
		}
		fwrite($f, "\n");
	}

	$class = '';
	$msg = "#These filters have no description\n";
	foreach ($descriptions as $filter => $desc) {
		if (empty($desc['desc']) || $desc['desc'][0] == '*') {
			if ($msg && empty($desc['desc'])) {
				fwrite($f, $msg);
				$msg = NULL;
			}
			if ($class != $desc['class']) {
				fwrite($f, "\n");
				$class = $desc['class'];
			}
			fwrite($f, $filter . ':=' . $desc['desc'] . "\n");
			unset($descriptions[$filter]);
		}
	}
	fwrite($f, "\n");

	$class = '';
	foreach ($descriptions as $filter => $desc) {
		if ($class != $desc['class']) {
			fwrite($f, "\n");
			$class = $desc['class'];
		}
		fwrite($f, $filter . ':=' . $desc['desc'] . "\n");
	}
	fclose($f);
}

//	create the doc file
$doc = '<div style="float:left;width:70%;">' .
				file_get_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/intro.html') .
				'</div>' .
				'<div style="float:right;width:30%;">' .
				file_get_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/filter list_index.html') .
				'</div>' .
				'<br clear="all">' .
				file_get_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/filter list.html');
file_put_contents(SERVERPATH . '/docs/filterDoc.htm', $doc);

function mytrim($str) {
	return trim(str_replace('<!--sort first-->/', '', $str));
}

function myunQuote($string) {
	preg_match_all('~[\"\'](.*?)[\"\']~', $string, $matches);
	if (!empty($matches)) {
		foreach ($matches[0] as $key => $quoted) {
			$string = str_replace($quoted, '#' . $key, $string);
		}
		$string = preg_replace('~\.~', '', $string);
		$string = preg_replace('~\s~', '', $string);
		foreach ($matches[1] as $key => $unquoted) {
			$string = str_replace('#' . $key, $unquoted, $string);
		}
	}
	return $string;
}
