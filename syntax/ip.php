<?php
/**
 * DokuWiki Plugin ipaddress (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Grant Emsley <grant@emsley.ca>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_ipaddress_ip extends DokuWiki_Syntax_Plugin {

	var $db = null;
	var $helper = null;
	
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
        return 'normal';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 50;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<IP>.*?</IP>',$mode,'plugin_ipaddress_ip');
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
		// Remove tags and trim content
        preg_match('/\<IP\>(.*)\<\/IP\>/', $match, $content);
		//$match = trim($content[1]);
		
		//$data = explode(' ', $match, 2);
        //return $data;
		
		$data = $this->helper->parseContent(trim($content[1]));
		
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

		// $data = array($valid, $ip, $bin, $mask, $description)
		if($data[0]) {
			// Valid IP address
			
			// print IP
			$renderer->doc .= $renderer->_xmlEntities($data[1]);
			// print mask
			if($data[3]) { $renderer->doc .= '/' . $data[3]; }
			// print hex IP for debugging
			//$renderer->doc .= ' (' . $renderer->_xmlEntities($data[2]) . ')';
			// print description
			if($data[4]) { $renderer->doc .= ' (' . $renderer->_xmlEntities($data[4]).')';}
		} else {
			// Not a valid IP address
			$renderer->doc .= '<b><i>Invalid IP address</i></b> - ' . $data[1];
		}

        return true;
    }
}

// vim:ts=4:sw=4:et:
