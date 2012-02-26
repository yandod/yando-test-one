<?php
require_once 'bootstrap.php';
$date = $_GET['date'];
$party_id = Tankiyo::createParty($date);
Tankiyo::joinParty($basic['id'], $basic['name'], $party_id);
header('Location: '. AppInfo::getUrl());
exit();