<?php
$dir = __DIR__;
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getPathname(), 'vendor') === false && strpos($file->getPathname(), 'libraries') === false) {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        $content = str_replace('InnovAI Medical Center', 'InnovAI Medical Center', $content);
        $content = str_replace('InnovAI Medical Center', 'InnovAI Medical Center', $content);
        $content = str_replace('InnovAI Medical Center', 'InnovAI Medical Center', $content);
        $content = str_replace('InnovAI Medical Center', 'InnovAI Medical Center', $content);
        
        // Currency replacement. Using regex to only match $ when it's followed by a number or PHP variable, wait, $ is used in PHP variables. 
        // We only want to replace $ used in display (like 'EGP ' . number_format or >EGP <?php echo )
        // Let's replace '>EGP ' with '>EGP ' and '>EGP  ' with '>EGP '
        $content = str_replace('>EGP ', '>EGP ', $content);
        $content = str_replace('>EGP  ', '>EGP ', $content);
        $content = preg_replace('/\'\$\' \./', "'EGP ' .", $content);
        $content = preg_replace('/"\$" \./', '"EGP " .', $content);
        $content = str_replace('(EGP)', '(EGP)', $content);
        
        if ($original !== $content) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated: " . $file->getPathname() . "\n";
        }
    }
}
?>