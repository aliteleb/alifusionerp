<?php

namespace Modules\Core\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Personal Information'))
                            ->description(__('Basic user personal details'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Full Name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('employment_id')
                                    ->label(__('Employment ID'))
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),

                                TextInput::make('email')
                                    ->label(__('Email Address'))
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('password')
                                    ->label(__('Password'))
                                    ->password()
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->helperText(__('Minimum 8 characters'))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null),

                                PhoneInput::make('phone')
                                    ->label(__('Phone'))
                                    ->required()
                                    ->placeholder(__('Enter phone number'))
                                    ->helperText(__('Enter phone number with country code'))
                                    ->prefixIcon('heroicon-o-phone')
                                    ->unique(\Modules\Core\Entities\User::class, 'phone', ignoreRecord: true)
                                    ->columnSpan(1)
                                    ->countryStatePath('country_code')
                                    ->defaultCountry('IQ') // Default to Iraq
                                    ->displayNumberFormat(PhoneInputNumberType::NATIONAL)
                                    ->inputNumberFormat(PhoneInputNumberType::INTERNATIONAL),

                                TextInput::make('skype_id')
                                    ->label(__('Skype ID'))
                                    ->maxLength(100),

                                SpatieMediaLibraryFileUpload::make('cover')
                                    ->label(__('Cover Photo'))
                                    ->helperText(__('Upload a cover photo for this user'))
                                    ->collection('cover')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->maxSize(5120) // 5MB
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Section::make(__('Options'))
                            ->description(__('User settings and configurations'))
                            ->columns(2)
                            ->schema([
                                Toggle::make('is_department_head')
                                    ->label(__('Is Department Head?'))
                                    ->helperText(__('Mark if this user is the head of their department'))
                                    ->default(false),

                                Toggle::make('is_hq')
                                    ->label(__('Is HQ?'))
                                    ->helperText(__('Mark if this user is from headquarters'))
                                    ->default(false),

                                CheckboxList::make('roles')
                                    ->label(__('Roles'))
                                    ->helperText(__('Select roles for this user'))
                                    ->relationship('roles', 'name')
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Organization'))
                            ->description(__('User organizational details'))
                            ->schema([

                                Select::make('branch_id')
                                    ->label(__('Primary Branch'))
                                    ->helperText(__('Select the branch this user belongs to'))
                                    ->required()
                                    ->options(fn () => \App\Core\Models\Branch::active()->get()->mapWithKeys(fn ($branch) => [$branch->id => $branch->getTranslation('name', app()->getLocale())]))
                                    ->preload()
                                    ->native(false)
                                    ->searchable(),

                                Select::make('department_id')
                                    ->label(__('Primary Department'))
                                    ->helperText(__('Select the primary department this user belongs to'))
                                    ->relationship(
                                        'department',
                                        'name',
                                        fn ($query) => $query->where('is_active', true)->orderByJsonColumn('name')
                                    )
                                    ->preload()
                                    ->native(false)
                                    ->searchable(),

                                Select::make('branches')
                                    ->label(__('Branches'))
                                    ->helperText(__('Select all branches this user can access'))
                                    ->required()
                                    ->options(fn () => \App\Core\Models\Branch::active()->get()->mapWithKeys(fn ($branch) => [$branch->id => $branch->getTranslation('name', app()->getLocale())]))
                                    ->multiple()
                                    ->preload()
                                    ->native(false)
                                    ->searchable()
                                    ->dehydrated(false)
                                    ->saveRelationshipsUsing(function ($component, $state) {
                                        $component->getRecord()->branches()->sync($state ?? []);
                                    })
                                    ->loadStateFromRelationshipsUsing(function ($component) {
                                        $component->state($component->getRecord()->branches->pluck('id')->toArray());
                                    }),

                                Select::make('departments')
                                    ->label(__('Departments'))
                                    ->helperText(__('Select all departments this user belongs to'))
                                    ->options(fn () => \App\Core\Models\Department::active()->get()->mapWithKeys(fn ($department) => [$department->id => $department->getTranslation('name', app()->getLocale())]))
                                    ->multiple()
                                    ->preload()
                                    ->native(false)
                                    ->searchable()
                                    ->dehydrated(false)
                                    ->saveRelationshipsUsing(function ($component, $state) {
                                        $component->getRecord()->departments()->sync($state ?? []);
                                    })
                                    ->loadStateFromRelationshipsUsing(function ($component) {
                                        $component->state($component->getRecord()->departments->pluck('id')->toArray());
                                    }),

                            ]),

                    ]),
            ]);
    }
}


