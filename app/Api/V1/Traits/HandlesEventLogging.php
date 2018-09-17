<?php

namespace App\Api\V1\Traits;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Api\V1\Controllers\EventController;
use Exception;

trait HandlesEventLogging
{
    public static function bootHandlesEventLogging()
    {
        static::created(function ($model) {
            $action =  'created';
            self::saveEvent($model, $action);
        });

        static::updated(function ($model) {
            $action =  'updated';
            self::saveEvent($model, $action);
        });

        static::deleted(function ($model) {
            $action =  'deleted';
            self::saveEvent($model, $action);
        });
    }
    protected static function saveEvent($model, $action)
    {
        $modelName = self::getModelName($model);
        $event = self::getEvent($modelName, $action);
        $event_enum_id = self::getEventEnumId();
        $information = self::getEventInformation($model, $modelName, $action);
        $thread = get_class($model);

        EventController::save($event, $event_enum_id, $information, $thread);
    }
    protected static function getModelName($model)
    {
        //dd((new \ReflectionClass($model))->getShortName());
        return str_replace('_', ' ', snake_case(class_basename($model)));
    }
    protected static function getEvent($modelName, $action)
    {
        return $modelName . ' was ' . $action;
    }
    protected static function getEventEnumId()
    {
        return 2;
    }
    protected static function getEventInformation($model, $modelName, $action)
    {
        if ($user = self::getUser()) {
            return $user->first_name . ' ' . $user->last_name . ' ('. $user->email .') with ID: ' . $user->id .', '. $action . ' a ' . $modelName . ($model->id?' with id '.$model->id:'');
        } else {
            return $modelName . ($model->id?' with id '.$model->id:'') . ' was ' . $action;
        }
    }
    protected static function getUser()
    {
        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            //couldn't authenticate user
            return false;
        }
    }
}
