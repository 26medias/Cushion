<?php
	error_reporting(E_ALL ^ E_NOTICE);
	require_once("cushion.php");
	
	$cushion 					= new Cushion();
	$cushion->conf["server"] 	= "twentysixmedias.iriscouch.com";
	$cushion->conf["port"] 		= "6984";
	echo "CouchDB version: ".$cushion->server_version()."<hr />";
	//$cushion->db_create("test2");
	//$cushion->db_list();
	
	echo "<hr />";
	
	$insertReturn = $cushion->insert("test", array(
		"name"	=> "Lex",
		"email"	=> "lex.onhym@gmail.com",
		"sites"	=> array(
			"htp://www.facebook.com",
			"htp://www.google.com",
			"htp://www.amazon.com"
		)
	));
	echo "Inserted ID: ".$insertReturn["id"]."<hr />";
	
?>