<?php

use App\Models\Category;
use App\Models\Currency;
use App\Models\FileManager;
use App\Models\Language;
use App\Models\Notification;
use App\Models\SearchResultItem;
use App\Models\Setting;
use App\Models\UserPackage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


function getOption($option_key, $default = '')
{
    $system_settings = config('settings');
    if ($option_key && isset($system_settings[$option_key])) {
        return $system_settings[$option_key];
    } else {
        return $default;
    }
}

if (!function_exists('getSlug')) {
    function getSlug($text)
    {
        if ($text) {
            $data = preg_replace("/[~`{}.'\"\!\@\#\$\%\^\&\*\(\)\_\=\+\/\?\>\<\,\[\]\:\;\|\\\]/", "", $text);
            $slug = preg_replace("/[\/_|+ -]+/", "-", $data);
            return $slug;
        }
        return '';
    }
}

function number_parser($value)
{
    return (float) str_replace(',', '', number_format(($value), 2));
}

function getSettingImage($option_key)
{
    try {
        $system_settings = config('settings');
        if ($option_key && isset($system_settings[$option_key])) {
            $fileManager = FileManager::find($system_settings[$option_key]);
            $destinationPath = 'files/Setting' . '/' . $fileManager->file_name;
            if (Storage::disk(config('app.STORAGE_DRIVER'))->exists($destinationPath)) {
                if (config('app.STORAGE_DRIVER') == "s3") {
                    $s3 = Storage::disk(config('app.STORAGE_DRIVER'));
                    return $s3->url($destinationPath);
                }
                return asset('storage/' . $destinationPath);
            }
        } else {
            return asset('assets/images/no-image.jpg');
        }
    } catch (\Exception $e) {
        return asset('assets/images/no-image.jpg');
    }
}

function settingImageStoreUpdate($option_id, $requestFile, $name)
{
    $new_file = FileManager::where('origin_type', 'App\Models\Setting')->where('origin_id', $option_id)->first();

    if ($new_file) {
        $new_file->removeFile();
        $upload = $new_file->updateUpload($new_file->id, 'Setting', $requestFile, $name);
    } else {
        $new_file = new FileManager();
        $upload = $new_file->upload('Setting', $requestFile, $name);
    }

    if ($upload['status']) {
        $upload['file']->origin_id = $option_id;
        $upload['file']->origin_type = "App\Models\Setting";
        $upload['file']->save();
        return $upload['file']->id;
    } else {
        throw new Exception($upload['message']);
    }
}

function getErrorMessage($e, $customMsg = null)
{
    if ($customMsg != null) {
        return $customMsg;
    }
    if (env('APP_DEBUG')) {
        return $e->getMessage() . $e->getLine();
    } else {
        return SOMETHING_WENT_WRONG;
    }
}

function set_local_timezone($timezone)
{
    config(['app.timezone' => @$timezone] ?? 'UTC');
    date_default_timezone_set(@$timezone ?? 'UTC');
}

function  getFileUrl($folderName, $fileName)
{
    if ($fileName == '' || $folderName == '') {
        return asset('assets/images/no-image.jpg');
    }
    $destinationPath = $folderName . '/' . $fileName;
    if (Storage::disk(config('app.STORAGE_DRIVER'))->exists($destinationPath)) {
        if (config('app.STORAGE_DRIVER') != "public") {
            $s3 = Storage::disk(config('app.STORAGE_DRIVER'));
            return $s3->url($destinationPath);
        }
        if ($destinationPath != '/') {
            return asset('storage/' . $destinationPath);
        }
    }

    return asset('assets/images/no-image.jpg');
}

if (!function_exists('getCityById')) {
    function getCityById($city_id)
    {
        $cities_file = public_path('file/cities.csv');
        $cityArr = csvToArray($cities_file);
        foreach ($cityArr as $city) {
            if ($city['id'] == $city_id) {
                $result = array(
                    'id' => $city['id'],
                    'name' => $city['name'],
                    'state_id' => $city['state_id'],
                );
                return $result;
            }
        }
        return '';
    }
}

if (!function_exists('getStateById')) {
    function getStateById($state_id)
    {
        $states_file = public_path('file/states.csv');
        $stateArr = csvToArray($states_file);
        foreach ($stateArr as $state) {
            if ($state['id'] == $state_id) {
                $result = array(
                    'id' => $state['id'],
                    'name' => $state['name'],
                    'country_id' => $state['country_id'],
                );
                return $result;
            }
        }
        return '';
    }
}

if (!function_exists('getCountryById')) {
    function getCountryById($country_id)
    {
        $states_file = public_path('file/countries.csv');
        $countryArr = csvToArray($states_file);
        foreach ($countryArr as $country) {
            if ($country['id'] == $country_id) {
                $result = array(
                    'id' => $country['id'],
                    'name' => $country['country_name'],
                    'sortname' => $country['sortname'],
                );
                return $result;
            }
        }
        return '';
    }
}

