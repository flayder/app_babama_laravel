<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Configure;
use App\Models\EmailTemplate;
use App\Models\Language;
use App\Models\SmsControl;
use App\Models\SmsTemplate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Stevebauman\Purify\Facades\Purify;
use Validator;

class SmsControlController extends Controller
{
    public function smsConfig(Request $request)
    {
        if ($request->isMethod('GET')) {
            $data['smsControl'] = SmsControl::firstOrCreate(['id' => 1]);
            $data['control'] = Configure::firstOrNew();

            return view('admin.pages.smscontrols.index', $data);
        } elseif ($request->isMethod('POST')) {
            $purifiedData = ($request->all());

            $validator = Validator::make($purifiedData, [
                'actionMethod' => 'required|min:3|max:4',
                'actionUrl' => 'required|url',
                'headerDataKeys.*' => 'nullable|string|min:2|required_with:headerDataValues.*',
                'headerDataValues.*' => 'nullable|string|min:2|required_with:headerDataKeys.*',
                'paramKeys.*' => 'nullable|string|min:2|required_with:paramValues.*',
                'paramValues.*' => 'nullable|string|min:2|required_with:paramKeys.*',
                'formDataKeys.*' => 'nullable|string|min:2|required_with:formDataValues.*',
                'formDataValues.*' => 'nullable|string|min:2|required_with:formDataKeys.*',
            ], [
                'min' => 'Field value must be at least :min characters.',
                'string' => 'Field value must be :string.',
                'required_with' => 'Field value empty not allowed',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $purifiedData = (object) $purifiedData;

            $headerData = array_combine($purifiedData->headerDataKeys, $purifiedData->headerDataValues);
            $paramData = array_combine($purifiedData->paramKeys, $purifiedData->paramValues);
            $formData = array_combine($purifiedData->formDataKeys, $purifiedData->formDataValues);

            $headerData = (empty(array_filter($headerData))) ? null : json_encode(array_filter($headerData));
            $paramData = (empty(array_filter($paramData))) ? null : json_encode(array_filter($paramData));
            $formData = (empty(array_filter($formData))) ? null : json_encode(array_filter($formData));

            $actionMethod = $purifiedData->actionMethod;
            $actionUrl = $purifiedData->actionUrl;

            $smsControl = SmsControl::firstOrCreate(['id' => 1]);
            $smsControl->actionUrl = $actionUrl;
            $smsControl->actionMethod = $actionMethod;
            $smsControl->formData = $formData;
            $smsControl->paramData = $paramData;
            $smsControl->headerData = $headerData;
            $smsControl->save();

            return back()->with('success', 'SMS configuration Saved');
        }
    }

    public function smsControlAction(Request $request): RedirectResponse
    {
        $configure = Configure::firstOrNew();
        $reqData = ($request->except('_token', '_method'));

        config(['basic.sms_notification' => (int) $reqData['sms_notification']]);
        config(['basic.sms_verification' => (int) $reqData['sms_verification']]);

        $fp = fopen(base_path().'/config/basic.php', 'w');
        fwrite($fp, '<?php return '.var_export(config('basic'), true).';');
        fclose($fp);

        $configure->fill($reqData)->save();

        session()->flash('success', ' Updated Successfully');

        Artisan::call('optimize:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        return back();
    }

    public function show(): Factory|View|Application
    {
        $smstemplate = SmsTemplate::groupBy('template_key')->distinct()->orderBy('template_key')->get();

        return view('admin.pages.smstemplate.show', compact('smstemplate'));
    }

    public function edit(int $id): Factory|View|Application
    {
        $smstemplate = SmsTemplate::findOrFail($id);
        $languages = Language::orderBy('short_name')->get();

        foreach ($languages as $lang) {
            $checkTemplate = EmailTemplate::where('template_key', $smstemplate->template_key)->where('language_id', $lang->id)->count();

            if ('en' == $lang->short_name && (null == $smstemplate->language_id)) {
                $smstemplate->language_id = $lang->id;
                $smstemplate->lang_code = $lang->short_name;
                $smstemplate->save();
            }

            if (0 == $checkTemplate) {
                $template = new EmailTemplate();
                $template->language_id = $lang->id;
                $template->template_key = $smstemplate->template_key;
                $template->name = $smstemplate->name;
                $template->subject = $smstemplate->subject;
                $template->template = $smstemplate->template;
                $template->sms_body = $smstemplate->sms_body;
                $template->short_keys = $smstemplate->short_keys;
                $template->mail_status = $smstemplate->mail_status;
                $template->sms_status = $smstemplate->sms_status;
                $template->lang_code = $lang->short_name;
                $template->save();
            }
        }

        $smsTemplates = EmailTemplate::where('template_key', $smstemplate->template_key)->with('language')->get();

        return view('admin.pages.smstemplate.edit', compact('smstemplate', 'smsTemplates'));
    }

    public function update(Request $request, SmsTemplate $smsTemplate, $id): RedirectResponse
    {
        $req = $request->all();
        $smstemplate = SmsTemplate::findOrFail($id);
        $smstemplate->sms_status = $req['sms_status'];
        $smstemplate->sms_body = $req['sms_body'];
        $smstemplate->save();

        return back()->with('success', 'Successfully Updated');
    }
}
