<?php

declare(strict_types=1);

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

    'accepted' => '":Attribute" dient te worden geaccepteerd.',
    'accepted_if' => '":Attribute" dient te worden geaccepteerd wanneer ":Other" :value is.',
    'active_url' => '":Attribute" is geen geldige URL.',
    'after' => '":Attribute" moet een datum zijn na :date.',
    'after_or_equal' => '":Attribute" moet een datum zijn na of gelijk aan :date.',
    'alpha' => '":Attribute" mag alleen letters bevatten.',
    'alpha_dash' => '":Attribute" mag alleen letters, nummers, en strepen bevatten.',
    'alpha_num' => '":Attribute" mag alleen letters en nummers bevatten.',
    'array' => '":Attribute" moet een array zijn.',
    'before' => '":Attribute" moet een datum zijn eerder dan :date.',
    'before_or_equal' => '":Attribute" moet een datum zijn voor of gelijk aan :date.',
    'between' => [
        'numeric' => '":Attribute" moet tussen :min en :max liggen.',
        'file' => '":Attribute" moet tussen :min en :max kilobytes zijn.',
        'string' => '":Attribute" moet tussen :min en :max karakters lang zijn.',
        'array' => '":Attribute" moet tussen :min en :max items bevatten.',
    ],
    'boolean' => '":Attribute" kan enkel waar of niet waar zijn.',
    'confirmed' => '":Attribute" bevestiging komt niet overeen.',
    'current_password' => 'Het wachtwoord is ongeldig.',
    'date' => '":Attribute" is geen geldige datum.',
    'date_equals' => '":Attribute" moet een datum zijn gelijk aan :date.',
    'date_format' => '":Attribute" komt niet overeen met het formaat :format.',
    'declined' => '":Attribute" dient te worden afgewezen.',
    'declined_if' => '":Attribute" dient te worden afgewezen wanneer ":Other" :value is.',
    'different' => '":Attribute" en ":Other" dienen verschillend te zijn.',
    'digits' => '":Attribute" moet :digits cijfers zijn.',
    'digits_between' => '":Attribute" moet tussen :min en :max cijfers zijn.',
    'dimensions' => '":Attribute" heeft een ongeldige grootte.',
    'distinct' => '":Attribute" heeft een dubbele waarde.',
    'doesnt_start_with' => '":Attribute" kan niet beginnen met de volgende waardes: :values.',
    'email' => '":Attribute" dient een geldig emailadres te zijn.',
    'ends_with' => '":Attribute" moet eindigen met één van het volgende: :values',
    'enum' => 'Geselecteerde ":Attribute" is ongeldig.',
    'exists' => 'Geselecteerde ":Attribute" is ongeldig.',
    'file' => '":Attribute" moet een bestand zijn.',
    'filled' => '":Attribute" veld is verplicht.',
    'gt' => [
        'numeric' => 'Het ":Attribute" veld moet groter zijn dan :value.',
        'file' => 'Het ":Attribute" veld moet groter zijn dan :value kilobytes.',
        'string' => 'Het ":Attribute" veld moet groter zijn dan :value tekens.',
        'array' => 'Het ":Attribute" veld moet meer dan :value items bevatten.',
    ],
    'gte' => [
        'numeric' => 'Het ":Attribute" veld moet groter of gelijk zijn aan :value.',
        'file' => 'Het ":Attribute" veld moet groter of gelijk zijn aan :value kilobytes.',
        'string' => 'Het ":Attribute" veld moet groter of gelijk zijn aan :value tekens.',
        'array' => 'Het ":Attribute" veld moet :value of meer items bevatten.',
    ],
    'image' => '":Attribute" dient een afbeelding te zijn.',
    'in' => 'Geselecteerde ":Attribute" is ongeldig.',
    'in_array' => '":Attribute" komt niet voor in ":Other".',
    'integer' => '":Attribute" dient een geheel getal te zijn.',
    'ip' => '":Attribute" dient een geldig IP adres te zijn.',
    'ipv4' => '":Attribute" dient een geldig IPv4 adres te zijn.',
    'ipv6' => '":Attribute" dient een geldig IPv6 adres te zijn..',
    'json' => '":Attribute" moet een geldige JSON string zijn.',
    'lt' => [
        'numeric' => 'Het ":Attribute" veld moet kleiner zijn dan :value.',
        'file' => 'Het ":Attribute" veld moet kleiner zijn dan :value kilobytes.',
        'string' => 'Het ":Attribute" veld moet kleiner zijn dan :value tekens.',
        'array' => 'Het ":Attribute" veld moet minder dan :value items bevatten.',
    ],
    'lte' => [
        'numeric' => 'Het ":Attribute" veld moet kleiner of gelijk zijn aan :value.',
        'file' => 'Het ":Attribute" veld moet kleiner of gelijk zijn aan :value kilobytes.',
        'string' => 'Het ":Attribute" veld moet kleiner of gelijk zijn aan :value tekens.',
        'array' => 'Het ":Attribute" veld mag maximaal :value items bevatten.',
    ],
    'mac_address' => '":Attribute" moet een geldig MAC adres zijn.',
    'max' => [
        'numeric' => '":Attribute" mag niet groter zijn dan :max.',
        'file' => '":Attribute" mag niet groter zijn dan :max kilobytes.',
        'string' => '":Attribute" mag niet groter zijn dan :max karakters.',
        'array' => '":Attribute" mag niet meer dan :max items bevatten.',
    ],
    'mimes' => '":Attribute" dient een bestand te zijn van het type: :values.',
    'mimetypes' => '":Attribute" dient een bestand te zijn van het type: :values.',
    'min' => [
        'numeric' => '":Attribute" dient minimaal :min te zijn.',
        'file' => '":Attribute" dient minimaal :min kilobytes te zijn.',
        'string' => '":Attribute" dient minimaal :min karakters te bevatten.',
        'array' => '":Attribute" dient minimaal :min items te bevatten.',
    ],
    'multiple_of' => 'Het ":Attribute" moet een veelvoud zijn van :value.',
    'not_in' => 'Geselecteerde ":Attribute" is ongeldig.',
    'not_regex' => 'Het ":Attribute" format is ongeldig.',
    'numeric' => '":Attribute" dient een nummer te zijn.',
    'password' => [
        'letters' => '":Attribute" moet alleen uit letters bestaan.',
        'mixed' => '":Attribute" moet minstens één hoofdletter en één kleine letter bevatten.',
        'numbers' => '":Attribute" moet minstens één nummer bevatten.',
        'symbols' => '":Attribute" moet minstens één symbool bevatten.',
        'uncompromised' => 'Attribute komt voor in een data lek. Kies een ander ":Attribute".',
    ],
    'present' => '":Attribute" dient aanwezig te zijn.',
    'prohibited' => 'Het ":Attribute" veld is niet toegestaan.',
    'prohibited_if' => 'Het ":Attribute" veld is niet toegestaan wanneer ":Other" :value is.',
    'prohibited_unless' => 'Het ":Attribute" veld is niet toegestaan tenzij ":Other" voorkomt in :values.',
    'prohibits' => 'Het ":Attribute" veld staat niet toe dat ":Other" aanwezig is.',
    'regex' => 'Het ":Attribute" formaat is ongeldig.',
    'required' => 'Het ":Attribute" veld is verplicht.',
    'required_array_keys' => 'Het veld ":Attribute" moet vermeldingen bevatten voor: :values.',
    'required_if' => 'Het ":Attribute" veld is verplicht wanneer ":Other" is :value.',
    'required_unless' => 'Het ":Attribute" veld is verplicht, tenzij ":Other" is in :values.',
    'required_with' => 'Het ":Attribute" veld is verplicht wanneer :values aanwezig is.',
    'required_with_all' => 'Het ":Attribute" veld is verplicht wanneer :values aanwezig is.',
    'required_without' => 'Het ":Attribute" veld is verplicht wanneer :values niet aanwezig is.',
    'required_without_all' => 'Het ":Attribute" veld is verplicht wanneer geen van :values aanwezig is.',
    'same' => '":Attribute" en ":Other" moeten hetzelfde zijn.',
    'size' => [
        'numeric' => '":Attribute" moet :size zijn.',
        'file' => '":Attribute" moet :size kilobytes groot zijn.',
        'string' => '":Attribute" moet :size karakters lang zijn.',
        'array' => '":Attribute" moet :size items bevatten.',
    ],
    'starts_with' => '":Attribute" moet beginnen met één van het volgende: :values',
    'string' => '":Attribute" moet een string zijn.',
    'timezone' => '":Attribute" moet een geldige tijdszone zijn.',
    'unique' => '":Attribute" is al in gebruik. Kies een andere waarde.',
    'uploaded' => 'Het uploaden van ":Attribute" is mislukt.',
    'url' => '":Attribute" formaat is ongeldig.',
    'uuid' => '":Attribute" moet een valide UUID zijn.',

    'custom' => [
        'files' => [
            'required' => 'Bestand is verplicht',
        ],
        'data' => [
            'number' => [
                'unique' => 'Waarde :value is al in gebruik: vul hier een unieke waarde in.',
            ],
        ],
        'ip_range_invalid' => 'Mag alleen geldige IPv4 / IPv6 patronen bevatten (gescheiden door komma\'s of spaties).',
        'mime_type_invalid' => ':Attribute is geen geldig bestand.',
    ],
];
