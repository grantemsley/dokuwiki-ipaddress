<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Grant Emsley <grant@emsley.ca>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_ipaddress extends DokuWiki_Plugin {

    /**
     * @var helper_plugin_sqlite initialized via _getDb()
     */
    protected $db = null;

    /**
     * Simple function to check if the database is ready to use
     *
     * @return bool
     */
    public function ready() {
        return (bool) $this->_getDB();
    }

    /**
     * load the sqlite helper
     *
     * @return helper_plugin_sqlite|false plugin or false if failed
     */
    function _getDB() {
        if($this->db === null) {
            $this->db = plugin_load('helper', 'sqlite');
            if($this->db === null) {
                msg('The data plugin needs the sqlite plugin', -1);
                return false;
            }
            if(!$this->db->init('ipaddress',DOKU_PLUGIN.'ipaddress/db/')) {
                $this->db = null;
                return false;
            }
        }
        return $this->db;
    }
	
	/* Validates that content in the <IP> tag starts with an IP address (possibly with subnet mask) followed by an optional description */
	function parseContent($content) {
		
		list($address, $description) = explode(' ', $content, 2);
		list($ip, $mask) = explode('/', $address,2);
		
		if($this->checkIP($ip)) {
			$bin = $this->IPtoHex($ip);
			return array(true, $ip, $bin, $mask, $description);
		} else {
			return array(false, $content);
		}
	}
	
	function checkIP($ip) {
		return filter_var($ip, FILTER_VALIDATE_IP);
	}
	
	// Convert an IP address to a hex string
	function IPtoHex($ip) {
		return bin2hex(inet_pton($ip));
	}
	
	function HEXtoIP($ip) {
		return inet_ntop(hex2bin($ip));
	}
	
	
	// Returns the IP addresses between start and end
	function getIPList($start, $end) {
		
		$starthex = $this->IPtoHEX($start);
		$endhex = $this->IPtoHEX($end);
		
		$db = $this->_getDB();
		// Sorts so with mask is first, biggest to smallest subnet, then addresses without a mask, then sort by IP
		$res = $db->query('SELECT * from addresses WHERE address BETWEEN ? AND ? ORDER BY mask="", mask ASC, address ASC;', $starthex, $endhex);
		$results = $db->res2arr($res);
		
		// Put the results into an array, grouped by IP address and mask
		$iplist = array();
		foreach($results as $record) {
			$ip = $this->HEXtoIP($record['address']);
			if($record['mask'] > 0) {
				$address = $ip.'/'.$record['mask'];
			} else {
				$address = $ip;
			}
			
			$iplist[$address][] = $record;
		}
		
		return $iplist;
	}
	
}