if (!function_exists('csvToArray')) {
    function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }
}

function convertToReadableSize($size)
{
    $base = log($size) / log(1024);
    $suffix = array("", "KB", "MB", "GB", "TB");
    $f_base = floor($base);
    return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
}

function getCurrencySymbol()
{
    $currency = Currency::where('current_currency', 'on')->first();
    if ($currency) {
        $symbol = $currency->symbol . '';
        return $symbol;
    }
    return '';
}

function getCurrencyPlacement()
{
    $currency = Currency::where('current_currency', 'on')->first();
    $placement = 'before';
    if ($currency) {
        $placement = $currency->currency_placement;
        return $placement;
    }

    return $placement;
}

function currencyPrice($price)
{
    if ($price == null) {
        return 0;
    }
    if (getCurrencyPlacement() == 'after')
        return number_format($price, 2) . '' . getCurrencySymbol();
    else {
        return getCurrencySymbol() . number_format($price, 2);
    }
}

function timeFormat($date)
{
    return Carbon::parse($date)->format('Y-m-d');
}

function setEnvironmentValue($envKey, $envValue)
{
    try {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        $str .= "\n"; // In case the searched variable is in the last line without \n
        $keyPosition = strpos($str, "{$envKey}=");
        if ($keyPosition) {
            $endOfLinePosition = strpos($str, PHP_EOL, $keyPosition);
            $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
            $envValue = str_replace(chr(92), "\\\\", $envValue);
            $envValue = str_replace('"', '\"', $envValue);
            $newLine = "{$envKey}=\"{$envValue}\"";
            if ($oldLine != $newLine) {
                $str = str_replace($oldLine, $newLine, $str);
                $str = substr($str, 0, -1);
                $fp = fopen($envFile, 'w');
                fwrite($fp, $str);
                fclose($fp);
            }
        } else if (strtoupper($envKey) == $envKey) {
            $envValue = str_replace(chr(92), "\\\\", $envValue);
            $envValue = str_replace('"', '\"', $envValue);
            $newLine = "{$envKey}=\"{$envValue}\"\n";
            $str .= $newLine;
            $str = substr($str, 0, -1);
            $fp = fopen($envFile, 'w');
            fwrite($fp, $str);
            fclose($fp);
        }
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

if (!function_exists('languages')) {
    function languages()
    {
        $data = Language::where('status', 1)->get();
        if ($data) {
            return $data;
        }
        return [];
    }
}

if (!function_exists('languageLocale')) {
    function languageLocale($locale)
    {
        $data = Language::where('code', $locale)->first();
        if ($data) {
            return $data->code;
        }
        return 'en';
    }
}

function selectedLanguage()
{
    $language = Language::where('code', session()->get('local'))->first();
    if (!$language) {
        $language = Language::first();
        if ($language) {
            $ln = $language->code;
            session(['local' => $ln]);
            Carbon::setLocale($ln);
            App::setLocale(session()->get('local'));
        }
    }

    return $language;
}

function appLanguages()
{
    return Language::where('status', 1)->get();
}


if (!function_exists('getLayout')) {
    function getLayout()
    {
        $output = [
            USER_ROLE_ADMIN => 'admin',
            USER_ROLE_USER => 'user',
        ];

        return $output[auth()->user()->role];
    }
}

if (!function_exists('updateEnv')) {
    function updateEnv($values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                $str .= "\n";
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}=" . json_encode($envValue) . "\n";
                } else {
                    Log::info("{$envKey}=" . json_encode($envValue));
                    $str = str_replace($oldLine, "{$envKey}=" . json_encode($envValue), $str);
                }
            }
        }
        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) {
            return false;
        } else {
            return true;
        }
    }
}

function addNotification($title, $body = null, $url = null, $image = null, $user_id = null, $sender_id = null)
{
    $notification = new Notification();
    $notification->title = $title;
    $notification->body = $body;
    $notification->url = $url;
    $notification->image = $image;
    $notification->user_id = $user_id;
    $notification->sender_id = $sender_id;
    $notification->save();
}

function getNotification($user_id)
{
    $fetchNotification = Notification::join('users', 'users.id', '=', 'notifications.sender_id')
        ->join('file_managers', function ($join) {
            $join->on('file_managers.origin_id', '=', 'notifications.sender_id')
                ->where('file_managers.origin_type', '=', 'App\Models\User');
        })
        ->select('notifications.*', 'users.first_name', 'file_managers.file_name', 'file_managers.folder_name')
        ->where(function ($query) use ($user_id) {
            $query->where('notifications.user_id', $user_id)
                ->orWhere('notifications.user_id', null);
        })
        ->where('notifications.is_seen', DEACTIVATE)
        ->latest()->get();

    return $fetchNotification;
}

if (!function_exists('getCustomerCurrentBuildVersion')) {
    function getCustomerCurrentBuildVersion()
    {
        $buildVersion = getOption('build_version');

        if (is_null($buildVersion)) {
            return 1;
        }

        return (int)$buildVersion;
    }
}

