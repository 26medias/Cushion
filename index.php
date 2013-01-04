<?php
	error_reporting(E_ALL ^ E_NOTICE);
	require_once("cushion.php");
	
	$cushion 					= new Cushion();
	$cushion->conf["server"] 	= "twentysixmedias.iriscouch.com";
	$cushion->conf["port"] 		= "6984";
	echo "CouchDB version: ".$cushion->server_version()."<hr />";
	//$cushion->db_create("test2");
	//$cushion->db_list();
	
	$insertReturn = $cushion->insert("test", array(
		"timestamp"	=> time(),
		"name"	=> "Lex",
		"email"	=> "lex.onhym@gmail.com",
		"sites"	=> array(
			"htp://www.facebook.com",
			"htp://www.google.com",
			"htp://www.amazon.com"
		)
	));
	
	echo "Inserted ID: ".$insertReturn["id"]."<hr />";
	
	/*
	// creating a new view
	$cushion->map_create("foo", "function(doc){emit(doc._id, doc);}");
	$cushion->map_create("foo2", "function(doc){emit(doc._id, doc.email);}");
	
	$cushion->view_create("test", "example", array("foo","foo2"));
	*/
	
	// calling the view
	$cushion->query("test.example.foo");
?>