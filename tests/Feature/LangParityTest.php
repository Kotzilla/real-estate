<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * English and Thai must have the same translation files and keys: APP_FALLBACK_LOCALE is th, so a key missing in
 * one language shows as its raw name ("validation.required") instead of falling back.
 */
class LangParityTest extends TestCase
{
    /**
     * A translation array's keys as dotted paths
     */
    private static function keys(array $lines, string $prefix = ''): array
    {
        $keys = [];
        foreach ($lines as $key => $value) {
            array_push($keys, ...(is_array($value) && $value !== [] ? self::keys($value, "$prefix$key.") : ["$prefix$key"]));
        }

        return $keys;
    }

    public function test_english_and_thai_have_the_same_files_and_keys(): void
    {
        $files = fn (string $locale) => array_map('basename', glob(lang_path("$locale/*.php")));
        $this->assertEqualsCanonicalizing($files('en'), $files('th'), 'A translation file exists in one language only');

        foreach ($files('en') as $file) {
            $en = self::keys(require lang_path("en/$file"));
            $th = self::keys(require lang_path("th/$file"));
            $this->assertSame([], array_values(array_diff($en, $th)), "lang/th/$file is missing keys");
            $this->assertSame([], array_values(array_diff($th, $en)), "lang/en/$file is missing keys");
        }

        $json = fn (string $locale) => array_keys(json_decode(file_get_contents(lang_path("$locale.json")), true));
        $this->assertEqualsCanonicalizing($json('en'), $json('th'), 'en.json and th.json differ');
    }
}
