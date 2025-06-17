<?php

namespace Src\Interface;

interface Version
{
    public function check();

    public function process(string $fileUrl);
}