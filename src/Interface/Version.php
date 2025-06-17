<?php 

namespace Src\Interface;

interface Version
{
    public function setup(string $url, ?string $name);

    public function check();

    public function process(string $fileUrl);
}