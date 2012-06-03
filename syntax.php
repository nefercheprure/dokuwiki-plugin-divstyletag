<?php
/**
 * DokuWiki Plugin divstyletag (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  nefercheprure <nefercheprure <hidden@>>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_divstyletag extends DokuWiki_Syntax_Plugin {
	public function getType() {
		return 'container';
	}

	public function getPType() {
		return 'normal';
	}

	public function getSort() {
		// shall be < code(200)
		// shall be > header(50)
		return 158;
	}

	/**
	 * Allowed Mode Types
	 *
	 * Defines the mode types for other dokuwiki markup that maybe nested within the
	 * plugin's own markup. Needs to return an array of one or more of the mode types
	 * defined in $PARSER_MODES in parser.php
	 */
	function getAllowedTypes() {
		global $PARSER_MODES;
		$rv = array(
			'container','substition','protected','disabled','formatting','paragraphs' /*
			'container','substition','protected','disabled','formatting','paragraphs',
			'baseonly' // */
		);
		return $rv;
	}

	// override default accepts() method to allow nesting
	// - ie, to get the plugin accepts its own entry syntax
	function accepts($mode)
	{
		if ($mode == substr(get_class($this), 7)) return true;
		return parent::accepts($mode);
	}

	public function connectTo($mode) {
		//FIXME: add pseudoheaders
		//if ($mode == substr(get_class($this), 7)) {
		//	$this->Lexer->addSpecialPattern( '[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)',$mode,'plugin_divstyletag');
		//}
		$this->Lexer->addEntryPattern('<div(?:(?: +style="(?:[^"<>]*)")?(?: +class="(?:[^"<>]*)")?)>(?=.*?</div>)',$mode,'plugin_divstyletag');

		//echo $mode . DOKU_LF;
	}

	public function postConnect() {
		$this->Lexer->addExitPattern('</div>','plugin_divstyletag');
		$this->Lexer->addPattern( '[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)','plugin_divstyletag');
	}

	public function handle($match, $state, $pos, &$handler) {
		switch ($state) {
		case DOKU_LEXER_ENTER:
			$val = '<div>';
			if ($this->_isValid($match)) {
				$val = $match;
			}
			return array($state, $val);
		case DOKU_LEXER_MATCHED:
			/*
			$sec = $handler->status['section'];
			$handler->status['section'] = false;
			$handler->header($match,$state,$pos);
			$handler->status['section'] = $sec;
			$handler->_addCall('section_close', array(), $pos);
			// */
			$title = trim($match);
			$level = 7 - strspn($title,'=');
			if($level < 1) $level = 1;
			$title = trim($title,'=');
			$title = trim($title);
			return array($state, array($title, $level, $pos));
		case DOKU_LEXER_UNMATCHED:
			$handler->_addCall('cdata', array($match), $pos);
			return false; 
			//return array($state,$match);
		case DOKU_LEXER_EXIT:
			return array($state, '');
		}
		return false;//array();
	}

	public function render($mode, &$renderer, $data) {
		if ($mode == 'xhtml') {
			list($state, $val) = $data;

			/* //DEBUG
			ob_start();
			echo 'THIS foobar'.DOKU_LF;
			var_dump($this);
			$y = ob_get_clean();
			$renderer->doc.='<pre>'.
			$renderer->_xmlEntities($y)
			.'</pre>'; // */

			switch ($state) {
			case DOKU_LEXER_ENTER:
				$renderer->doc .= $val;
				break;
			case DOKU_LEXER_EXIT:
				$renderer->doc .= '</div>';
				break;
			case DOKU_LEXER_MATCHED:
				list($text,$level,$pos) = $val;
				if(!$text) break; //skip empty headlines

				$hid = $renderer->_headerToLink($text,true);

				//only add items within configured levels
				$renderer->toc_additem($hid, $text, $level);

				// adjust $node to reflect hierarchy of levels
				$renderer->node[$level-1]++;
				if ($level < $renderer->lastlevel) {
					for ($i = 0; $i < $renderer->lastlevel-$level; $i++) {
						$renderer->node[$renderer->lastlevel-$i-1] = 0;
					}
				}
				$renderer->lastlevel = $level;

				// write the header
				$renderer->doc .= DOKU_LF.'<h'.$level;
				$renderer->doc .= '><a name="'.$hid.'" id="'.$hid.'">';
				$renderer->doc .= $renderer->_xmlEntities($text);
				$renderer->doc .= "</a></h$level>".DOKU_LF;
				break;
			}
			return true;
		}
		return false;
	}

	private function _isValid($match) {
		//FIXME 
		return true;
	}
}

// vim:ts=8:sw=8:noet:
