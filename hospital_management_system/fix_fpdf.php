<?php
$f = 'C:\xampp\htdocs\hospital_management_system\includes\fpdf\fpdf.php';
$c = file_get_contents($f);
$c = preg_replace('/EGP ([a-zA-Z_\x7f-\xff])/', '$$$1', $c);
file_put_contents($f, $c);
echo "Fixed FPDF";
