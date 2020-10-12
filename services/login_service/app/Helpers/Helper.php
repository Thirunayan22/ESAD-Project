<?php

/**
 * Page Title : Helper.
 *
 * Filename : Helper.php
 *
 * Description: Helper functions to user across the project
 *
 * @package
 * @category Business.
 * @version Release: 1.0.0
 * @since File Creation Date: 31/09/2020.
 */
use App\Models\UserActivity as LogActivityModel;
use App\Services\Auth\RolesService;

if (!function_exists('addToLog')) {

    /**
     * @author Nuwan Ishara.
     * @author Function Creation Date: 31/09/2020.
     *
     * Save activity
     * @category Helper functions.
     *
     * @param string $activity Input - Activity Description
     * @param integer $status Input - Activity status [default - 0 fail, 1 - success]
     * @param integer $imi Input - IMEI number
     * @param string $devName Input - Device name
     * @param string $latitude Input - location
     * @param string $longitude Input - location
     * @var
     *
     * @return -
     *
     * @throws Exception
     *  No data found
     *
     * @uses
     *
     * @version 1.0.0
     *
     *
     */
    function addToLog($activity, $status = 0, $imi = 0, $devName = 'default',
            $latitude = '00', $longitude = '00')
    {
        $log = new LogActivityModel;
        $log->uidx = auth()->check() ? auth()->user()->id : null;
        $log->user_name = auth()->check() ? auth()->user()->user_name : null;
        $log->url = Request::fullUrl();
        $log->activity = $activity;

        $log->status = $status;

        $controllerAndAction = explode('@', Route::getCurrentRoute()->getActionName());

        $log->controller_name = $controllerAndAction[0];
        $log->action_name = $controllerAndAction[1];
        $log->parameters = Request::url();

        $log->ip_addr = Request::ip();
        $log->date_time = now();
        $log->time_diff = 'sample time diff';
        $log->imi = $imi;
        $log->dev_name = $devName;
        $log->ua_browser = Request::header('user-agent');
        $log->latitude = $latitude;
        $log->longitude = $longitude;
        $log->save();
    }

}

if (!function_exists('getGlobalSettingByName')) {

    /**
     * @author Nuwan Ishara.
     * @author Function Creation Date: 31/09/2020.
     *
     * Save activity
     * @category Helper functions.
     *
     * @param string $settingName Input - Name of the setting
     *
     * @var
     *
     * @return string $settingValue Output setting value
     *
     * @throws Exception
     *  No data found
     *
     * @uses
     *
     * @version 1.0.0
     *
     *
     */
    function getGlobalSettingByName($settingName)
    {
        switch ($settingName) {

            case 'DEFAULT_ITEMS_PER_PAGE' :
                $settingValue = 10;
                break;
            default:
                $settingValue = 10;
                break;
        }
//        $settingValue = false;
//        $settingInst = new SettingsService();
//        $settingData = $settingInst->viewSetting($settingName);
//        if (isset($settingData->getData()->data[0]->sys_setting_value)) {
//            $settingValue = $settingData->getData()->data[0]->sys_setting_value;
//        }
//
//
        return $settingValue;
    }

}

if (!function_exists('getCurrencySymbol')) {

    /**
     * @author Nuwan Ishara.
     * @author Function Creation Date: 29/04/2019.
     *
     *
     * get currency symbol by name
     * @category Helper functions.
     *
     * @param string $currencyName Input - Name of the currency
     *
     * @var
     *
     * @return string $settingValue Output setting value
     *
     * @throws Exception
     *
     *
     * @uses
     *
     * @version 1.0.0
     *
     *
     */
//    function getCurrencySymbol($currencyName)
//    {
//        $settingValue = false;
//        $currencyData = new CurrencyService();
//
//        $settingData = $currencyData->viewCurrencyByName($currencyName);
//        if (isset($settingData->getData()->data[0]->cur_symbol)) {
//            $settingValue = $settingData->getData()->data[0]->cur_symbol;
//        }
//        return $settingValue;
//    }
}



if (!function_exists('getStatusCodes')) {

    /**
     * @author Nuwan Ishara.
     * @author Function Creation Date: 31/09/2020.
     *
     *
     * get status codes
     * @category Helper functions.
     *
     * @param string $statusString Input - Name of the status
     *
     * @var
     *
     * @return string $settingValue Output status code
     *
     * @throws
     *
     *
     * @uses
     *
     * @version 1.0.0
     *
     *
     */
    function getStatusCodes($statusString)
    {
        switch ($statusString) {

            case 'VALIDATION_ERROR' :
                $settingValue = 400;
                break;
            case 'EXCEPTION' :
                $settingValue = 400;
                break;
            case 'SUCCESS' :
                $settingValue = 200;
                break;
            case 'UNAUTHORIZED' :
                $settingValue = 401;
                break;
            case 'AUTH_ERROR' :
                $settingValue = 403;
                break;
            default:
                $settingValue = 400;
                break;
        }
        return $settingValue;
    }

}

