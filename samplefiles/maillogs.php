<?php

use MVC4PHP\SQLTable;

$name = "maillogs";
$fields = ["cod:key", "crtdt:datetime", "expdt:datetime", "request"];
$pk = "cod";
return new SQLTable($name, $fields, $pk);
