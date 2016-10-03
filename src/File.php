<?php

namespace Pixbit\GoogleDrive;

use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class File
{
    /** @var Google_Service_Drive_DriveFile */
 	public $driveFile;

 	public $googleDrive;

    public static function createFromGoogleDriveFile(Google_Service_Drive_DriveFile $driveFile, GoogleDrive $drive = null) {
        $file = new static($drive);
        $file->driveFile = $driveFile;
        return $file;
    }

    public static function create(string $name, string $folderId = null) {
        $builder =  new QueryBuilder();
        $builder->folder($folderId);
        return $builder->create($name);
    }

    public static function find($fileId)
    {
        $builder =  new QueryBuilder();
        return $builder->find($fileId);
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

    public static function folder($folderId)
    {
        $builder =  new QueryBuilder();
        $builder->folder($folderId);
        return $builder;
    }

    public static function drive(GoogleDrive $drive) {
        $builder =  new QueryBuilder();
        $builder->drive($drive);
        return $builder;
    }

    public static function service(Google_Service_Drive $service) {
        $builder =  new QueryBuilder();
        $builder->service($service);
        return $builder;
    }

    public function __construct($drive = null) {
        $this->driveFile = new Google_Service_Drive_DriveFile();
        if(is_null($drive) || !($drive instanceof GoogleDrive))
            $this->googleDrive = File::getGoogleDrive();
        else  $this->googleDrive = $drive;
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
	
	public function isDirectory() {
		if(!isset($this->driveFile)) return FALSE;
		return $this->driveFile->mimeType == 'application/vnd.google-apps.folder';
	}

    public function rename($newName) {
        if(empty($newName) || !isset($this->driveFile)) return;
        $this->driveFile = $this->googleDrive->renameFile($this->driveFile->id, $newName);
    }

    public function move($folderId) {
        if(empty($newName) || !isset($this->driveFile)) return;
        $this->driveFile = $this->googleDrive->moveFile($this->driveFile->id, $folderId);
    }

    public function copy($newName, $folderId) {
        if(!isset($this->driveFile)) return;

        if(!empty($newName)) {
            $this->driveFile = $this->googleDrive->copyFile($this->driveFile->id, $newName);
        }

        if(!empty($folderId)) {
            $this->driveFile = $this->googleDrive->moveFile($this->driveFile->id, $folderId);
        }
    }

    public function delete() {
        if(empty($newName) || !isset($this->driveFile)) return;
        $this->driveFile = $this->googleDrive->permanentDelete($this->driveFile->id);
    }

    public function trash() {
        if(empty($newName) || !isset($this->driveFile)) return;
        $this->driveFile = $this->googleDrive->trashFile($this->driveFile->id);
    }

    public function unTrash() {
        if(empty($newName) || !isset($this->driveFile)) return;
        $this->driveFile = $this->googleDrive->unTrashFile($this->driveFile->id);
    }

    public static function getGoogleDrive() {
        return GoogleDriveFactory::createForDrive();
    }

}
