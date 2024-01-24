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

    'accepted' => 'O atributo :attribute deve ser aceito.',
    'accepted_if' => 'O atributo :attribute deve ser aceito quando :other for :value.',
    'active_url' => 'O atributo :attribute não é uma URL válida.',
    'after' => 'O atributo :attribute deve ser uma data posterior a :date.',
    'after_or_equal' => 'O atributo :attribute deve ser uma data posterior ou igual a :date.',
    'alpha' => 'O atributo :attribute deve conter apenas letras.',
    'alpha_dash' => 'O atributo :attribute deve conter apenas letras, números, traços e underscores.',
    'alpha_num' => 'O atributo :attribute deve conter apenas letras e números.',
    'array' => 'O atributo :attribute deve ser um array.',
    'ascii' => 'O atributo :attribute deve conter apenas caracteres alfanuméricos de um byte e símbolos.',
    'before' => 'O atributo :attribute deve ser uma data anterior a :date.',
    'before_or_equal' => 'O atributo :attribute deve ser uma data anterior ou igual a :date.',
    'between' => [
        'array' => 'O atributo :attribute deve ter entre :min e :max itens.',
        'file' => 'O atributo :attribute deve ter entre :min e :max kilobytes.',
        'numeric' => 'O atributo :attribute deve estar entre :min e :max.',
        'string' => 'O atributo :attribute deve ter entre :min e :max caracteres.',
    ],
    'boolean' => 'O atributo :attribute deve ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação do atributo :attribute não coincide.',
    'current_password' => 'A senha atual está incorreta.',
    'date' => 'O atributo :attribute não é uma data válida.',
    'date_equals' => 'O atributo :attribute deve ser uma data igual a :date.',
    'date_format' => 'O atributo :attribute não coincide com o formato :format.',
    'decimal' => 'O atributo :attribute deve ter :decimal casas decimais.',
    'declined' => 'O atributo :attribute deve ser recusado.',
    'declined_if' => 'O atributo :attribute deve ser recusado quando :other for :value.',
    'different' => 'Os atributos :attribute e :other devem ser diferentes.',
    'digits' => 'O atributo :attribute deve ter :digits dígitos.',
    'digits_between' => 'O atributo :attribute deve ter entre :min e :max dígitos.',
    'dimensions' => 'O atributo :attribute possui dimensões de imagem inválidas.',
    'distinct' => 'O atributo :attribute tem um valor duplicado.',
    'doesnt_end_with' => 'O atributo :attribute não pode terminar com nenhum dos seguintes: :values.',
    'doesnt_start_with' => 'O atributo :attribute não pode começar com nenhum dos seguintes: :values.',
    'email' => 'O atributo :attribute deve ser um endereço de e-mail válido.',
    'ends_with' => 'O atributo :attribute deve terminar com um dos seguintes: :values.',
    'enum' => 'O valor selecionado para :attribute é inválido.',
    'exists' => 'O valor selecionado para :attribute é inválido.',
    'file' => 'O atributo :attribute deve ser um arquivo.',
    'filled' => 'O atributo :attribute deve ter um valor.',
    'gt' => [
        'array' => 'O atributo :attribute deve ter mais de :value itens.',
        'file' => 'O atributo :attribute deve ser maior que :value kilobytes.',
        'numeric' => 'O atributo :attribute deve ser maior que :value.',
        'string' => 'O atributo :attribute deve ser maior que :value caracteres.',
    ],
    'gte' => [
        'array' => 'O atributo :attribute deve ter :value itens ou mais.',
        'file' => 'O atributo :attribute deve ser maior ou igual a :value kilobytes.',
        'numeric' => 'O atributo :attribute deve ser maior ou igual a :value.',
        'string' => 'O atributo :attribute deve ter :value caracteres ou mais.',
    ],
    'image' => 'O atributo :attribute deve ser uma imagem.',
    'in' => 'O valor selecionado para :attribute é inválido.',
    'in_array' => 'O atributo :attribute não existe em :other.',
    'integer' => 'O atributo :attribute deve ser um número inteiro.',
    'ip' => 'O atributo :attribute deve ser um endereço IP válido.',
    'ipv4' => 'O atributo :attribute deve ser um endereço IPv4 válido.',
    'ipv6' => 'O atributo :attribute deve ser um endereço IPv6 válido.',
    'json' => 'O atributo :attribute deve ser uma string JSON válida.',
    'lowercase' => 'O atributo :attribute deve estar em minúsculas.',
    'lt' => [
        'array' => 'O atributo :attribute deve ter menos de :value itens.',
        'file' => 'O atributo :attribute deve ser menor que :value kilobytes.',
        'numeric' => 'O atributo :attribute deve ser menor que :value.',
        'string' => 'O atributo :attribute deve ter menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'O atributo :attribute não deve ter mais de :value itens.',
        'file' => 'O atributo :attribute deve ser menor ou igual a :value kilobytes.',
        'numeric' => 'O atributo :attribute deve ser menor ou igual a :value.',
        'string' => 'O atributo :attribute deve ter no máximo :value caracteres.',
    ],
    'mac_address' => 'O atributo :attribute deve ser um endereço MAC válido.',
    'max' => [
        'array' => 'O atributo :attribute não deve ter mais de :max itens.',
        'file' => 'O atributo :attribute não deve ser maior que :max kilobytes.',
        'numeric' => 'O atributo :attribute não deve ser maior que :max.',
        'string' => 'O atributo :attribute não deve ser maior que :max caracteres.',
    ],
    'max_digits' => 'O atributo :attribute não deve ter mais de :max dígitos.',
    'mimes' => 'O atributo :attribute deve ser um arquivo do tipo: :values.',
    'mimetypes' => 'O atributo :attribute deve ser um arquivo do tipo: :values.',
    'min' => [
        'array' => 'O atributo :attribute deve ter pelo menos :min itens.',
        'file' => 'O atributo :attribute deve ter pelo menos :min kilobytes.',
        'numeric' => 'O atributo :attribute deve ser no mínimo :min.',
        'string' => 'O atributo :attribute deve ter no mínimo :min caracteres.',
    ],
    'min_digits' => 'O atributo :attribute deve ter pelo menos :min dígitos.',
    'multiple_of' => 'O atributo :attribute deve ser um múltiplo de :value.',
    'not_in' => 'O valor selecionado para :attribute é inválido.',
    'not_regex' => 'O formato do atributo :attribute é inválido.',
    'numeric' => 'O atributo :attribute deve ser um número.',
    'password' => [
        'letters' => 'O atributo :attribute deve conter pelo menos uma letra.',
        'mixed' => 'O atributo :attribute deve conter pelo menos uma letra maiúscula e uma minúscula.',
        'numbers' => 'O atributo :attribute deve conter pelo menos um número.',
        'symbols' => 'O atributo :attribute deve conter pelo menos um símbolo.',
        'uncompromised' => 'O :attribute fornecido apareceu em uma violação de dados. Por favor, escolha um :attribute diferente.',
    ],
    'present' => 'O atributo :attribute deve estar presente.',
    'prohibited' => 'O atributo :attribute é proibido.',
    'prohibited_if' => 'O atributo :attribute é proibido quando :other é :value.',
    'prohibited_unless' => 'O atributo :attribute é proibido a menos que :other esteja em :values.',
    'prohibits' => 'O atributo :attribute proíbe que :other esteja presente.',
    'regex' => 'O formato do atributo :attribute é inválido.',
    'required' => 'O atributo :attribute é obrigatório.',
    'required_array_keys' => 'O atributo :attribute deve conter entradas para: :values.',
    'required_if' => 'O atributo :attribute é obrigatório quando :other é :value.',
    'required_if_accepted' => 'O atributo :attribute é obrigatório quando :other é aceito.',
    'required_unless' => 'O atributo :attribute é obrigatório a menos que :other esteja em :values.',
    'required_with' => 'O atributo :attribute é obrigatório quando :values está presente.',
    'required_with_all' => 'O atributo :attribute é obrigatório quando :values estão presentes.',
    'required_without' => 'O atributo :attribute é obrigatório quando :values não está presente.',
    'required_without_all' => 'O atributo :attribute é obrigatório quando nenhum dos :values está presente.',
    'same' => 'Os atributos :attribute e :other devem ser iguais.',
    'size' => [
        'array' => 'O atributo :attribute deve conter :size itens.',
        'file' => 'O atributo :attribute deve ter :size kilobytes.',
        'numeric' => 'O atributo :attribute deve ser :size.',
        'string' => 'O atributo :attribute deve ter :size caracteres.',
    ],
    'starts_with' => 'O atributo :attribute deve começar com um dos seguintes: :values.',
    'string' => 'O atributo :attribute deve ser uma string.',
    'timezone' => 'O atributo :attribute deve ser um fuso horário válido.',
    'unique' => 'O atributo :attribute já foi utilizado.',
    'uploaded' => 'Falha no upload do atributo :attribute.',
    'uppercase' => 'O atributo :attribute deve estar em maiúsculas.',
    'url' => 'O atributo :attribute deve ser uma URL válida.',
    'ulid' => 'O atributo :attribute deve ser um ULID válido.',
    'uuid' => 'O atributo :attribute deve ser um UUID válido.',

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

];
