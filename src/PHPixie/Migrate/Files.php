<?php

namespace PHPixie\Migrate;

class Files
{
    protected $rootPath;
    
    public function __construct($rootPath)
    {
        $this->rootPath = $rootPath;
    }
    
    public function getFileMap($path)
    {
        $path = $this->rootPath->path($path);
        return $this->getRecursiveFileMap(rtrim($path, '\\/'));
    }
    
    protected function getRecursiveFileMap($path)
    {
        if(!file_exists($path) || !is_dir($path)) {
            throw new Exception("Path $path is not a directory");
        }
        
        $files = array();
        foreach(scandir($path) as $file) {
            if($file{0} == '.') {
                continue;
            }
            
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            if(isset($files[$fileName])) {
                throw new Exception("Multiple files with name $fileName present");
            }
            
            $filePath = $path.'/'.$file;
            
            if(is_dir($filePath)) {
                foreach($this->getRecursiveFileMap($filePath) as $subName => $subPath) {
                    $files[$file.'/'.$subName] = $subPath;
                }
                continue;
            }
            
            if(!is_file($filePath)) {
                throw new Exception("Path $filePath is not a regular file or directory");
            }
            
            $files[$fileName] = $filePath;
        }
        
        return $files;
    }
}