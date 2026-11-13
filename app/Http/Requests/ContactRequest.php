<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Validator;

class ContactRequest extends FormRequest
{
    /** Minimum seconds between page load and submit; bots usually post instantly. */
    public const MIN_SECONDS = 3;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9 +()\-]*$/'],
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['prohibited'],        // honeypot: hidden from people, filled by bots
            'form_token' => ['required', 'string'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            try {
                $issued = (int) Crypt::decryptString((string) $this->input('form_token'));
            } catch (DecryptException) {
                $v->errors()->add('message', 'Please reload the page and try again.');

                return;
            }
            if (time() - $issued < self::MIN_SECONDS) {
                $v->errors()->add('message', 'That was very quick. Please wait a moment and send again.');
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['website.prohibited' => 'Please reload the page and try again.'];
    }
}
