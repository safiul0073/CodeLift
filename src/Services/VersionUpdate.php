<?php 

namespace Src\Services;

use Src\Exceptions\MyException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Src\Interface\Version;

class VersionUpdate extends BaseService implements Version
{

    public function setup(string $url, string $name)
    {
        $this->setBaseUrl($url);

        $this->setApplicationName($name);
    }
    /**
     * Checks for updates by invoking the checkVersion method and retrieves the response.
     *
     * @return mixed The response from the version check.
     */

    public function check(): array|null
    {
        return $this->checkVersion()->getCheckResponse();
    }

    /**
     * Downloads the update zip file, extracts it, updates all files, updates the database, 
     * clears the old files and pushes the data to the system.
     * @param string $fileUrl
     * @return void
     */
    public function process(string $fileUrl)
    {
        $zipPath = storage_path('app/update.zip');
        $extractPath = storage_path('app/update-temp');
        $projectPath = base_path();

        $this->getFileFromUrl($fileUrl, $zipPath);

        $this->createExtractPath($extractPath);

        $this->extractZip($zipPath, $extractPath);

        $this->updateAllFiles($extractPath, $projectPath);

        $this->updateDatabase();

        $this->clearOldFiles($zipPath, $extractPath);

        $this->pushDataToSystem();
    }

    private function ignoredFiles()
    {
        return ['.env', 'vendor', 'storage', 'node_modules', 'composer.lock'];
    }

    private function getFileFromUrl(string $fileUrl, string $zipPath)
    {
        try {
            file_put_contents($zipPath, file_get_contents($fileUrl));
        } catch (\Exception $e) {
            throw new MyException('Failed to download update file.', 500);
        }
    }

    private function createExtractPath(string $extractPath)
    {
        if (File::exists($extractPath)) {
            File::deleteDirectory($extractPath);
        }
        File::makeDirectory($extractPath, 0755, true);
    }

    private function extractZip(string $zipPath, string $extractPath)
    {
        $zip = new \ZipArchive;
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            throw new MyException('Failed to extract zip file.');
        }
    }

    private function updateAllFiles( string $extractPath, string $projectPath)
    {
        $files = File::allFiles($extractPath, true);

        foreach ($files as $file) {
            $relativePath = str_replace($extractPath.'/', '', $file->getPathname());

            foreach ($this->ignoredFiles() as $ignore) {
                if (str_starts_with($relativePath, $ignore)) {
                    continue 2;
                }
            }

            $targetPath = $projectPath.'/'.$relativePath;
            File::ensureDirectoryExists(dirname($targetPath));
            File::copy($file->getPathname(), $targetPath);
        }
    }

    private function updateDatabase()
    {
        Artisan::call('optimize:clear');
        Artisan::call('migrate', ['--force' => true]);
    }

    /**
     * Remove the downloaded zip file and the extracted folder after the update is done.
     *
     * @param string $zipPath
     * @param string $extractPath
     * @return void
     */
    private function clearOldFiles(string $zipPath, string $extractPath)
    {
        File::delete($zipPath);
        File::deleteDirectory($extractPath);
    }


    private function pushDataToSystem()
    {
        $ip_address = request()->ip();
        $url = $this->getBaseUrl();
        try {
            Http::post("$url/api/v1/update-log", [
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'name' => 'Boativus',
                'ip' => $ip_address,
            ]);
        } catch (\Throwable $th) {
            throw new MyException($th->getMessage(), 500);
        }
    }
}
