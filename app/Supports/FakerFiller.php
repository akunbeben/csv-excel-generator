<?php

namespace App\Supports;

use Filament\Support\Contracts\HasLabel;

enum FakerFiller: int implements HasLabel
{
    case Name = 1;
    case Email = 2;
    case Password = 3;
    case PhoneNumber = 4;
    case Address = 5;
    case Sentences = 6;
    case Number = 7;
    case Date = 8;
    case CompanyName = 9;

    public function fill(string $locale = 'en_US'): string | int
    {
        $faker = fake($locale);

        return match ($this) {
            self::Name => $faker->name(),
            self::Email => $faker->email(),
            self::Password => $faker->password(),
            self::PhoneNumber => $faker->phoneNumber(),
            self::Address => $faker->address(),
            self::Sentences => $faker->realText(),
            self::Number => $faker->numberBetween(1, 100),
            self::Date => $faker->date(),
            self::CompanyName => $faker->company(),
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Name => 'Name',
            self::Email => 'Email',
            self::Password => 'Password',
            self::PhoneNumber => 'Phone Number',
            self::Address => 'Address',
            self::Sentences => 'Sentences',
            self::Number => 'Number',
            self::Date => 'Date',
            self::CompanyName => 'Company Name',
        };
    }

    public static function options(): array
    {
        return [
            self::Name->value => 'Name',
            self::Email->value => 'Email',
            self::Password->value => 'Password',
            self::PhoneNumber->value => 'Phone Number',
            self::Address->value => 'Address',
            self::Sentences->value => 'Sentences',
            self::Number->value => 'Number',
            self::Date->value => 'Date',
            self::CompanyName->value => 'Company Name',
        ];
    }
}
