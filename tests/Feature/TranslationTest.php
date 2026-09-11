<?php

function flattenJsonKeys(array $array, string $prefix = ''): array
{
    $keys = [];

    foreach ($array as $key => $value) {
        $full = $prefix === '' ? $key : $prefix.'.'.$key;

        if (is_array($value)) {
            $keys = array_merge($keys, flattenJsonKeys($value, $full));
        } else {
            $keys[] = $full;
        }
    }

    return $keys;
}

it('defines the same translation keys in english and german', function () {
    $en = json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);
    $de = json_decode(file_get_contents(lang_path('de.json')), true, flags: JSON_THROW_ON_ERROR);

    expect(flattenJsonKeys($en))->toBe(flattenJsonKeys($de));
});
