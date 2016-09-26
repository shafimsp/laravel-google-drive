<?php
/**
 * Created by PhpStorm.
 * User: hpw
 * Date: 23-09-2016
 * Time: 16:29
 */

namespace Pixbit\GoogleDrive;

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

    public function where($field, $operator, $value) {
        $operators = $this->fields[$field];
        if(!$operators) return $this;
        if(!array_search($operator, $operators)) return $this;

        if(in_array($field, ['parents', 'parents', 'owners', 'writers', 'readers'])) {
            $filters[] = "'".$value."' in ".$field;
        } else {
            $filters[] = $field." ".$operator." '".$value."'";
        }

        return $this;
    }

    public function folder($folderId) {
        $this->folderId = $folderId;
    }

    public function size($pageSize) {
        $this->pageSize = $pageSize;
    }

    public function token($nextPageToken) {
        $this->pageToken = $nextPageToken;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function get()
    {
        $driveFiles = File::getGoogleDrive()->listFiles($this->folderId, $this->getFilters(), $this->pageSize, $this->pageToken);

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
}