<?php
	
	class Cushion {
		function Cushion() {
			$this->conf = array(
				"protocol"	=> "https",
				"server"	=> "",
				"port"		=> "6984",
				"user"		=> "",
				"password"	=> ""
			);
			$this->debug_mode = true;
		}
		
		function r($method="GET", $q="", $decode=true) {
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
		
		function debug($var, $val) {
			if (!$this->debug_mode) {
				return false;
			}
			echo "<div style=\"margin-left: 40px;background-color:#fff;\"><u><h3>".$label."</h3></u><pre style=\"border-left:2px solid #000000;margin:10px;padding:4px;\">".print_r($data, true)."</pre></div>";
		}
		
		function db_list($decode=true) {
			$return = $this->r("GET","_all_dbs",$decode);
			debug("db_list", $return);
			return $return;
		}
		function db_create($name, $decode=true) {
			$return = $this->r("PUT", $name, $decode);
			debug("db_create", $return);
			return $return;
		}
		function db_drop($name, $decode=true) {
			$return = $this->r("DELETE", $name, $decode);
			debug("db_drop", $return);
			return $return;
		}
	}
?>