<?php

include_once('Ops/crypt.class');
$crypt = new OpsCrypt();

$mysql_host = 'localhost';
$mysql_db = 'parsh';
$mysql_user = 'parshrwuser';
$mysql_password = $crypt->get_password('parsh_db_rw');

include_once('Parsh/includes/main.inc');
?>
