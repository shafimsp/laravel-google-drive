<?php

namespace Pixbit\GoogleDrive;

use Carbon\Carbon;
use DateTime;
use Google_Service_Drive;
use Google_Service_Drive_FileList;
use Google_Service_Drive_DriveFile;

class GoogleDrive
{
    /** @var \Google_Service_Drive */
    protected $driveService;

    public function __construct(Google_Service_Drive $driveService)
    {
        $this->driveService = $driveService;
    }

    public function listFiles($folderId = null, array $filters = [], $pageSize = null, $pageToken = null) {
        $folderId = empty($folderId) ? "root" : $folderId;

        $folderFilter = "'" . $folderId . "' in parents";

        $filters = !is_array($filters) ? [] : $filters;
        $filters[] = $folderFilter;
        $filters = implode(" and ", $filters);

        $parameters = [
            'q' => $filters,
            'spaces' => 'drive',
            'fields' => 'files,kind,nextPageToken',
        ];

        if(is_numeric($pageSize) && intval($pageSize) > 0) {
			$parameters['pageSize'] = $pageSize;

            if (!empty($pageToken)) {
                $parameters['pageToken'] = $pageToken;
            }

            return $this
                ->driveService
                ->files
                ->listFiles($parameters);
           
        } else {
             $files = [];

            do {
                $fl = $this
                    ->driveService
                    ->files
                    ->listFiles($parameters);

                $files = array_merge($files, $fl->getFiles());
                $pageToken = $fl->getNextPageToken();
            } while (!empty($pageToken));

            return $files;
        }
    }

    public function getFile($fileId = null) {
        $fileId = empty($fileId) ? "root" : $fileId;

        $parameters = [
            'fields' => 'appProperties,createdTime,description,explicitlyTrashed,fileExtension,folderColorRgb,fullFileExtension,headRevisionId,iconLink,id,isAppAuthorized,kind,lastModifyingUser,md5Checksum,mimeType,modifiedByMeTime,modifiedTime,name,originalFilename,ownedByMe,owners,parents,permissions,properties,quotaBytesUsed,shared,sharedWithMeTime,sharingUser,size,spaces,starred,thumbnailLink,trashed,version,viewedByMe,viewedByMeTime,viewersCanCopyContent,webContentLink,webViewLink,writersCanShare',
        ];

        return $this
            ->driveService
            ->files
            ->get($fileId, $parameters);
    }

    /**
     * create directory
     * @param string $name
     * @param mixed $folderId (parent or array parents)
     * @return mixed
     */
    public function createFolder($name, $folderId = null) {
        if(empty($name)) return;

        $folderIds = empty($folderId) ? array('root') : is_array($folderId) ? $folderId : array($folderId);

        $parameters = [
            'fields' => 'appProperties,createdTime,description,explicitlyTrashed,fileExtension,folderColorRgb,fullFileExtension,headRevisionId,iconLink,id,isAppAuthorized,kind,lastModifyingUser,md5Checksum,mimeType,modifiedByMeTime,modifiedTime,name,originalFilename,ownedByMe,owners,parents,permissions,properties,quotaBytesUsed,shared,sharedWithMeTime,sharingUser,size,spaces,starred,thumbnailLink,trashed,version,viewedByMe,viewedByMeTime,viewersCanCopyContent,webContentLink,webViewLink,writersCanShare',
        ];

        $folder = new Google_Service_Drive_DriveFile([
            'name' => trim($name),
            'parents' => $folderIds,
            'mimeType' => 'application/vnd.google-apps.folder']);

        return $this
            ->driveService
            ->files
            ->create($folder, $parameters);
    }

    /**
     * Rename file or directory
     * @param string $fileId
     * @param string $name
     * @return mixed
     */
    public function renameFile($fileId, $name) {
        if(empty($fileId)) return;
        if(empty($name)) return;

        $parameters = [
            'fields' => 'appProperties,createdTime,description,explicitlyTrashed,fileExtension,folderColorRgb,fullFileExtension,headRevisionId,iconLink,id,isAppAuthorized,kind,lastModifyingUser,md5Checksum,mimeType,modifiedByMeTime,modifiedTime,name,originalFilename,ownedByMe,owners,parents,permissions,properties,quotaBytesUsed,shared,sharedWithMeTime,sharingUser,size,spaces,starred,thumbnailLink,trashed,version,viewedByMe,viewedByMeTime,viewersCanCopyContent,webContentLink,webViewLink,writersCanShare',
        ];

        $file = new Google_Service_Drive_DriveFile([
            'name' => trim($name)
        ]);

        return $this
            ->driveService
            ->files
            ->update($fileId, $file, $parameters);
    }

