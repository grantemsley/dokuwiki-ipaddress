<?php
/**
 * DokuWiki Plugin ipaddress (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Grant Emsley <grant@emsley.ca>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_ipaddress_save extends DokuWiki_Action_Plugin {

	var $sqlite = null;
	
	function __construct() {
		$this->helper = plugin_load('helper','ipaddress');
	}

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('IO_WIKIPAGE_WRITE', 'AFTER', $this, 'handle_io_wikipage_write');
   
    }

	
	
    /**
     * When the page is saved, remove any outdated database records and add new ones.
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_io_wikipage_write(Doku_Event &$event, $param) {
		$data = $event->data;
		// $data[0][0] = file path
		// $data[0][1] = content
		// $data[0][2] = whether content will be appended
		// $data[1] = namespace
		// $data[2] = page name
		// $data[3] = revision (false if new page or current version)

        $sql = $this->helper->_getDB();
        if(!$sql) return;
		
		if($data[3]) {
			// revision is set, so this is the old copy being moved to attic
		} else {
			// revision not set, this is the new version

			// if something went wrong saving, stop
			if(!$event->result) { return; }
			
			// delete existing records for this page
			$sql->query('DELETE FROM addresses WHERE ns = ? AND page = ?;', $data[1], $data[2]);
			
			// find IP address tags	
			preg_match_all('/\<IP\>(.*?)\<\/IP\>/', $data[0][1], $matches);
			foreach ($matches[1] as $match) {
				$content = $this->helper->parseContent(trim($match));
				// if it contained a valid IP, save to the database
				if($content[0]) {
					$sql->query('INSERT INTO addresses (ns, page, address, mask, description) VALUES (?,?,?,?,?)', $data[1], $data[2], $content[2], $content[3], $content[4]);
				}
			}
		}
		
    }

}

// vim:ts=4:sw=4:et:
