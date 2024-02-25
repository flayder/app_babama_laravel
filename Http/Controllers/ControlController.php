<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\Upload;
use App\Models\Color;
use App\Models\Configure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Stevebauman\Purify\Facades\Purify;

class ControlController extends Controller
{
    use Upload;

    private function set($key, $value, $env)
    {
        foreach ($env as $env_key => $env_value) {
            $entry = explode('=', $env_value, 2);
            if ($entry[0] == $key) {
                $env[$env_key] = $key.'='.$value."\n";
            } else {
                $env[$env_key] = $env_value;
            }
        }

        return $env;
    }

    public function logoUpdate(Request $request)
    {
        if ($request->hasFile('image')) {
            try {
                $old = 'logo.png';
                $this->uploadImage($request->image, config('location.logo.path'), null, $old, null, $old);
            } catch (\Exception $exp) {
                return back()->with('error', 'Logo could not be uploaded.');
            }
        }
        if ($request->hasFile('footer_image')) {
            try {
                $old = 'footer-logo.png';
                $this->uploadImage($request->footer_image, config('location.logo.path'), null, $old, null, $old);
            } catch (\Exception $exp) {
                return back()->with('error', 'Footer logo could not be uploaded.');
            }
        }

        if ($request->hasFile('favicon')) {
            try {
                $old = 'favicon.png';
                $this->uploadImage($request->favicon, config('location.logo.path'), null, $old, null, $old);
            } catch (\Exception $exp) {
                return back()->with('error', 'favicon could not be uploaded.');
            }
        }

        return back()->with('success', 'Logo has been updated.');
    }

    public function seoUpdate(Request $request)
    {
        $reqData = $request->except('_token', '_method');
        $request->validate([
            'meta_keywords' => 'required',
            'meta_description' => 'required',
            'social_title' => 'required',
            'social_description' => 'required',
        ]);

        config(['seo.meta_keywords' => $reqData['meta_keywords']]);
        config(['seo.meta_description' => $request['meta_description']]);
        config(['seo.social_title' => $reqData['social_title']]);
        config(['seo.social_description' => $reqData['social_description']]);

        if ($request->hasFile('meta_image')) {
            try {
                $old = config('seo.meta_image');
                $meta_image = $this->uploadImage($request->meta_image, config('location.logo.path'), null, $old, null, $old);
                config(['seo.meta_image' => $meta_image]);
            } catch (\Exception $exp) {
                return back()->with('error', 'favicon could not be uploaded.');
            }
        }

        $fp = fopen(base_path().'/config/seo.php', 'w');
        fwrite($fp, '<?php return '.var_export(config('seo'), true).';');
        fclose($fp);

        Artisan::call('optimize:clear');

        return back()->with('success', 'Favicon has been updated.');
    }
}
