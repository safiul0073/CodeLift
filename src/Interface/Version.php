<?php

namespace Src\Interface;

interface Version
{
    public function setup(string $name, ?string $url);

    public function check(?string $url = null);

    public function process(string $fileUrl);
}
