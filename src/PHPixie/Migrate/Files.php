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
        $path = $this->rootPath->path($path.'/');
        
        if(!file_exists($path) || !is_dir($path)) {
            throw new Exception("Path $path is not a directory");
        }
        
        $files = array();
        foreach(scandir($path) as $file) {
            if($file{0} == '.') {
                continue;
            }
            
            $fileName = strtolower(pathinfo($file, PATHINFO_FILENAME));
            if(isset($files[$fileName])) {
                throw new Exception("Multiple files with name $fileName present");
            }
            
            $filePath = $path.$file;
            if(!is_file($filePath)) {
                throw new Exception("Path $filePath is not a regular file");
            }
            
            $files[$fileName] = $filePath;
        }
        
        return $files;
    }
}