<?php
if(!session_id()) session_start();

$fp = new ei8XmlrpcFloodgatePage();
$fp->handle();
exit;

?>