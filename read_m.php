<?php
$content = file_get_contents('m.txt');
file_put_contents('out1.txt', mb_convert_encoding($content, 'UTF-8', 'UTF-16LE'));
