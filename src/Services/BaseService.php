<?php 

namespace Src\Services;

use Illuminate\Support\Facades\Http;
use Src\Exceptions\MyException;

class BaseService
{
    /**
     * this is api that will be called for getting new project and check for update
     * @var string
     */
    protected $base_url = 'https://script.pxlaxis.com'; 

    /**
     * this is new project zip file that will be downloaded
     * @var 
     */
    protected $application_name = '';

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
     * Sets the base url for the update api
     * @param string $base_url
     */
    protected function setBaseUrl(string $base_url)
    {
        $this->base_url = $base_url;
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

        try {
            $response = Http::get("$url/api/v1/update-available?version=$version&slug=boatibus&ip=$ip_address")->json();
        } catch (\Throwable $th) {
            throw new MyException($th->getMessage(), 500);
        }

        if (empty($response)) {
            throw new MyException('Failed to check for update.', 500);
        }

        if (isset($response['file_path'])){
            $this->setNewProject($response['file_path']);
        }
        
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