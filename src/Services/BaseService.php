<?php 

namespace Src\Services;

use Illuminate\Support\Facades\Http;

class BaseService
{
    /**
     * this is api that will be called for getting new project and check for update
     * @var string
     */
    protected $base_url = ''; 

    /**
     * this is new project zip file that will be downloaded
     * @var 
     */
    protected $new_project = null;

    /**
     * this is new project zip file that will be downloaded
     * @var 
     */
    protected $response_check = null;

    /**
     * this is new project zip file that will be downloaded
     * @var 
     */
    protected $application_name = '';

    /**
     * Returns the base url for the update api
     * @return string
     */
    protected function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * Get the new project
     * @return string|null
     */
    protected function getNewProject()
    {
        return $this->new_project;
    }

    /**
     * Get the new project
     * @return string|null
     */
    protected function getApplicationName()
    {
        return $this->application_name;
    }

    /**
     * Set the new project zip file that will be downloaded
     * @param string $new_project
     * @return void
     */
    protected function setNewProject(string $new_project)
    {
        $this->new_project = $new_project;
    }
    /**
     * Set the new project zip file that will be downloaded
     * @param string $application_name
     * @return void
     */
    protected function setApplicationName(string $application_name)
    {
        $this->application_name = $application_name;
    }

    /**
     * Checks if there is a new version available
     * @return self
     */
    protected function checkVersion()
    {
        $url = $this->getBaseUrl();
        $version = config('app.version');
        $ip_address = request()->ip();

        $response = Http::get("$url/api/v1/update-available?version=$version&name=Boativus&ip=$ip_address")->json();

        $this->setNewProject($response['file_path']);

        $this->response_check = $response;

        return $this;
    }
    /**
     * Get the response from the API check for update available
     * @return array|null
     */

    protected function getCheckResponse()
    {
        return $this->response_check;
    }
}