<?php
include_once __DIR__.'/includes/config.php';

$parser     = new ParserService();
$result     = $parser->parseAccessLog();
print2Console($result);