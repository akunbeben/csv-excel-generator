<?php

namespace App\Supports;

enum Locale: string
{
    case en_US = 'en_US';
    case de_DE = 'de_DE';
    case fr_FR = 'fr_FR';
    case es_ES = 'es_ES';
    case it_IT = 'it_IT';
    case pt_PT = 'pt_PT';
    case ru_RU = 'ru_RU';
    case ja_JP = 'ja_JP';
    case ko_KR = 'ko_KR';
    case zh_CN = 'zh_CN';
    case id_ID = 'id_ID';

    public static function options(): array
    {
        return [
            self::en_US->value => 'English (US)',
            self::de_DE->value => 'German (DE)',
            self::fr_FR->value => 'French (FR)',
            self::es_ES->value => 'Spanish (ES)',
            self::it_IT->value => 'Italian (IT)',
            self::pt_PT->value => 'Portuguese (PT)',
            self::ru_RU->value => 'Russian (RU)',
            self::ja_JP->value => 'Japanese (JP)',
            self::ko_KR->value => 'Korean (KR)',
            self::zh_CN->value => 'Chinese (CN)',
            self::id_ID->value => 'Indonesian (ID)',
        ];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::en_US => 'English (US)',
            self::de_DE => 'German (DE)',
            self::fr_FR => 'French (FR)',
            self::es_ES => 'Spanish (ES)',
            self::it_IT => 'Italian (IT)',
            self::pt_PT => 'Portuguese (PT)',
            self::ru_RU => 'Russian (RU)',
            self::ja_JP => 'Japanese (JP)',
            self::ko_KR => 'Korean (KR)',
            self::zh_CN => 'Chinese (CN)',
            self::id_ID => 'Indonesian (ID)',
        };
    }
}