    /**
     * Move file or directory
     * @param string $fileId
     * @param string $toFolderId
     * @return mixed
     */
    public function moveFile($fileId, $toFolderId) {
        $emptyFileMetadata = new Google_Service_Drive_DriveFile();

        // Retrieve the existing parents to remove
        $file = $this->driveService->files->get($fileId, array('fields' => 'parents'));

        $parents = join(',', $file->parents);

        $parameters = [
            'addParents' => $toFolderId,
            'removeParents' => $parents,
            'fields' => 'appProperties,createdTime,description,explicitlyTrashed,fileExtension,folderColorRgb,fullFileExtension,headRevisionId,iconLink,id,isAppAuthorized,kind,lastModifyingUser,md5Checksum,mimeType,modifiedByMeTime,modifiedTime,name,originalFilename,ownedByMe,owners,parents,permissions,properties,quotaBytesUsed,shared,sharedWithMeTime,sharingUser,size,spaces,starred,thumbnailLink,trashed,version,viewedByMe,viewedByMeTime,viewersCanCopyContent,webContentLink,webViewLink,writersCanShare',
        ];

        // Move the file to the new folder
        $file = $this->driveService->files->update($fileId, $emptyFileMetadata, $parameters);

        return $file;
    }

    /**
     * Copy file or directory
     * @param string $fileId
     * @param string $name (New name)
     * @return mixed
     */
    public function copyFile($fileId, $name) {
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($name);

        $file = $this->driveService->files->copy($fileId, $file);

        return $file;
    }

    /**
     * Permanently Delete File
     * @param string $fileId
     * @return mixed
     */
    public function permanentDelete($fileId){
        if(empty($fileId)) return FALSE;
        return $this->driveService->files->delete($fileId);
    }


    /**
     * Mark a file a trash
     * @param string $fileId
     * @return mixed
     */
    public function trashFile($fileId=''){
        if(empty($fileId)) return FALSE;
        $file = new Google_Service_Drive_DriveFile();
        $file->setTrashed(true);

        $parameters = [
            'fields' => 'appProperties,createdTime,description,explicitlyTrashed,fileExtension,folderColorRgb,fullFileExtension,headRevisionId,iconLink,id,isAppAuthorized,kind,lastModifyingUser,md5Checksum,mimeType,modifiedByMeTime,modifiedTime,name,originalFilename,ownedByMe,owners,parents,permissions,properties,quotaBytesUsed,shared,sharedWithMeTime,sharingUser,size,spaces,starred,thumbnailLink,trashed,version,viewedByMe,viewedByMeTime,viewersCanCopyContent,webContentLink,webViewLink,writersCanShare',
        ];

        $file = $this->driveService->files->update($fileId, $file, $parameters);

        return $file;
    }


    /**
     * Mark a file a un-Trash
     * @param string $fileId
     * @return mixed
     */
    public function unTrashFile($fileId){
        if(empty($fileId)) return FALSE;
        $file = new Google_Service_Drive_DriveFile();
        $file->setTrashed(false);

        $parameters = [
            'fields' => 'appProperties,createdTime,description,explicitlyTrashed,fileExtension,folderColorRgb,fullFileExtension,headRevisionId,iconLink,id,isAppAuthorized,kind,lastModifyingUser,md5Checksum,mimeType,modifiedByMeTime,modifiedTime,name,originalFilename,ownedByMe,owners,parents,permissions,properties,quotaBytesUsed,shared,sharedWithMeTime,sharingUser,size,spaces,starred,thumbnailLink,trashed,version,viewedByMe,viewedByMeTime,viewersCanCopyContent,webContentLink,webViewLink,writersCanShare',
        ];

        $file = $this->objService->files->update($fileId, $file, $parameters);

        return $file;
    }

    public function getService()//: Google_Service_Drive
    {
        return $this->driveService;
    }

}
