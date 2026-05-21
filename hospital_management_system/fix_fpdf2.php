<?php
$f = 'C:\xampp\htdocs\hospital_management_system\includes\fpdf\fpdf.php';
$c = file_get_contents($f);
$c = str_replace('$$' . 'this', '$' . 'this', $c);
file_put_contents($f, $c);
echo "Fixed FPDF double-dollar variables";
