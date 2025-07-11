<?php

use App\Helpers\CompanyHelper;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

if (!function_exists('company_time')) {
    function company_time($time = null, $format = 'H:i', $company = null)
    {
        return CompanyHelper::formatCompanyTime($time, $format, $company);
    }
}

if (!function_exists('company_date')) {
    function company_date($date = null, $format = 'Y-m-d', $company = null)
    {
        return CompanyHelper::formatCompanyDate($date, $format, $company);
    }
}

if (!function_exists('company_datetime')) {
    function company_datetime($dateTime = null, $company = null, $format = 'Y-m-d H:i:s')
    {
        return CompanyHelper::formatCompanyDateTime($dateTime, $format, $company);
    }
}

if (!function_exists('company_setting')) {
    function company_setting($key, $default = null, $company = null)
    {
        return CompanyHelper::getCompanySetting($key, $default, $company);
    }
}

if (!function_exists('upload_file')) {
    function upload_file(UploadedFile $file, $directory = 'uploads', $disk = 'public')
    {
        return CompanyHelper::uploadFile($file, $directory, $disk);
    }
}

if (!function_exists('get_file_url')) {
    function get_file_url($path, $disk = 'public')
    {
        return CompanyHelper::getUploadedFileUrl($path, $disk);
    }
}

if(!function_exists('check_file_exists')) {
    function check_file_exists($path, $disk = 'public')
    {
        return CompanyHelper::checkFileExists($path, $disk);
    }
}

if (!function_exists('delete_file')) {
    function delete_file($path, $disk = 'public')
    {
        return CompanyHelper::deleteUploadedFile($path, $disk);
    }
}
