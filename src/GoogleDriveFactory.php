<?php

namespace Pixbit\GoogleDrive;

use Illuminate\Support\Facades\File as LFile;
use Google_Client;
use Google_Service_Drive;

class GoogleDriveFactory
{
    public static function createForDrive() //: GoogleDrive
    {
        $config = config('laravel-google-drive');

//        $client = new Google_Client();
//        $client->setAuthConfig($config['client_secret_json']);
//        $client->setScopes([Google_Service_Drive::DRIVE]);
//        $client->useApplicationDefaultCredentials(true);

        $client = new Google_Client();
        $client->setApplicationName(config('laravel-google-drive.app_name'));
        $client->setAuthConfig(config('laravel-google-drive.client_secret_json'));
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setIncludeGrantedScopes(true);
        $client->setScopes([Google_Service_Drive::DRIVE]);

//        $accessTokenFilePath = storage_path('app'.DIRECTORY_SEPARATOR .'google'.DIRECTORY_SEPARATOR) . 1 . ".json";
        $accessTokenFilePath = config('laravel-google-drive.master_credential_json');
        if (!LFile::exists($accessTokenFilePath)) return;

        $accessToken = LFile::get($accessTokenFilePath);
        $client->setAccessToken($accessToken);
        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $refreshToken = $client->getRefreshToken();
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $accessToken = $client->getAccessToken();
            $accessToken['refresh_token'] = $refreshToken;
            LFile::put($accessTokenFilePath, json_encode($accessToken));
        }

        $service = new Google_Service_Drive($client);

        return new GoogleDrive($service);
    }
}
