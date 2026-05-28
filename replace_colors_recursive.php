<?php

$dir = new RecursiveDirectoryIterator('resources/views/owner');
$ite = new RecursiveIteratorIterator($dir);
foreach($ite as $file) {
    if($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        $original = $content;
        
        // Remove local --primary-gradient
        $content = preg_replace('/\s*--primary-gradient:[^;]+;/i', '', $content);
        
        // Replace slate dark text colors and primary colors with var(--text-main)
        $content = preg_replace('/#(0f172a|1e293b|334155|475569|1e3a8a|3b82f6|2563eb|4f46e5|0ea5e9|0284c7|ec4899|be185d|6366f1|a855f7|f59e0b|d97706|8b5cf6|6f42c1)/i', 'var(--text-main)', $content);
        
        // Replace slate muted text colors with var(--text-muted)
        $content = preg_replace('/#(64748b|94a3b8|cbd5e1)/i', 'var(--text-muted)', $content);
        
        // inline linear-gradient definitions
        $content = preg_replace('/linear-gradient\([^)]+\)/i', 'var(--primary-gradient)', $content);
        
        // Tailwind text classes
        $content = preg_replace('/text-(slate|gray|blue|indigo|purple|pink|yellow|orange)-(700|800|900)/i', 'text-dark', $content);
        $content = preg_replace('/text-(slate|gray|blue|indigo|purple|pink|yellow|orange)-(400|500|600)/i', 'text-muted', $content);
        
        if ($original !== $content) {
            file_put_contents($path, $content);
            echo $path . " updated.\n";
        }
    }
}
