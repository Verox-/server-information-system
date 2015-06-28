<?php

$redis = new Redis();
$redis->connect('localhost');

$redis->hmset('testing', array("blood"=>"bath"));
 ?>
