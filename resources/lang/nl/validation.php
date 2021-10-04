<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Het :attribute moet worden geaccepteerd.',
    'active_url' => 'Het :attribute is geen geldige URL.',
    'after' => 'Het :attribute moet een datum zijn na :date.',
    'after_or_equal' => 'Het :attribute moet een datum zijn na of gelijk zijn aan :date.',
    'alpha' => 'Het :attribute mag alleen letters bevatten.',
    'alpha_dash' => 'Het :attribute mag alleen letters, cijfers, streepjes en underscores bevatten.',
    'alpha_num' => 'Het :attribute mag alleen letters en cijfers bevatten.',
    'array' => 'Het :attribute moet een array zijn.',
    'before' => 'Het :attribute moet een datum eerder zijn dan :date.',
    'before_or_equal' => 'Het :attribute moet een datum vóór of gelijk zijn aan :date.',
    'between' => [
        'numeric' => 'Het :attribute moet tussen  :min en :max zijn.',
      'file' => 'Het :attribute moet tussen :min en :max kilobytes zijn.',
        'string' => 'Het :attribute moet tussen de :min en :max karakters zijn.',
        'array' => 'Het :attribute moet tussen :min en :max items bevatten.',
    ],
    'boolean' => 'Het :attribute veld moet waar of fout zijn.',
    'confirmed' => 'Het :attribute bevestiging komt niet overeen.',
    'date' => 'Het :attribute is geen geldige datum.',
    'date_equals' => 'Het :attribute moet een datum zijn die gelijk is aan :date.',
    'date_format' => 'Het :attribute komt niet overeen met het formaat :format.',
    'different' => 'Het :attribute en :other moeten anders zijn.',
    'digits' => 'Het :attribute moet :digits zijn.',
    'digits_between' => 'Het :attribute moet tussen de :min en :max cijfers liggen.',
    'dimensions' => 'Het :attribute heeft ongeldige foto dimensies.',
    'distinct' => 'Het :attribute veld heeft een dubbele waarde.',
    'email' => 'Het :attribute moet een geldig e-mailadres zijn.',
    'ends_with' => 'Het :attribute moet eindigen met een van de volgende: :values.',
    'exists' => 'Het geselecteerde :attribute is ongeldig.',
    'file' => 'Het :attribute moet een bestand zijn.',
    'filled' => 'Het veld :attribute moet een waarde hebben.',
    'gt' => [
        'numeric' => 'Het :attribute moet groter zijn dan :value.',
        'file' => 'Het :attribute moet groter zijn dan :value kilobytes.',
        'string' => 'Het :attribute moet groter zijn dan :value karakters.',
        'array' => 'Het :attribute moet meer dan :value iitems bevatten.',
    ],
    'gte' => [
        numeric' => 'Het :attribute moet groter zijn dan of gelijk zijn aan :value.',
        'file' => 'Het :attribute moet groter zijn dan of gelijk zijn aan :value kilobytes.',
        'string' => 'Het :attribute moet groter zijn dan of gelijk zijn aan :value tekens.',
        'array' => 'Het :attribute moet :value items of meer hebben.',
    ],
    'image' => 'Het :attribute moet een afbeelding zijn.',
    'in' => 'Het geselecteerde :attribute is ongeldig.',
    'in_array' => 'Het geselecteerde :attribute is ongeldig.',
    'integer' => 'Het :attribute moet een geheel getal zijn.',
    'ip' => 'Het :attribute moet een geldig IP-adres zijn.',
    'ipv6' => 'Het :attribute moet een geldig IPv6-adres zijn.',
    'json' => 'Het :attribute moet een geldige JSON-tekenreeks zijn.',
    'lt' => [
        'numeric' => 'Het :attribute moet kleiner zijn dan :value.',
        'file' => 'Het :attribute moet kleiner zijn dan :value kilobytes.',
        'string' => 'Het :attribute moet kleiner zijn dan :value.',
        'array' => 'Het :attribute moet minder dan :value items bevatten.',
    ],
    'lte' => [
        'numeric' => 'Het :attribute moet kleiner zijn dan of gelijk zijn aan :value.',
        'file' => 'Het :attribute moet kleiner zijn dan of gelijk zijn aan :value kilobytes.',
        'string' => 'Het :attribute moet kleiner zijn dan of gelijk zijn aan :value tekens.',
        'array' => 'Het :attribute mag niet meer dan :value items bevatten.',
    ],
    'max' => [
        'numeric' => 'Het :attribute mag niet groter zijn dan :max.',
        'file' => 'Het :attribute mag niet groter zijn dan :max kilobytes.',
        'string' => 'Het :attribute mag niet groter zijn dan :max karakters.',
        'array' => 'Het :attribute mag niet meer dan :max items bevatten.',
    ],
    'mimes' => 'Het :attribute moet een bestand zijn van het type: :values.',
    'mimetypes' => 'Het :attribute moet een bestand zijn van het type: :values.',
    'min' => [
        'numeric' => 'Het :attribute moet minimaal :min zijn.',
        'file' => 'Het :attribute moet minimaal :min kilobytes zijn.',
        'string' => 'Het :attribute moet minimaal :min karakters bevatten.',
        'array' => 'Het :attribute moet minimaal :min items bevatten.',
    ],
    'multiple_of' => 'Het :attribute moet een veelvoud zijn van :value.',
    'not_in' => 'Het geselecteerde :attribute is ongeldig.',
    'not_regex' => 'De indeling :attribute is ongeldig.',
    'numeric' => 'Het :attribute moet een getal zijn.',
    'password' => 'Het wachtwoord is incorrect.',
    'present' => 'Het veld :attribute moet aanwezig zijn.',
    'regex' => 'De indeling :attribute is ongeldig.',
    'required' => 'Het veld :attribute is verplicht.',
    'required_if' => 'Het veld :attribute is vereist wanneer :other :value is.',
    'required_unless' => 'Het veld :attribute is verplicht tenzij :other in :values ​​staat.',
    'required_with' => 'Het veld :attribute is verplicht wanneer :values ​​aanwezig is.',
    'required_with_all' => 'Het veld :attribute is vereist wanneer :values aanwezig zijn.',
    'required_without' => 'Het veld :attribute is vereist wanneer :values ​​niet aanwezig is.',
    'required_without_all' => 'Het veld :attribute is vereist als geen van de :values aanwezig is.',
    'same' => 'Het :attribute en :other moeten overeenkomen.',
    'size' => [
        'numeric' => 'Het :attribuut moet :size zijn.',
        'file' => 'Het :attribuut moet :size kilobytes zijn.',
        'string' => 'Het :attribuut moet :size karakters zijn.',
        'array' => 'Het :attribuut moet :size items bevatten.',
    ],
    'starts_with' => 'Het :attribuut moet beginnen met een van de volgende: :values.',
    'string' => 'Het :attribuut moet een tekenreeks zijn.',
    'timezone' => 'Het :attribuut moet een geldige zone zijn.',
    'unique' => 'Het :attribuut moet een geldige zone zijn.',
    'uploaded' => 'Het :attribuut kan niet worden geüpload.',
    'url' => 'De indeling :attribute is ongeldig.',
    'uuid' => 'hij :attribuut moet een geldige UUID zijn.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

    /*
    |--------------------------------------------------------------------------
    |Dutch Language by Finnie2006
    |--------------------------------------------------------------------------

];