if (!function_exists('setCustomerBuildVersion')) {
    function setCustomerBuildVersion($version)
    {
        $option = Setting::firstOrCreate(['option_key' => 'build_version']);
        $option->option_value = $version;
        $option->save();
    }
}

if (!function_exists('setCustomerCurrentVersion')) {
    function setCustomerCurrentVersion()
    {
        $option = Setting::firstOrCreate(['option_key' => 'current_version']);
        $option->option_value = config('app.current_version');
        $option->save();
    }
}

if (!function_exists('get_domain_name')) {
    function get_domain_name($url)
    {
        $parseUrl = parse_url(trim($url));
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        } else {
            $path = explode('/', $parseUrl['path']);
            $host = $path[0];
        }
        return  trim($host);
    }
}

if (!function_exists('setUserPackage')) {
    function setUserPackage($userId, $package, $duration, $orderId = NULL)
    {
        UserPackage::where(['user_id' => $userId])->whereIn('status' , [ACTIVE,INITIATE])->update(['status' => DEACTIVATE]);
        UserPackage::create([
            'user_id' => $userId,
            'package_id' => $package->id,
            'name' => $package->name,
            'generate_characters' => $package->generate_characters,
            'access_use_cases' => $package->access_use_cases,
            'write_languages' => 31,
            'access_tones' => 6,
            'generate_images' => $package->generate_images,
            'plagiarism_checker' => $package->plagiarism_checker,
            'access_community' => $package->access_community,
            'custom_use_cases' => $package->custom_use_cases,
            'dedicated_account' => $package->dedicated_account,
            'support' => $package->support,
            'monthly_price' => $package->monthly_price,
            'yearly_price' => $package->yearly_price,
            'device_limit' => $package->device_limit,
            'order_id' => $orderId,
            'is_trail' => $package->is_trail,
            'start_date' => now(),
            'end_date' => Carbon::now()->addDays($duration),
            'status' => ACTIVE,

        ]);
    }
}

if (!function_exists('getLimit')) {
    function getLimit($type, $userId = NULL)
    {
        $userId = is_null($userId) ? auth()->id() : $userId;
        $userPlan  = UserPackage::where('user_packages.status', ACTIVE)->where('user_id', $userId)->whereDate('end_date', '>=', now())->first();

        if ($type == RULES_CHARACTER) {
            if (is_null($userPlan)) {
                return 1;
            }
            $limit =  $userPlan->generate_characters;
            $used = SearchResultItem::where('user_id', $userId)->whereDate('created_at', '>=', $userPlan->start_date)->sum('total_characters');
            $remain = $limit - $used;
            $remain = $remain < 0 ? 0 : $remain;
            return $remain;
        } elseif ($type == RULES_LANGUAGE) {
            if (is_null($userPlan)) {
                return [];
            }
            return $userPlan->write_languageses;
        } elseif ($type == RULES_USE_CASE) {
            if (is_null($userPlan)) {
                return [];
            }
            // elseif(ins_array("-1", $userPlan->access_use_cases)){
            //     return Packag;
            // }
            return $userPlan->access_use_caseses;
        } elseif ($type == RULES_TONE) {
            if (is_null($userPlan)) {
                return [];
            }
            return $userPlan->access_toneses;
        } elseif ($type == RULES_PLAN_REMAINING_DAYS) {
            if (is_null($userPlan)) {
                return 0;
            }
            $endDate = Carbon::parse($userPlan->end_date);
            return Carbon::now()->diffInDays($endDate, false);
        }
    }
}


if (!function_exists('getCharUsed')) {
    function getCharUsed($userId = NULL)
    {
        $userId = is_null($userId) ? auth()->id() : $userId;
        $userPlan  = UserPackage::where('user_packages.status', ACTIVE)->where('user_id', $userId)->whereDate('end_date', '>=', now())->first();

        if (is_null($userPlan)) {
            return 0;
        }
        $used = SearchResultItem::where('user_id', $userId)->whereDate('created_at', '>=', $userPlan->start_date)->sum('total_characters');
        return $used;
    }
}


if (!function_exists('getUseCase')) {
    function getUseCase($useCase = [])
    {
       if(in_array("-1", $useCase)){
            return __("All");
       }
       return count($useCase);
    }
}

if (!function_exists('getCategories')) {
    function getCategories($take = null)
    {
        return Category::query()
            ->where('status', ACTIVE)
            ->when($take != null, function ($q) use ($take) {
                $q->take($take);
            })
            ->get();
    }
}

if (!function_exists('reviewStar')) {
    function reviewStar($star)
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i > $star) {
                $html .= '<span class="iconify" data-icon="ic:baseline-star"></span>';
            } else {
                $html .= '<span class="iconify star-filled" data-icon="ic:baseline-star"></span>';
            }
        }
        return $html;
    }
}
