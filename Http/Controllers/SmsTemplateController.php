<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\Language;
use App\Models\SmsTemplate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;

class SmsTemplateController extends Controller
{
    public function show(SmsTemplate $smsTemplate): View|Factory|Application
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

    public function update(Request $request, int $id): RedirectResponse
    {
        $req = $request->all();
        $smstemplate = SmsTemplate::findOrFail($id);
        $smstemplate->sms_status = $req['sms_status'];
        $smstemplate->sms_body = $req['sms_body'];
        $smstemplate->save();

        return back()->with('success', 'Successfully Updated');
    }
}
