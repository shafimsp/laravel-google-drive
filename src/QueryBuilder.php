<?php
/**
 * Created by PhpStorm.
 * User: hpw
 * Date: 23-09-2016
 * Time: 16:29
 */

namespace Pixbit\GoogleDrive;

use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_FileList;

class QueryBuilder
{

    private $fields = [
        'name' => ['contains', '=', '!='],
        'fullText' => ['contains'],
        'mimeType' => ['contains', '=', '!='],
        'modifiedTime' => ['<=', '<', '+', '!=', '>', '>='],
        'viewedByMeTime' => ['<=', '<', '+', '!=', '>', '>='],
        'trashed' => ['=', '!='],
        'starred' => ['=', '!='],
        'parents' => ['in'],
        'owners' => ['in'],
        'writers' => ['in'],
        'readers' => ['in'],
        'sharedWithMe' => ['=', '!='],
        'properties' => ['has'],
        'appProperties' => ['has'],
    ];

    private $folderId = null;
    private $filters = [];
    private $pageSize = 0;
    private $pageToken = null;

    private $drive;

    public function drive(GoogleDrive $drive) {
        $this->drive = $drive;
		return $this;
    }

    public function service(Google_Service_Drive $service) {
        $this->drive = new GoogleDrive($service);
		return $this;
    }

    public function folder($folderId) {
        $this->folderId = $folderId;
		return $this;
    }

    public function size($pageSize) {
        $this->pageSize = $pageSize;
		return $this;
    }

    public function token($nextPageToken) {
        $this->pageToken = $nextPageToken;
		return $this;
    }

    public function where($field, $operator, $value) {
        $operators = $this->fields[$field];
        if(!$operators) return $this;
        if(!array_search($operator, $operators)) return $this;

        if(in_array($field, ['parents', 'parents', 'owners', 'writers', 'readers'])) {
            $this->filters[] = "'".$value."' in ".$field;
        } else {
            $this->filters[] = $field." ".$operator." '".$value."'";
        }

        return $this;
    }

    public function create($name) {
        if(!isset($this->drive)) {
            $this->drive = File::getGoogleDrive();
        }

        $driveFile = $this->drive->createFolder($name, $this->folderId);
        return File::createFromGoogleDriveFile($driveFile);
    }

    public function find($fileId)
    {
        if(!isset($this->drive)) {
            $this->drive = File::getGoogleDrive();
        }

        $driveFile = $this->drive->getFile($fileId);
        return File::createFromGoogleDriveFile($driveFile);
    }

    public function get()
    {
        if(!isset($this->drive)) {
            $this->drive = File::getGoogleDrive();
        }

        $driveFiles = $this->drive->listFiles($this->folderId, $this->getFilters(), $this->pageSize, $this->pageToken);

        if($driveFiles instanceof Google_Service_Drive_FileList) {
            $fileList = $driveFiles;
            $driveFiles = collect($fileList->getFiles())
                ->map(function (Google_Service_Drive_DriveFile $file) {
                    return File::createFromGoogleDriveFile($file);
                })
                ->values();
            $fileList->setFiles($driveFiles);
            return $fileList;
        } else {
            return collect($driveFiles)
                ->map(function (Google_Service_Drive_DriveFile $file) {
                    return File::createFromGoogleDriveFile($file);
                })
                ->values();
        }
    }

    private function getFilters()
    {
        return $this->filters;
    }
}