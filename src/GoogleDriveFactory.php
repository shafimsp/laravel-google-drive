<?php

namespace Pixbit\GoogleDrive;

use Google_Client;
use Google_Service_Drive;

class GoogleDriveFactory
{
    public static function createForDriveId($driveId) //: GoogleDrive
    {
        $config = config('laravel-google-drive');

        $client = new Google_Client();

        $credentials = $client->loadServiceAccountJson(
            $config['client_secret_json'],
            'https://www.googleapis.com/auth/drive'
        );

        $client->setAssertionCredentials($credentials);

        $service = new Google_Service_Drive($client);

        return new GoogleDrive($service, $driveId);
    }
}
