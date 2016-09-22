<?php

namespace Pixbit\GoogleDrive;

use Carbon\Carbon;
use DateTime;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class GoogleDrive
{
    /** @var \Google_Service_Drive */
    protected $driveService;

    /** @var string */
    protected $driveId;

    public function __construct(Google_Service_Drive $driveService, $driveId)
    {
        $this->driveService = $driveService;

        $this->driveId = $driveId;
    }

    public function getDriveId()
    {
        return $this->driveId;
    }

    public function createDirectory($strName = null, $folderId = null)
    {
        if ($folderId == null) {
            $folderId = 'root';
        }
        if ($strName != null) {
            $ObjFileMetadata = new Google_Service_Drive_DriveFile([
                'name' => trim($strName),
                'parents' => array($folderId),
                'mimeType' => 'application/vnd.google-apps.folder']);
            $objFile = $this->driveService->files->create($ObjFileMetadata, ['fields' => 'id']);
        }
        return $objFile;
    }


    public function listFiles(
        $drive_id = null,
        $strPageToken = null,
        array $queryParameters = []
    ) //: String
    {
        if ($drive_id == null) {
            $drive_id = 'root';
        }

        $parameters = [
            'q' => "trashed = false and '" . $drive_id . "' in parents",
            'spaces' => 'drive',
            'pageToken' => $strPageToken,
        ];

        $parameters = array_merge($parameters, $queryParameters);

        return $this
            ->driveService
            ->files
            ->listFiles($parameters)
            ->getFiles();
    }

    public function getService()//: Google_Service_Drive
    {
        return $this->driveService;
    }

    public function renameFileDirectory($fileId = null, $fileNewName = null)
    {
        if ($fileId == null && $fileNewName == null) {
            $objFile = new Google_Service_Drive_DriveFile();
            $objFile->setName($fileNewName);

            $objUpdatedFile = $this->objService->files->update($fileId, $objFile, array(
                'fields' => 'name'
            ));
            return $objUpdatedFile;
        }
    }
}
