<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ValidEmailDomain implements Rule
{
    public function passes($attribute, $value)
    {
        // Validate the basic email format
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Extract the domain from the email
        list($user, $domain) = explode('@', $value);

        // Check if the domain has valid MX records
        return checkdnsrr($domain, 'MX');
    }

    public function message()
    {
        return 'The :attribute must be a valid email address with a valid domain.';
    }
}
