<?php 

namespace Src\Interface;

interface Version
{
    public function setup(string $name, ?string $url);

    public function check();

    public function process(string $fileUrl);
}