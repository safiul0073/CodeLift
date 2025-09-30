<?php

namespace Src\Controller;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Src\Interface\Version;

class UpdateController extends Controller
{
    private $version;

    public function __construct(Version $version)
    {
        $version->setup(env('APP_NAME'), env('UPDATE_API_URL'));

        $this->version = $version;
    }

    public function index()
    {
        $updater = $this->version->check();

        return $updater;
    }

    public function updateFiles(Request $request)
    {
        $this->version->process($request->file_url);
    }
}
