<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use RecursiveIteratorIterator;

class VendorCleanup extends Command
{
    protected $signature = 'vendor:cleanup {--o : Verbose Output} {--dry : Runs in dry mode without deleting files.}';
    protected $description = 'Cleans up useless files from vendor folder.';

    // Default patterns for common files
    protected $patterns = [
        '.git',
        '.github',
        'test',
        'tests',
        'travis',
        'demo',
        'demos',
        'license',
        'changelog*',
        'contributing*',
        'upgrading*',
        'upgrade*',
        '.idea',
        '.vagrant',
        'readme*',
        '_ide_helper.php',
        '*.md',
        '*.log',
        '*.txt',
        '*.pdf',
        '*.xls',
        '*.doc',
        '*.docx',
        '*.png',
        '*.gif',
        '*.jpg',
        '*.bmp',
        '*.jpeg',
        '*.ico',
        '.php_cs*',
        '.scrutinizer',
        '.gitignore',
        '.gitattributes',
        '.editorconfig',
        'dockerfile',
        'composer.json',
        'composer.lock',
    ];

    // These paths/patterns will NOT be deleted
    protected $excluded = [
        'laravel-mail-preview'
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $patterns = array_diff($this->patterns, $this->excluded);

        $directories = $this->expandTree(base_path('vendor'));

        $isDry = $this->option('dry');
        $isVerbose = $this->option('o');

        foreach ($directories as $directory) {
            foreach ($patterns as $pattern) {

                $casePattern = preg_replace_callback('/([a-z])/i', [$this, 'prepareWord'], $pattern);

                $files = glob($directory . '/' . $casePattern, GLOB_BRACE);

                if (!$files) {
                    continue;
                }

                $files = array_diff($files, $this->excluded);

                foreach ($this->excluded as $excluded) {
                    $key = $this->arrayFind($excluded, $files);

                    if ($key !== false) {
                        echo ('SKIPPED: ' . $files[$key]) . PHP_EOL;
                        unset($files[$key]);
                    }
                }

                foreach ($files as $file) {
                    if (is_dir($file)) {
                        if ($isVerbose) {
                            echo ('DELETING DIR: ' . $file) . PHP_EOL;
                        }

                        if (!$isDry) {
                            $this->delTree($file);
                        }
                    } else {
                        if ($isVerbose) {
                            echo ('DELETING FILE: ' . $file) . PHP_EOL;
                        }

                        if (!$isDry) {
                            @unlink($file);
                        }
                    }
                }
            }
        }

        echo ('Vendor Cleanup Done!') . PHP_EOL;
    }

    /**
     * Recursively traverses the directory tree
     *
     * @param  string $dir
     * @return array
     */
    protected function expandTree($dir)
    {
        $directories = [];
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $directory = $dir . '/' . $file;

            if (is_dir($directory)) {
                $directories[] = $directory;
                $directories = array_merge($directories, $this->expandTree($directory));
            }
        }

        return $directories;
    }

    /**
     * Recursively deletes the directory
     *
     * @param  string $dir
     * @return bool
     */
    protected function delTree($dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            return false;
        }

        $iterator = new RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                @rmdir($filename);
            } else {
                @unlink($filename);
            }
        }

        @rmdir($dir);
    }

    /**
     * Prepare word
     *
     * @param  string $matches
     * @return string
     */
    protected function prepareWord($matches)
    {
        return '[' . strtolower($matches[1]) . strtoupper($matches[1]) . ']';
    }

    protected function arrayFind($needle, array $haystack)
    {
        foreach ($haystack as $key => $value) {
            if (false !== stripos($value, $needle)) {
                return $key;
            }
        }

        return false;
    }
}
