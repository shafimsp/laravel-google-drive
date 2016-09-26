<?php

namespace Pixbit\GoogleDrive;

use Google_Service_Drive_DriveFile;

class File
{
    /** @var Google_Service_Drive_DriveFile */
 	public $driveFile;

    public static function createFromGoogleDriveFile(Google_Service_Drive_DriveFile $driveFile) {
        $file = new static();
        $file->driveFile = $driveFile;
        return $file;
    }

    public static function create(string $name, string $folderId = null) {
        $driveFile = File::getGoogleDrive()->createFolder($name, $folderId);
        return File::createFromGoogleDriveFile($driveFile);
    }

    public static function find($fileId)
    {
        $driveFile = File::getGoogleDrive()->getFile($fileId);
        return File::createFromGoogleDriveFile($driveFile);
    }

    public static function get($folderId = null)
    {
        $builder =  new QueryBuilder();
        $builder->folder($folderId);
        return $builder->get();
    }

    public static function where($field, $operator, $value)
    {
        $builder =  new QueryBuilder();
        $builder->where($field, $operator, $value);
        return $builder;
    }


    public function __construct() {
        $this->driveFile = new Google_Service_Drive_DriveFile();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
        $value = array_get($this->driveFile, $name);
        return $value;
    }


    public static function getGoogleDrive() {
        return GoogleDriveFactory::createForDrive();
    }

}
