<?php
require_once 'bootstrap.php';
$party_id = $_GET['party_id'];
Tankiyo::leaveParty($basic['id'],$party_id);
header('Location: '. AppInfo::getUrl());
exit();