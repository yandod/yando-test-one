<?php
require_once 'bootstrap.php';
$party_id = $_GET['party_id'];
Tankiyo::joinParty($basic['id'], $basic['name'], $party_id);
header('Location: '. AppInfo::getUrl());
exit();
