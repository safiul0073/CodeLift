<?php 

namespace Src\Interface;

interface Version
{
    public function setApiUrl(string $url);

    public function setApplicationName(string $name);
    
    public function check();

    public function process(string $fileUrl);
}