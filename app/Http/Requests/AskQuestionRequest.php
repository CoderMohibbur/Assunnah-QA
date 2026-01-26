<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AskQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Guest allow
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', 1),
            ],

            'name'  => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:190'],

            'title' => ['required', 'string', 'min:6', 'max:255'],
            'body'  => ['required', 'string', 'min:10', 'max:20000'],

            // honeypot + timing
            'website' => ['nullable', 'max:0'], // must be empty
            'form_started_at' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'ক্যাটাগরি নির্বাচন করুন।',
            'category_id.exists'   => 'সঠিক ক্যাটাগরি নির্বাচন করুন।',

            'name.required'  => 'আপনার নাম লিখুন।',
            'name.min'       => 'নাম কমপক্ষে ২ অক্ষরের হতে হবে।',

            'phone.required' => 'মোবাইল নম্বর লিখুন।',
            'phone.regex'    => 'সঠিক মোবাইল নম্বর দিন (যেমন: 01XXXXXXXXX)।',

            'email.email'    => 'সঠিক ইমেইল দিন।',

            'title.required' => 'প্রশ্নের শিরোনাম লিখুন।',
            'title.min'      => 'শিরোনাম কমপক্ষে ৬ অক্ষরের হতে হবে।',

            'body.required'  => 'প্রশ্ন বিস্তারিত লিখুন।',
            'body.min'       => 'প্রশ্ন বিস্তারিত কমপক্ষে ১০ অক্ষরের হতে হবে।',

            'website.max' => 'অনুগ্রহ করে আবার চেষ্টা করুন।',
            'form_started_at.required' => 'অনুগ্রহ করে আবার চেষ্টা করুন।',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'  => is_string($this->name) ? trim($this->name) : $this->name,
            'phone' => is_string($this->phone) ? trim($this->phone) : $this->phone,
            'email' => is_string($this->email) ? trim($this->email) : $this->email,
            'title' => is_string($this->title) ? trim($this->title) : $this->title,
            'body'  => is_string($this->body) ? trim($this->body) : $this->body,
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $started = (int) $this->input('form_started_at', 0);
            $now = now()->timestamp;

            // ✅ too fast submit (less than 4 seconds) => likely bot
            if ($started > 0 && ($now - $started) < 4) {
                $validator->errors()->add('rate', 'খুব দ্রুত সাবমিট করা হয়েছে। কয়েক সেকেন্ড পর আবার চেষ্টা করুন।');
            }
        });
    }
}
