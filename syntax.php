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
		//return 'FIXME: container|baseonly|formatting|substition|protected|disabled|paragraphs';
		return 'container';
	}

	public function getPType() {
		//return 'FIXME: normal|block|stack';
		return 'normal';
	}

	public function getSort() {
		return 12453;
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

		$rv = array_merge (
			$PARSER_MODES['container'],
			$PARSER_MODES['baseonly'],
			$PARSER_MODES['paragraphs'],
			$PARSER_MODES['formatting'],
			$PARSER_MODES['substition'],
			$PARSER_MODES['protected'],
			$PARSER_MODES['disabled']
		);
 
		return $rv;
	}


	public function connectTo($mode) {
		//$this->Lexer->addSpecialPattern('<FIXME>',$mode,'plugin_divstyletag');
		$this->Lexer->addEntryPattern('<div(?:(?: +style="(?:[^"<>]*)")?(?: +class="(?:[^"<>]*)"))?>(?=.*?</div>)',$mode,'plugin_divstyletag');
	}

	public function postConnect() {
		$this->Lexer->addExitPattern('</div>','plugin_divstyletag');
	}

	public function handle($match, $state, $pos, &$handler) {
		switch ($state) {
		case DOKU_LEXER_ENTER:
			$val = '<div>';
			if ($this->_isValid($match)) {
				$val = $match;
			}
			return array($state, $val);

		case DOKU_LEXER_UNMATCHED:
			$handler->_addCall('cdata', array($match), $pos);
			return array($state,'');
		case DOKU_LEXER_EXIT:
			return array($state, '');
		}
		return array();
	}

	public function render($mode, &$renderer, $data) {
		if ($mode == 'xhtml') {
			list($state, $val) = $data;
			switch ($state) {
			case DOKU_LEXER_ENTER:
				$renderer->doc .= $val;
			case DOKU_LEXER_EXIT:
				$renderer->doc .= '</div>';
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
