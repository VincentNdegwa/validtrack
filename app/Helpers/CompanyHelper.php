<?php

namespace App\Helpers;

use App\Models\Company;
use App\Models\CompanySetting;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyHelper
{
    public static function getCompany($company = null)
    {
        if ($company) {
            return $company instanceof Company ? $company : Company::find($company);
        }
        
        if (Auth::check()) {
            return Auth::user()->company;
        }
        
        return null;
    }
    
    public static function getCompanySetting($key, $default = null, $company = null)
    {
        $company = self::getCompany($company);
        
        if (!$company) {
            return $default;
        }
        
        return $company->getSetting($key, $default);
    }
    
    public static function getCompanyTimezone($company = null)
    {
        return self::getCompanySetting(CompanySetting::TIMEZONE, 'UTC', $company);
    }
    
    public static function formatCompanyTime($time = null, $format = 'H:i', $company = null)
    {
        $timezone = self::getCompanyTimezone($company);
        
        if (!$time) {
            $time = now();
        }
        
        if (is_string($time)) {
            $time = Carbon::parse($time);
        }
        
        return $time->setTimezone($timezone)->format($format);
    }
    
    public static function formatCompanyDate($date = null, $format = 'Y-m-d', $company = null)
    {
        $timezone = self::getCompanyTimezone($company);
        
        if (!$date) {
            $date = now();
        }
        
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }
        
        return $date->setTimezone($timezone)->format($format);
    }
    
    public static function formatCompanyDateTime($dateTime = null, $format = 'Y-m-d H:i:s', $company = null)
    {
        $timezone = self::getCompanyTimezone($company);
        
        if (!$dateTime) {
            $dateTime = now();
        }
        
        if (is_string($dateTime)) {
            $dateTime = Carbon::parse($dateTime);
        }
        
        return $dateTime->setTimezone($timezone)->format($format);
    }
    
    public static function uploadFile(UploadedFile $file, $directory = 'uploads', $disk = 'public')
    {
        try {
            if (!$file->isValid()) {
                return [
                    'success' => false,
                    'error' => 'Invalid file upload',
                    'message' => $file->getErrorMessage(),
                    'path' => null,
                    'url' => null
                ];
            }
            
            $path = $file->store($directory, $disk);
            
            if (!$path) {
                return [
                    'success' => false,
                    'error' => 'Storage error',
                    'message' => 'Failed to store the file',
                    'path' => null,
                    'url' => null
                ];
            }
            
            return [
                'success' => true,
                'error' => null,
                'message' => 'File uploaded successfully',
                'path' => $path,
                'url' => self::getUploadedFileUrl($path, $disk),
                'filename' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => get_class($e),
                'message' => $e->getMessage(),
                'path' => null,
                'url' => null
            ];
        }
    }
    
    public static function getUploadedFileUrl($path, $disk = 'public')
    {
        if (empty($path)) {
            return null;
        }
        
        if ($disk === 'public') {
            return Storage::url($path);
        }
        
        return Storage::disk($disk)->path($path);
    }

    public static function checkFileExists($path, $disk = 'public')
    {
        if (empty($path)) {
            return false;
        }
        
        return Storage::disk($disk)->exists($path);
    }
    
    public static function deleteUploadedFile($path, $disk = 'public')
    {
        try {
            if (empty($path)) {
                return [
                    'success' => false,
                    'error' => 'Invalid path',
                    'message' => 'The file path is empty',
                    'path' => $path
                ];
            }
            
            if (!Storage::disk($disk)->exists($path)) {
                return [
                    'success' => false,
                    'error' => 'File not found',
                    'message' => 'The file does not exist',
                    'path' => $path
                ];
            }
            
            $deleted = Storage::disk($disk)->delete($path);
            
            if (!$deleted) {
                return [
                    'success' => false,
                    'error' => 'Delete failed',
                    'message' => 'Failed to delete the file',
                    'path' => $path
                ];
            }
            
            return [
                'success' => true,
                'error' => null,
                'message' => 'File deleted successfully',
                'path' => $path
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => get_class($e),
                'message' => $e->getMessage(),
                'path' => $path
            ];
        }
    }
}
