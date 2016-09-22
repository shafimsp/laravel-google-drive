<?php

namespace Pixbit\GoogleDrive;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\GoogleCalendar\GoogleCalendar
 */
class GoogleDriveFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-google-drive';
    }
}
