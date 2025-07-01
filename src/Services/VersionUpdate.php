<?php 

namespace Src\Services;

use Src\Exceptions\MyException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Src\Interface\Version;

class VersionUpdate extends BaseService implements Version
{
    protected $trackingFileName = '.auto-update-sync-state.json';

    /**
     * Configures the version update service by setting the application name and optionally the base URL.
     *
     * @param string $name The name of the application as it like lowercase.
     * @param string|null $url The base URL for the update API. If null, the URL is not set.
     */

    public function setup(string $name, ?string $url)
    {
        if (!empty($url)) {
            $this->setBaseUrl($url);
        }
        
        $this->setApplicationName(strtolower($name));
    }
    /**
     * Checks for updates by invoking the checkVersion method and retrieves the response.
     *
     * @return mixed The response from the version check.
     */

    public function check(?string $url = null): array|null
    {
        return $this->checkVersion($url)->getCheckResponse();
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

private function updateAllFiles(string $extractPath, string $projectPath)
{
    $files = File::allFiles($extractPath, true);
    $trackingData = $this->getTrackingFileData(); // previous state

    $newTrackingData = [];

    foreach ($files as $file) {
        $relativePath = str_replace($extractPath . '/', '', $file->getPathname());

        foreach ($this->ignoredFiles() as $ignore) {
            if (str_starts_with($relativePath, $ignore)) {
                continue 2;
            }
        }

        $targetPath = $projectPath . '/' . $relativePath;
        File::ensureDirectoryExists(dirname($targetPath));

        $hash = md5_file($file->getPathname());
        $size = filesize($file->getPathname());

        $previous = $trackingData[$relativePath] ?? null;

        if ($previous && $previous['hash'] === $hash && $previous['size'] === $size) {
            // File is unchanged, skip copying
            $newTrackingData[$relativePath] = $previous;
            continue;
        }

        // New or updated file
        File::copy($file->getPathname(), $targetPath);
        $newTrackingData[$relativePath] = [
            'type' => 'file',
            'path' => $relativePath,
            'size' => $size,
            'hash' => $hash,
        ];
    }

    $this->generateUpdateJsonTrackingFile($newTrackingData);
}

    /**
     * Generates a JSON file with the current state of the update process.
     * This file is used to track the state of the update process.
     * Do not modify this file. If you delete this file a resync will be triggered.
     * @param array $data // ['type' => 'file', 'path' => '/path/to/file', 'size' => 1234, 'hash' => '1234567890']
     * @return void
     */
    private function generateUpdateJsonTrackingFile(array $data)
    {
        $trackingFileName = base_path($this->trackingFileName);

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($trackingFileName));

        $file_state = [
            'description' => 'Do not delete this file. It tracks the state of the update process. Deleting this file will trigger a full resync.',
            'generated_at' => now(),
            'data' => $data
        ];

        File::put($trackingFileName, json_encode($file_state, JSON_PRETTY_PRINT));
    }

    private function getTrackingFileData(): array
    {
        $trackingFileName = base_path($this->trackingFileName);

        if (!File::exists($trackingFileName)) {
            return []; // First time or tracking file deleted
        }

        $file_state = json_decode(File::get($trackingFileName), true);

        return $file_state['data'] ?? [];
    }

    private function updateDatabase()
    {
        // Run composer update
        $this->runComposerUpdate();
        
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
                'slug' => $this->getApplicationName(),
                'ip' => $ip_address,
            ]);
        } catch (\Throwable $th) {
            throw new MyException($th->getMessage(), 500);
        }
    }
}
