<?php

namespace App\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Admin pages show the session 'error' message as a pop-up (admin.common.footer-js) but do not print
 * validation errors, so a failed check is also sent back as that pop-up message.
 */
trait ShowsErrorPopup
{
    protected function failedValidation(Validator $validator)
    {
        $messages = array_slice(array_values(array_unique($validator->errors()->all())), 0, 4);
        $response = redirect()->to($this->getRedirectUrl())
            ->withInput()
            ->withErrors($validator->errors(), $this->errorBag)
            ->with('error', implode(' ', $messages));

        throw new ValidationException($validator, $response);
    }
}