if (!function_exists('roleNames')) {

    /**
     * @author Nuwan Ishara.
     * @author Function Creation Date: 31/09/2020.
     *
     *
     * get status codes
     * @category Helper functions.
     *
     * @param string $statusString Input - Name of the status
     *
     * @var
     *
     * @return string $settingValue Output status code
     *
     * @throws
     *
     *
     * @uses
     *
     * @version 1.0.0
     *
     *
     */
    function roleNames($roleName)
    {
        switch ($roleName) {

            case 'ADMIN' :
                $dbRoleName = 'admin';
                break;
            case 'ADMIN_ASSIST' :
                $dbRoleName = 'admin_assist';
                break;
            case 'SELLER' :
                $dbRoleName = 'seller';
                break;
            case 'BUYER' :
                $dbRoleName = 'buyer';
                break;

            default:
                $dbRoleName = 'buyer';
                break;
        }

        $roleInst = new RolesService();
        $roleData = $roleInst->getByName($dbRoleName, true);
        return $roleData;
    }

}
if (!function_exists('roleData')) {

    /**
     * @author Nuwan Ishara.
     * @author Function Creation Date: 31/09/2020.
     *
     *
     * get status codes
     * @category Helper functions.
     *
     * @param string $statusString Input - Name of the status
     *
     * @var
     *
     * @return string $settingValue Output status code
     *
     * @throws
     *
     *
     * @uses
     *
     * @version 1.0.0
     *
     *
     */
    function roleData($roleId)
    {
        $roleInst = new RolesService();
        $roleData = $roleInst->getById($roleId, true);
        return $roleData;
    }

}

if (!function_exists('permissionLevelCheck')) {

    /**
     * @author Nuwan Ishara.
     * @author Function Creation Date: 31/09/2020.
     *
     *
     * get status codes
     * @category Helper functions.
     *
     * @param string $permissionLevel Input - Name of the status
     *
     * @var
     *
     * @return string $settingValue Output status code
     *
     * @throws
     *
     *
     * @uses
     *
     * @version 1.0.0
     *
     *
     */
    function permissionLevelCheck($permissionLevel, $roleId)
    {
        $settingValue = [];
        switch ($permissionLevel) {
            case 'ADMIN_ONLY' :
                $settingValue = [roleNames('ADMIN')->id];
                break;
            case 'ADMINS_ONLY' :
                $settingValue = [roleNames('ADMIN')->id, roleNames('ADMIN_ASSIST')->id];
                break;
            case 'ADMIN_ASSIST_ONLY' :
                $settingValue = [roleNames('ADMIN_ASSIST')->id];
                break;
            case 'BUYER_ONLY' :
                $settingValue = [roleNames('BUYER')->id];
                break;
            case 'SELLER_ONLY' :
                $settingValue = [roleNames('SELLER')->id];
                break;
            default:
                $settingValue = [];
                break;
        }
        if (in_array($roleId, $settingValue)) {
            return true;
        }

        return false;
    }

}

if (!function_exists('checkDataType')) {

    /**
     * @author Nuwan Ishara.
     * @author Function Creation Date: 31/09/2020.
     *
     *
     * check the variable value's type
     * @category Helper functions.
     *
     * @param string $variable Input - type of the reference(1-  invoice, 2- grn, 3 - expense)
     * @param string $requiredType Input - type of the reference(numeric - variable in string contains only the numbers,  integer - variable strictly integer )
     *
     *
     * @var
     *
     * @return
     *
     * @throws Exception
     * INVALID_DATA_TYPE
     *
     * @uses
     *
     * @version 1.0.0
     *
     *
     */
    function checkDataType($variable, $requiredType = 'numeric')
    {
        try {
            if ($requiredType == 'integer') {
                if (!is_int($variable)) {
                    throw new Exception("INVALID_DATA_TYPE", getStatusCodes('VALIDATION_ERROR'));
                }
            }

            if ($requiredType == 'numeric') {
                if (!is_numeric($variable)) {
                    throw new Exception("INVALID_DATA_TYPE", getStatusCodes('VALIDATION_ERROR'));
                }
            }

            if ($requiredType == 'string') {
                if (!is_string($variable)) {
                    throw new Exception("INVALID_DATA_TYPE", getStatusCodes('VALIDATION_ERROR'));
                }
            }

            return true;
        } catch (Exception $exception) {
            addToLog($exception->getMessage());
            return $exception;
        }
    }

}
