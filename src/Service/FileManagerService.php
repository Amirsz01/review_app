<?php


namespace App\Service;


use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Symfony\Component\HttpFoundation\File\File;

class FileManagerService
{
    private Client $client;
    private DropboxAdapter $adapter;
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->client = new Client();
        $this->adapter = new DropboxAdapter($this->client);
        $this->filesystem = new Filesystem($this->adapter, ['case_sensitive' => false]);
    }


    public function imageUpload(File $file): string
    {
        $location = uniqid() . '.jpeg';
        try {
            $this->filesystem->write($location, $file->getContent());
        } catch (FilesystemException $e) {
            dd($e);
        }
        return $location;
    }

    public function createShareLink(string $location): string
    {
        return $this->client->createSharedLinkWithSettings($location)["url"]."&raw=1";
    }

    public function imageRemove(string $location) : bool
    {
        try
        {
            $this->filesystem->delete($location);
        }
        catch (FilesystemException $exception)
        {
            return false;
        }
        return true;
    }
}
