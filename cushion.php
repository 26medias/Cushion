<?php
	
	class Cushion {
		function Cushion() {
			// Server Configuration
			$this->conf = array(
				"protocol"	=> "https",
				"server"	=> "",
				"port"		=> "6984",
				"user"		=> "",
				"password"	=> ""
			);
			
			// Debug Mode
			$this->debug_mode 	= true;
			
			$this->maps			= array();
			$this->reduces		= array();
		}
		
		
		/*** CORE ***/
		function r($method="GET", $q="", $data=false, $decode=true) {
			//open connection
			$ch = curl_init();
			
			if ($this->conf["user"] != "" && $this->conf["password"] != "") {
				$url = $this->conf["protocol"]."://".$this->conf["user"].":".$this->conf["password"]."@".$this->conf["server"].":".$this->conf["port"];
			} elseif ($this->conf["user"] != "" && $this->conf["password"] == "") {
				$url = $this->conf["protocol"]."://".$this->conf["user"]."@".$this->conf["server"].":".$this->conf["port"];
			} else {
				$url = $this->conf["protocol"]."://".$this->conf["server"].":".$this->conf["port"];
			}
			
			//set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 	false);
			curl_setopt($ch, CURLOPT_URL, 				$url."/".$q);
			if ($data !== false) {
				// if the data is an array, then we encode it as a json string
				if (is_array($data)) {
					$data = json_encode($data);
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, 	$data);
			}
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 	$method);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 	true);
			
			//execute post
			$result = curl_exec($ch);
			
			//close connection
			curl_close($ch);
			
			if ($decode) {
				$result = json_decode($result, true);
			}
			return $result;
		}
		
		/*** UTILITIES ***/
		function debug($label, $data) {
			if (!$this->debug_mode) {
				return false;
			}
			echo "<div style=\"margin-left: 40px;background-color:#fff;\"><u><h3>".$label."</h3></u><pre style=\"border-left:2px solid #000000;margin:10px;padding:4px;\">".print_r($data, true)."</pre></div>";
		}
		function UUID() {
			return sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
				// 32 bits for "time_low"
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				
				// 16 bits for "time_mid"
				mt_rand( 0, 0xffff ),
				
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				mt_rand( 0, 0x0fff ) | 0x4000,
				
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				mt_rand( 0, 0x3fff ) | 0x8000,
				
				// 48 bits for "node"
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
			);
		}
		
		
		/*** SERVER MANAGEMENT ***/
		function server_version($decode=true) {
			$return = $this->r("GET", "", false, $decode);
			
			$this->debug("server_version", $return);
			
			return $return["version"];
		}
		
		/*** DATABASE MANAGEMENT ***/
		function db_list($decode=true) {
			$return = $this->r("GET","_all_dbs", false,$decode);
			$this->debug("db_list", $return);
			return $return;
		}
		function db_create($name, $decode=true) {
			$return = $this->r("PUT", $name, false, $decode);
			
			$this->debug("db_create", $return);
			
			if (isset($return["ok"])) {
				return true;
			}
			return $return;
		}
		function db_drop($name, $decode=true) {
			$return = $this->r("DELETE", $name, false, $decode);
			
			$this->debug("db_drop", $return);
			
			if (isset($return["ok"])) {
				return true;
			}
			return $return;
		}
		
		/*** DOCUMENT MANAGEMENT ***/
		function insert($db, $data, $uuid=false, $decode=true, $whole=false) {
			// generate a UUID if the user doesn't provide one
			if ($uuid === false) {
				$uuid = $this->UUID();
			}
			$return = $this->r("PUT", $db."/".$uuid, $data, $decode);
			
			$this->debug("insert", $return);
			
			return $return;
		}
		function update($db, $uuid, $data, $_rev=false, $decode=true) {
			
			// decoding to be able to check if _rev is specified
			if (!is_array($data)) {
				$data = json_decode($data, true);
			}
			// if _rev is not specified, we add it; Else we don't touch it.
			if (!array_key_exists("_rev", $data)) {
				$data["_rev"] = $_rev;
			}
			
			$return = $this->r("PUT", $db."/".$uuid, $data, $decode);
			
			$this->debug("update", $return);
			
			return $return;
		}
		
		/*** QUERY/MAP/REDUCE/VIEW MANAGEMENT ***/
		function map_create($name, $map) {
			$this->maps[$name] = $map;
			return $map;
		}
		function reduce_create($name, $map) {
			$this->reduces[$name] = $map;
			return $map;
		}
		function view_create($db, $name, $views, $decode=true) {
			$view = array(
				"_id"	=>	"_design/".$name,
				"views"	=> array()
			);
			foreach ($views as $viewName) {
				if (!is_array($view["views"][$viewName])) {
					$view["views"][$viewName] = array();
				}
				
				if (array_key_exists($viewName, $this->maps)) {
					$view["views"][$viewName]["map"] = $this->maps[$viewName];
				}
			}
			
			// save the view
			$return = $this->r("PUT", $db."/_design/".$name, $view, $decode);
			
			$this->debug("view_create", array(
				"view"		=> $view,
				"return"	=> $return
			));
			return $return;
		}
		/*
		// $cushion->query("test","example","foo");
		function query($db, $name, $view, $decode=true) {
			$return = $this->r("GET", $db."/_design/".$name."/_view/".$view, false, $decode);
			
			$this->debug("query", array(
				"view"		=> $view,
				"return"	=> $return
			));
		}*/
		function query($path, $params=false, $decode=true) {
			$pathArray 	= explode(".", $path);
			$db 		= $pathArray[0];
			$name 		= $pathArray[1];
			$view 		= $pathArray[2];
			
			$_params = $params===false?"":"?".http_build_query($params);
			
			$return = $this->r("GET", $db."/_design/".$name."/_view/".$view.$_params, false, $decode);
			
			$this->debug("query", array(
				"view"		=> $view,
				"return"	=> $return
			));
			return $return;
		}
		
	}
?>