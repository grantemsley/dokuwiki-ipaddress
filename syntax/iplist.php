<?php
/**
 * DokuWiki Plugin ipaddress (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Grant Emsley <grant@emsley.ca>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_ipaddress_iplist extends DokuWiki_Syntax_Plugin {
	
	function __construct() {
		$this->helper = plugin_load('helper','ipaddress');
	}
	
    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'substition';
    }
    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'block';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 115;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<IPList .*?\/>',$mode,'plugin_ipaddress_iplist');
    }

    /**
     * Handle matches of the ipaddress syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler){
        preg_match('/\<IPList (.*)\/>/', $match, $content);
		list($start, $end) = explode(' ', trim($content[1]), 2);


		if(!$this->helper->checkIP($start)) { return "Start address $start is invalid."; }
		if(!$this->helper->checkIP($end)) { return "End address $end is invalid.";}

		$data = array();
		$data['start'] = $start;
		$data['end'] = $end;
		$data['records'] = $this->helper->getIPList($start, $end);

		
        return $data;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer &$renderer, $data) {
        if($mode != 'xhtml') return false;

		$renderer->doc .= '<h3>Addresses between '.$data['start'].' and '.$data['end'].'</h3>';
		$renderer->doc .= '<table><tr><th>Address</th><th>Links</th></tr>';
		foreach ($data['records'] as $address => $record) {
			$renderer->doc .= '<tr><td>'.$address.'</td><td>';
			foreach ($record as $row) {
				$renderer->internallink($row['ns'].':'.$row['page']);
				if($row['description']) {
					$renderer->doc .= ' ('.$row['description'].')';
				}
				$renderer->doc .= '<br>';
			}
			$renderer->doc .= '</td></tr>';	
		}
		$renderer->doc .= '</table>';
		
        return true;
    }
}

// vim:ts=4:sw=4:et:
