<?php

namespace Pixbit\GoogleDrive;

use Carbon\Carbon;
use DateTime;
use Google_Service_Drive_DriveFile;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Support\Collection;

class File
{
    /** @var Google_Service_Drive_File */
 	public $googleFile;

    /** @var int */
    protected $driveId;

    public static function createFromGoogleDriveFile(Google_Service_Drive_DriveFile $googleFile, $driveId)
    {
        $event = new static();

        $event->googleFile = $googleFile;

        $event->driveId = $driveId;

        return $event;
    }

    public static function create(array $properties, string $driveId = null)
    {
        $event = new static();

        $event->driveId = static::getGoogleDrive($driveId)->getDriveId();

        foreach ($properties as $name => $value) {
            $event->$name = $value;
        }

        return $event->save();
    }

    public function __construct()
    {
        $this->googleFile = new Google_Service_Drive_DriveFile();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $name = $this->getFieldName($name);

        // if ($name === 'sortDate') {
            // return $this->getSortDate();
        // }

        $value = array_get($this->googleFile, $name);

        // if (in_array($name, ['start.date', 'end.date']) && $value) {
            // $value = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        // }
// 
        // if (in_array($name, ['start.dateTime', 'end.dateTime']) && $value) {
            // $value = Carbon::createFromFormat(DateTime::RFC3339, $value);
        // }

        return $value;
    }

    public function __set($name, $value)
    {
        $name = $this->getFieldName($name);

        // if (in_array($name, ['start.date', 'end.date', 'start.dateTime', 'end.dateTime'])) {
            // $this->setDateProperty($name, $value);
// 
            // return;
        // }

        array_set($this->googleFile, $name, $value);
    }

    public function exists(): bool
    {
        return $this->id != '';
    }


    public function insertFile($strPathToFile=null,$strDescrption=null): Google_Service_Calendar_Event
    {
		// if(file_exists($strPathToFile)){
            $strMimeType = mime_content_type ($strPathToFile);
            if(in_array($strMimeType, $this->arAllowedMimeType)){
                $strFileSize = filesize($strPathToFile);
                if($strFileSize <= 5242880){
                    $objFile = new Google_Service_Drive_DriveFile();
                    $objFile->setTitle(basename($strPathToFile));
                    $objFile->setDescription($strDescrption);
                    $objFile->setMimeType($strMimeType);
                    $strData = file_get_contents('a.jpg');
                    $objReturn = $this->driveService->files->insert($objFile, [
                        'data' => $strData,
                        'mimeType' => $strMimeType,
                        'uploadType' => 'multipart'
                    ]);
                }else{
                }
            }
        // }
		return $objReturn;
    }
	
	
	public function downloadFile($fileId=null){
        if($fileId!=null){
            $objComponent = $this->driveService->files->get($fileId, ['fields' => 'files(webViewLink)']);
            return $objComponent;
        }
    }
	
	public function TrashFile($fileId=null)
    {
    	if($fileId!=null){
    		$objFile = new Google_Service_Drive_DriveFile();
	        $objFile->setTrashed(true);
	
	        $objUpdatedFile = $this->driveService->files->update($strID, $objFile, array(
	            'fields' => 'trashed'
	        ));
	
	        return $objUpdatedFile;
    	}
    }


    
    public function deleteFile($fileId)
    {
        $this->driveService->files->delete($fileId);
    }
	

  
    public static function find($fileId, $driveId = null): File
    {
        $googleDrive = static::getGoogleDrive($driveId);

        $googleEvent = $googleDrive->getFiles($driveId);

        return static::createFromGoogleCalendarEvent($googleEvent, $calendarId);
    }

    public function save(): Event
    {
        $method = $this->exists() ? 'updateEvent' : 'insertEvent';

        $googleCalendar = $this->getGoogleCalendar($this->calendarId);

        $googleEvent = $googleCalendar->$method($this);

        return static::createFromGoogleCalendarEvent($googleEvent, $googleCalendar->getCalendarId());
    }


    protected static function getGoogleDrive($driveId = null)
    {
        $driveId = $driveId ?? config('laravel-google-calendar.drive_id');

        return GoogleDriveFactory::createForDriveId($driveId);
    }

    /**
     * @param string         $name
     * @param \Carbon\Carbon $date
     */
     
    /*
    protected function setDateProperty(string $name, Carbon $date)
        {
            $eventDateTime = new Google_Service_Calendar_EventDateTime();
    
            if (in_array($name, ['start.date', 'end.date'])) {
                $eventDateTime->setDate($date->format('Y-m-d'));
                $eventDateTime->setTimezone($date->getTimezone());
            }
    
            if (in_array($name, ['start.dateTime', 'end.dateTime'])) {
                $eventDateTime->setDateTime($date->format(DateTime::RFC3339));
                $eventDateTime->setTimezone($date->getTimezone());
            }
    
            if (starts_with($name, 'start')) {
                $this->googleEvent->setStart($eventDateTime);
            }
    
            if (starts_with($name, 'end')) {
                $this->googleEvent->setEnd($eventDateTime);
            }
        }
	 */
    

    protected function getFieldName(string $name): string
    {
        return [
            // 'name' => 'summary',
            // 'description' => 'description',
            // 'startDate' => 'start.date',
            // 'endDate' => 'end.date',
            // 'startDateTime' => 'start.dateTime',
            // 'endDateTime' => 'end.dateTime',
        ][$name] ?? $name;
    }

    // public function getSortDate(): string
    // {
        // if ($this->startDate) {
            // return $this->startDate;
        // }
// 
        // if ($this->startDateTime) {
            // return $this->startDateTime;
        // }
// 
        // return '';
    // }
}
