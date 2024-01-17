<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Setting;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class SettingController extends Controller
{
    use ResponseTrait;
    public function generalSetting()
    {
        $data['pageTitle'] = __("General Setting");
        $data['subGeneralSettingActiveClass']  = 'active';
        $data['currencies'] = Currency::all();
        $data['current_currency'] = Currency::where('current_currency', 'on')->first();
        $data['languages'] = Language::all();
        $data['default_language'] = Language::where('default', 1)->first();
        return view('admin.setting.general-setting')->with($data);
    }

    public function generalSettingUpdate(Request $request)
    {
        $inputs = Arr::except($request->all(), ['_token']);
        $keys = [];

        foreach ($inputs as $k => $v) {
            $keys[$k] = $k;
        }

        foreach ($inputs as $key => $value) {
            $option = Setting::firstOrCreate(['option_key' => $key]);

            try {

                if ($request->hasFile('app_preloader') && $key == 'app_preloader') {
                    $upload = settingImageStoreUpdate($option->id, $request->app_preloader, 'preloader');
                    $option->option_value = $upload;
                    $option->save();
                } elseif ($request->hasFile('app_logo') && $key == 'app_logo') {
                    $upload = settingImageStoreUpdate($option->id, $request->app_logo, 'logo');
                    $option->option_value = $upload;
                    $option->save();
                } elseif ($request->hasFile('app_logo_black') && $key == 'app_logo_black') {
                    $upload = settingImageStoreUpdate($option->id, $request->app_logo_black, 'logo-black');
                    $option->option_value = $upload;
                    $option->save();
                } elseif ($request->hasFile('app_fav_icon') && $key == 'app_fav_icon') {
                    $upload = settingImageStoreUpdate($option->id, $request->app_fav_icon, 'favicon');
                    $option->option_value = $upload;
                    $option->save();
                } elseif ($request->hasFile('sign_in_image') && $key == 'sign_in_image') {
                    $upload = settingImageStoreUpdate($option->id, $request->sign_in_image, 'login');
                    $option->option_value = $upload;
                    $option->save();
                } elseif ($request->hasFile('home_hero_area_image') && $key == 'home_hero_area_image') {
                    $upload = settingImageStoreUpdate($option->id, $request->home_hero_area_image, 'home');
                    $option->option_value = $upload;
                    $option->save();
                } elseif ($request->hasFile('home_generate_content_section_image') && $key == 'home_generate_content_section_image') {
                    $upload = settingImageStoreUpdate($option->id, $request->home_generate_content_section_image, 'home');
                    $option->option_value = $upload;
                    $option->save();
                } else {
                    $option->option_value = $value;
                    $option->save();
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        /**  ====== Set Currency ====== */
        if ($request->currency_id) {
            Currency::where('id', $request->currency_id)->update(['current_currency' => 'on']);
            Currency::where('id', '!=', $request->currency_id)->update(['current_currency' => 'off']);
        }

        /**  ====== Set Language ====== */
        if ($request->language_id) {
            Language::where('id', $request->language_id)->update(['default' => 1]);
            Language::where('id', '!=', $request->language_id)->update(['default' => 0]);
            Language::where('default', 1)->first();
        }

        return redirect()->back()->with('success', __(UPDATED_SUCCESSFULLY));
    }

    public function colorSetting()
    {
        $data['pageTitle'] = __("Color Setting");
        $data['subColorSettingActiveClass']  = 'active';
        return view('admin.setting.color-setting')->with($data);
    }

    public function smtpSetting()
    {
        $data['pageTitle'] = __("SMTP Setting");
        $data['subSmtpSettingActiveClass']  = 'active';
        return view('admin.setting.smtp-setting')->with($data);
    }

    public function sendTestMail(Request $request)
    {
        $data = $request;
        $request->validate([
            'email' => 'email|required|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:255'
        ]);
        try {
            Mail::to($request->email)->send(new TestMail($data));
            $message = __(SENT_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function chatGptApiSetting()
    {
        $data['pageTitle'] = __("OpenAI API Setting");
        $data['subChatGPTApiSettingActiveClass']  = 'active';
        return view('admin.setting.chat-gpt-api')->with($data);
    }

    public function googleAnalytics()
    {
        $data['pageTitle'] = __("Google Analytics Setting");
        $data['subGoogleAnalyticsSettingActiveClass']  = 'active';
        return view('admin.setting.google-analytics')->with($data);
    }

    public function generalSettingEnvUpdate(Request $request)
    {
        $inputs = Arr::except($request->all(), ['_token']);
        $keys = [];

        foreach ($inputs as $k => $v) {
            $keys[$k] = $k;
        }

        foreach ($inputs as $key => $value) {
            if ($key == 'app_mail_status') {
                $option = Setting::firstOrCreate(['option_key' => $key]);
                $option->option_value = $value;
                $option->type = 1;
                $option->save();
            }
            setEnvironmentValue($key, $value);
        }

        return redirect()->back()->with('success', __(UPDATED_SUCCESSFULLY));
    }

    public function storageLink()
    {
        try {
            if (file_exists(public_path('storage'))) {
                $this->deleteDir(public_path('storage'));
                Artisan::call('storage:link');
                return redirect()->back()->with('success', 'Created Storage Link Updated Successfully');
            } else {
                Artisan::call('storage:link');
            }
            return redirect()->back()->with('success', 'Created Storage Link Updated Successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException($dirPath . ' must be a directory');
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public function migrateSeed()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
        return redirect()->back()->with('success', 'Migrate and Seed Updated Successfully');
    }

   public function getPages()
    {
        $data['pageTitle'] = __("Page");
        $data['subFaqActiveClass'] = 'active';
        Setting::firstOrCreate(['option_key' => 'privacy_policy']);
        Setting::firstOrCreate(['option_key' => 'terms_and_condition']);
        Setting::firstOrCreate(['option_key' => 'about_us']);
        Setting::firstOrCreate(['option_key' => 'distance-sales-agreement']);
        $data['privacyPolicy'] = Setting::where('option_key', 'privacy_policy')->first();
        $data['termsAndCondition'] = Setting::where('option_key', 'terms_and_condition')->first();
        $data['aboutUs'] = Setting::where('option_key', 'about_us')->first();
        $data['DistanceSalesAgreement'] = Setting::where('option_key', 'distance-sales-agreement')->first();

        return view('admin.setting.pages', $data);
    }
}
