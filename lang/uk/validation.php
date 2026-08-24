<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Наступні мовні рядки містять стандартні повідомлення про помилки,
    | які використовуються класом валідатора.
    |
    */

    'accepted' => 'Поле :attribute має бути прийняте.',
    'accepted_if' => 'Поле :attribute має бути прийняте, якщо :other має значення :value.',
    'active_url' => 'Поле :attribute має містити дійсну URL-адресу.',
    'after' => 'Поле :attribute має містити дату після :date.',
    'after_or_equal' => 'Поле :attribute має містити дату після або рівну :date.',
    'alpha' => 'Поле :attribute має містити лише літери.',
    'alpha_dash' => 'Поле :attribute може містити лише літери, цифри, дефіси та символи підкреслення.',
    'alpha_num' => 'Поле :attribute може містити лише літери та цифри.',
    'any_of' => 'Поле :attribute має некоректне значення.',
    'array' => 'Поле :attribute має бути масивом.',
    'ascii' => 'Поле :attribute може містити лише однобайтові літери, цифри та символи.',
    'before' => 'Поле :attribute має містити дату до :date.',
    'before_or_equal' => 'Поле :attribute має містити дату до або рівну :date.',

    'between' => [
        'array' => 'Поле :attribute має містити від :min до :max елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути від :min до :max КБ.',
        'numeric' => 'Значення поля :attribute має бути від :min до :max.',
        'string' => 'Поле :attribute має містити від :min до :max символів.',
    ],

    'boolean' => 'Поле :attribute має містити значення true або false.',
    'can' => 'Поле :attribute містить недозволене значення.',
    'confirmed' => 'Підтвердження поля :attribute не збігається.',
    'contains' => 'У полі :attribute відсутнє необхідне значення.',
    'current_password' => 'Неправильний пароль.',
    'date' => 'Поле :attribute має містити коректну дату.',
    'date_equals' => 'Поле :attribute має містити дату, що дорівнює :date.',
    'date_format' => 'Поле :attribute має відповідати формату :format.',
    'decimal' => 'Поле :attribute має містити :decimal знаків після коми.',
    'declined' => 'Поле :attribute має бути відхилене.',
    'declined_if' => 'Поле :attribute має бути відхилене, якщо :other має значення :value.',
    'different' => 'Поле :attribute та :other мають відрізнятися.',
    'digits' => 'Поле :attribute має містити :digits цифр.',
    'digits_between' => 'Поле :attribute має містити від :min до :max цифр.',
    'dimensions' => 'Поле :attribute містить зображення з некоректними розмірами.',
    'distinct' => 'Поле :attribute містить дубльоване значення.',
    'doesnt_contain' => 'Поле :attribute не повинно містити жодного з таких значень: :values.',
    'doesnt_end_with' => 'Поле :attribute не повинно закінчуватися одним із таких значень: :values.',
    'doesnt_start_with' => 'Поле :attribute не повинно починатися з одного з таких значень: :values.',
    'email' => 'Поле :attribute має містити коректну електронну адресу.',
    'encoding' => 'Поле :attribute має бути закодоване у :encoding.',
    'ends_with' => 'Поле :attribute має закінчуватися одним із таких значень: :values.',
    'enum' => 'Вибране значення поля :attribute є некоректним.',
    'exists' => 'Вибране значення поля :attribute є некоректним.',
    'extensions' => 'Файл у полі :attribute має мати одне з таких розширень: :values.',
    'file' => 'Поле :attribute має містити файл.',
    'filled' => 'Поле :attribute має містити значення.',

    'gt' => [
        'array' => 'Поле :attribute має містити більше :value елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути більшим за :value КБ.',
        'numeric' => 'Значення поля :attribute має бути більшим за :value.',
        'string' => 'Поле :attribute має містити більше :value символів.',
    ],

    'gte' => [
        'array' => 'Поле :attribute має містити :value або більше елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути не меншим за :value КБ.',
        'numeric' => 'Значення поля :attribute має бути більшим або дорівнювати :value.',
        'string' => 'Поле :attribute має містити не менше :value символів.',
    ],

    'hex_color' => 'Поле :attribute має містити коректний шістнадцятковий колір.',
    'image' => 'Поле :attribute має містити зображення.',
    'in' => 'Вибране значення поля :attribute є некоректним.',
    'in_array' => 'Поле :attribute має міститися в :other.',
    'in_array_keys' => 'Поле :attribute має містити принаймні один із таких ключів: :values.',
    'integer' => 'Поле :attribute має містити ціле число.',
    'ip' => 'Поле :attribute має містити коректну IP-адресу.',
    'ipv4' => 'Поле :attribute має містити коректну IPv4-адресу.',
    'ipv6' => 'Поле :attribute має містити коректну IPv6-адресу.',
    'json' => 'Поле :attribute має містити коректний JSON-рядок.',
    'list' => 'Поле :attribute має бути списком.',
    'lowercase' => 'Поле :attribute має містити лише символи в нижньому регістрі.',

    'lt' => [
        'array' => 'Поле :attribute має містити менше :value елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути меншим за :value КБ.',
        'numeric' => 'Значення поля :attribute має бути меншим за :value.',
        'string' => 'Поле :attribute має містити менше :value символів.',
    ],

    'lte' => [
        'array' => 'Поле :attribute не повинно містити більше :value елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути не більшим за :value КБ.',
        'numeric' => 'Значення поля :attribute має бути меншим або дорівнювати :value.',
        'string' => 'Поле :attribute має містити не більше :value символів.',
    ],

    'mac_address' => 'Поле :attribute має містити коректну MAC-адресу.',

    'max' => [
        'array' => 'Поле :attribute не повинно містити більше :max елементів.',
        'file' => 'Розмір файлу в полі :attribute не повинен перевищувати :max КБ.',
        'numeric' => 'Значення поля :attribute не повинно перевищувати :max.',
        'string' => 'Поле :attribute не повинно містити більше :max символів.',
    ],

    'max_digits' => 'Поле :attribute не повинно містити більше :max цифр.',
    'mimes' => 'Файл у полі :attribute має бути одного з таких типів: :values.',
    'mimetypes' => 'Файл у полі :attribute має бути одного з таких типів: :values.',

    'min' => [
        'array' => 'Поле :attribute має містити щонайменше :min елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути не меншим за :min КБ.',
        'numeric' => 'Значення поля :attribute має бути не меншим за :min.',
        'string' => 'Поле :attribute має містити щонайменше :min символів.',
    ],

    'min_digits' => 'Поле :attribute має містити щонайменше :min цифр.',
    'missing' => 'Поле :attribute не повинно бути присутнім.',
    'missing_if' => 'Поле :attribute не повинно бути присутнім, якщо :other має значення :value.',
    'missing_unless' => 'Поле :attribute не повинно бути присутнім, якщо :other не має значення :value.',
    'missing_with' => 'Поле :attribute не повинно бути присутнім, якщо :values присутнє.',
    'missing_with_all' => 'Поле :attribute не повинно бути присутнім, якщо :values присутні.',
    'multiple_of' => 'Поле :attribute має бути кратним :value.',
    'not_in' => 'Вибране значення поля :attribute є некоректним.',
    'not_regex' => 'Формат поля :attribute є некоректним.',
    'numeric' => 'Поле :attribute має містити число.',

    'password' => [
        'letters' => 'Поле :attribute має містити щонайменше одну літеру.',
        'mixed' => 'Поле :attribute має містити щонайменше одну велику та одну малу літеру.',
        'numbers' => 'Поле :attribute має містити щонайменше одну цифру.',
        'symbols' => 'Поле :attribute має містити щонайменше один символ.',
        'uncompromised' => 'Значення :attribute було виявлено у витоку даних. Будь ласка, виберіть інше значення :attribute.',
    ],

    'present' => 'Поле :attribute має бути присутнім.',
    'present_if' => 'Поле :attribute має бути присутнім, якщо :other має значення :value.',
    'present_unless' => 'Поле :attribute має бути присутнім, якщо :other не має значення :value.',
    'present_with' => 'Поле :attribute має бути присутнім, якщо :values присутнє.',
    'present_with_all' => 'Поле :attribute має бути присутнім, якщо :values присутні.',
    'prohibited' => 'Поле :attribute заборонено.',
    'prohibited_if' => 'Поле :attribute заборонено, якщо :other має значення :value.',
    'prohibited_if_accepted' => 'Поле :attribute заборонено, якщо :other прийнято.',
    'prohibited_if_declined' => 'Поле :attribute заборонено, якщо :other відхилено.',
    'prohibited_unless' => 'Поле :attribute заборонено, якщо :other не входить до :values.',
    'prohibits' => 'Поле :attribute забороняє наявність поля :other.',
    'regex' => 'Формат поля :attribute є некоректним.',
    'required' => 'Поле :attribute є обов’язковим.',
    'required_array_keys' => 'Поле :attribute має містити такі ключі: :values.',
    'required_if' => 'Поле :attribute є обов’язковим, якщо :other має значення :value.',
    'required_if_accepted' => 'Поле :attribute є обов’язковим, якщо :other прийнято.',
    'required_if_declined' => 'Поле :attribute є обов’язковим, якщо :other відхилено.',
    'required_unless' => 'Поле :attribute є обов’язковим, якщо :other не входить до :values.',
    'required_with' => 'Поле :attribute є обов’язковим, якщо :values присутнє.',
    'required_with_all' => 'Поле :attribute є обов’язковим, якщо :values присутні.',
    'required_without' => 'Поле :attribute є обов’язковим, якщо :values відсутнє.',
    'required_without_all' => 'Поле :attribute є обов’язковим, якщо жодне з :values не вказано.',
    'same' => 'Поле :attribute має збігатися з :other.',

    'size' => [
        'array' => 'Поле :attribute має містити :size елементів.',
        'file' => 'Розмір файлу в полі :attribute має становити :size КБ.',
        'numeric' => 'Значення поля :attribute має дорівнювати :size.',
        'string' => 'Поле :attribute має містити :size символів.',
    ],

    'starts_with' => 'Поле :attribute має починатися з одного з таких значень: :values.',
    'string' => 'Поле :attribute має бути рядком.',
    'timezone' => 'Поле :attribute має містити коректний часовий пояс.',
    'unique' => 'Таке значення поля :attribute вже використовується.',
    'uploaded' => 'Не вдалося завантажити поле :attribute.',
    'uppercase' => 'Поле :attribute має містити лише символи у верхньому регістрі.',
    'url' => 'Поле :attribute має містити коректну URL-адресу.',
    'ulid' => 'Поле :attribute має містити коректний ULID.',
    'uuid' => 'Поле :attribute має містити коректний UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Тут можна вказати власні повідомлення про помилки валідації
    | для конкретних полів та правил.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'Власне повідомлення про помилку.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Тут можна вказати зрозумілі для користувача назви полів.
    |
    */

    'attributes' => [
        'name' => 'ім’я',
        'surname' => 'прізвище',
        'middle_name' => 'по батькові',
        'email' => 'електронна пошта',
        'phone' => 'номер телефону',
        'password' => 'пароль',
        'password_confirmation' => 'підтвердження пароля',
        'dob' => 'дата народження',
        'sex' => 'стать',
        'city_id' => 'місто',
        'about' => 'про себе',
    ],

];
