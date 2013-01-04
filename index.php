<?php
	error_reporting(E_ALL ^ E_NOTICE);
	require_once("cushion.php");
	
	$cushion 					= new Cushion();
	$cushion->conf["server"] 	= "twentysixmedias.iriscouch.com";
	$cushion->conf["port"] 		= "6984";
	$cushion->debug_mode		= false;
	
	echo "CouchDB version: ".$cushion->server_version()."<hr />";
	
	// create table "users"
	$cushion->db_create("users");
	echo "Tables: ".print_r($cushion->db_list(), true)."<hr />";
	
	// insert data
	$insertReturn = $cushion->insert("users", array(
		"timestamp"	=> time(),
		"name"	=> "Lex3".time(),
		"email"	=> "lex3".time().".onhym@gmail.com",
		"pass"	=> "123456".time()
	));
	
	echo "Inserted ID: ".$insertReturn["id"]."<hr />";
	
	/*
	// check for data
	$cushion->map_create("list", 	"function(doc){emit(doc._id, doc);}");
	$cushion->map_create("byname", 	"function(doc){emit(doc.name, doc);}");
	$cushion->map_create("bytime", 	"function(doc){emit(doc.timestamp, doc);}");
	
	// list users
	$view = $cushion->view_create("users", "users", array("list","byname","bytime"));
	echo "view: <pre>".print_r($view, true)."</pre><hr />";
	*/
	
	// calling the view
	$users1 = $cushion->query("users.users.list");
	$users2 = $cushion->query("users.users.byname");
	$users3 = $cushion->query("users.users.bytime");
	$recent = $cushion->query("users.users.bytime", array("limit"=>1,"descending"=>"true"));
	
	echo "users1: <pre>".print_r($users1, true)."</pre><hr />";
	echo "users2: <pre>".print_r($users2, true)."</pre><hr />";
	echo "users3: <pre>".print_r($users3, true)."</pre><hr />";
	echo "recent: <pre>".print_r($recent, true)."</pre><hr />";
?>