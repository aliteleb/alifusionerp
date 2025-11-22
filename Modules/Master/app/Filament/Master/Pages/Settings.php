<?php

namespace Modules\Master\Filament\Master\Pages;

use Modules\Core\Entities\Setting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.master.pages.settings';

    protected static ?int $navigationSort = 10;

    public Authenticatable $user;

    public ?array $data = [];

    public function getTitle(): string|Htmlable
    {
        return __('Master Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'master-settings';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Tabs')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('General')->label(__('General Information'))->icon('heroicon-o-cog-6-tooth')->schema($this->getGeneralSchema()),
                    Tab::make('System')->label(__('System Configuration'))->icon('heroicon-o-cog-8-tooth')->schema($this->getSystemSchema()),
                    Tab::make('Copyright')->label(__('Copyright & Legal'))->icon('heroicon-o-shield-check')->schema($this->getCopyrightSchema()),
                    Tab::make('Email')->label(__('Email Settings'))->icon('heroicon-o-envelope')->schema($this->getEmailSchema()),
                ])->persistTab(),
        ])->statePath('data');
    }

    private function getGeneralSchema(): array
    {
        return [
            Section::make(__('Master Application Details'))
                ->schema([
                    TextInput::make('app_name')
                        ->label(__('Master Application Name'))
                        ->default('Ali Fusion ERP Master')
                        ->required(),
                    TextInput::make('app_description')
                        ->label(__('Description'))
                        ->default('Ali Fusion ERP - Master Panel'),
                    TextInput::make('company_name')
                        ->label(__('Company Name'))
                        ->required(),
                    TextInput::make('company_email')
                        ->label(__('Company Email'))
                        ->email()
                        ->required(),
                    TextInput::make('company_phone')
                        ->label(__('Company Phone'))
                        ->tel(),
                    TextInput::make('company_website')
                        ->label(__('Company Website'))
                        ->url(),
                    Textarea::make('company_address')
                        ->label(__('Company Address')),
                    TextInput::make('support_email')
                        ->label(__('Support Email'))
                        ->email()
                        ->helperText(__('Email for tenant support requests')),
                    TextInput::make('support_phone')
                        ->label(__('Support Phone'))
                        ->tel()
                        ->helperText(__('Phone for tenant support')),
                ])->columns(2),

            Section::make(__('Master Panel Branding'))
                ->schema([
                    FileUpload::make('logo')
                        ->label(__('Master Panel Logo'))
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->helperText(__('Recommended size: 200x50 pixels')),
                    FileUpload::make('favicon')
                        ->label(__('Master Panel Favicon'))
                        ->image()
                        ->disk('public')
                        ->helperText(__('Recommended size: 32x32 pixels')),
                    FileUpload::make('login_image')
                        ->label(__('Master Login Image'))
                        ->image()
                        ->disk('public')
                        ->helperText(__('Recommended size: 400x300 pixels')),
                ])->columns(2),

            Section::make(__('Localization'))
                ->schema([
                    TagsInput::make('locales')
                        ->label(__('Available Locales'))
                        ->placeholder(__('Add locale codes (e.g., en, ar, ku)'))
                        ->default(['en', 'ar', 'ku'])
                        ->helperText(__('List of locale codes available for the master panel and tenants')),
                ])->columns(1),
        ];
    }

    private function getSystemSchema(): array
    {
        return [
            Section::make(__('System Configuration'))
                ->schema([
                    TextInput::make('max_tenants')
                        ->label(__('Maximum Tenants'))
                        ->numeric()
                        ->default(100)
                        ->helperText(__('Maximum number of tenant facilities allowed')),
                    Toggle::make('auto_tenant_creation')
                        ->label(__('Auto Tenant Database Creation'))
                        ->default(true)
                        ->helperText(__('Automatically create database when a new facility is added')),
                    Toggle::make('auto_migration')
                        ->label(__('Auto Migration for New Tenants'))
                        ->default(true)
                        ->helperText(__('Automatically run migrations for new tenant databases')),
                    Toggle::make('backup_notifications')
                        ->label(__('Backup Notifications'))
                        ->default(true)
                        ->helperText(__('Send notifications for backup operations')),
                    TextInput::make('backup_retention_days')
                        ->label(__('Backup Retention (Days)'))
                        ->numeric()
                        ->default(30)
                        ->helperText(__('Number of days to keep backup files')),
                    Toggle::make('maintenance_mode')
                        ->label(__('Global Maintenance Mode'))
                        ->default(false)
                        ->helperText(__('Enable maintenance mode for all tenant panels')),
                    Textarea::make('maintenance_message')
                        ->label(__('Maintenance Message'))
                        ->default(__('System is currently under maintenance. Please try again later.'))
                        ->helperText(__('Message to show when maintenance mode is enabled')),
                ])->columns(2),

            Section::make(__('Default Tenant Settings'))
                ->schema([
                    TextInput::make('default_currency')
                        ->label(__('Default Currency'))
                        ->default('USD')
                        ->helperText(__('Default currency for new tenants')),
                    Select::make('default_timezone')
                        ->label(__('Default Timezone'))
                        ->options([
                            'UTC' => 'UTC',
                            'America/New_York' => 'America/New_York',
                            'Europe/London' => 'Europe/London',
                            'Asia/Baghdad' => 'Asia/Baghdad',
                            'Asia/Dubai' => 'Asia/Dubai',
                            'Asia/Riyadh' => 'Asia/Riyadh',
                        ])
                        ->default('UTC')
                        ->searchable()
                        ->helperText(__('Default timezone for new tenants')),
                    Select::make('default_language')
                        ->label(__('Default Language'))
                        ->options([
                            'en' => __('English'),
                            'ar' => __('Arabic'),
                            'ku' => __('Kurdish'),
                        ])
                        ->default('en')
                        ->helperText(__('Default language for new tenants')),
                ])->columns(2),
        ];
    }

    private function getEmailSchema(): array
    {
        return [
            Section::make([
                TextInput::make('mail_mailer')
                    ->label(__('Mail Driver'))
                    ->placeholder(__('smtp'))
                    ->helperText(__('The mail service driver (smtp, sendmail, mailgun, etc.)'))
                    ->required(),
                TextInput::make('mail_host')
                    ->label(__('Mail Host'))
                    ->placeholder(__('smtp.example.com'))
                    ->helperText(__('The mail server host address'))
                    ->required(),
                TextInput::make('mail_port')
                    ->label(__('Mail Port'))
                    ->placeholder(__('587'))
                    ->helperText(__('The mail server port (usually 587 for TLS or 465 for SSL)'))
                    ->numeric()
                    ->required(),
                TextInput::make('mail_username')
                    ->label(__('Mail Username'))
                    ->placeholder(__('username@example.com'))
                    ->helperText(__('Your mail server username'))
                    ->required(),
                TextInput::make('mail_password')
                    ->label(__('Mail Password'))
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => $state ? $state : null)
                    ->helperText(__('Your mail server password'))
                    ->required(),
                TextInput::make('mail_encryption')
                    ->label(__('Mail Encryption'))
                    ->placeholder(__('tls'))
                    ->helperText(__('The encryption type (tls, ssl, or null)'))
                    ->required(),
                TextInput::make('mail_from_address')
                    ->label(__('Mail From Address'))
                    ->placeholder(__('noreply@example.com'))
                    ->helperText(__('The email address that will appear in the `From` field'))
                    ->email()
                    ->required(),
                TextInput::make('mail_from_name')
                    ->label(__('Mail From Name'))
                    ->placeholder(__('Ali Fusion ERP'))
                    ->helperText(__('The name that will appear in the `From` field'))
                    ->required(),
            ])->compact()->columns(2)->heading(fn () => __('Master SMTP Settings')),
        ];
    }

    private function getCopyrightSchema(): array
    {
        return [
            Section::make(__('Global Copyright Information'))
                ->description(__('Configure copyright and legal information for all tenant facilities'))
                ->schema([
                    TextInput::make('global_copyright_text')
                        ->label(__('Copyright Text'))
                        ->placeholder(__('Copyright notice for your organization'))
                        ->helperText(__('This text will appear in the footer of all tenant applications'))
                        ->default('Ali Fusion ERP'),
                    TextInput::make('global_legal_entity_name')
                        ->label(__('Legal Entity Name'))
                        ->placeholder(__('Your Company Legal Name'))
                        ->helperText(__('The legal name of your organization for official documents'))
                        ->required(),
                    TextInput::make('global_registration_number')
                        ->label(__('Registration Number'))
                        ->placeholder(__('Company registration or license number'))
                        ->helperText(__('Official registration or license number')),
                ])->columns(1),

            Section::make(__('Global Document Protection'))
                ->description(__('Settings for protecting documents across all tenant facilities'))
                ->schema([
                    Toggle::make('global_enable_document_watermark')
                        ->label(__('Enable Document Watermark'))
                        ->helperText(__('Add company watermark to all generated documents across all tenants'))
                        ->default(true),
                    Textarea::make('global_document_confidentiality_notice')
                        ->label(__('Document Confidentiality Notice'))
                        ->rows(3)
                        ->placeholder(__('Enter confidentiality notice for documents...'))
                        ->helperText(__('This notice will appear on PDFs and official documents across all tenants'))
                        ->default('This document is confidential and proprietary. Unauthorized distribution is prohibited.'),
                ])->columns(1),

            Section::make(__('System Information'))
                ->description(__('Global version and licensing information'))
                ->schema([
                    TextInput::make('global_system_version')
                        ->label(__('System Version'))
                        ->placeholder(__('v1.0.0'))
                        ->helperText(__('Current version of your HRMS implementation'))
                        ->default(config('app.version', '1.0.0')),
                    Textarea::make('global_license_information')
                        ->label(__('License Information'))
                        ->rows(3)
                        ->placeholder(__('Enter software license information...'))
                        ->helperText(__('Software licensing and usage terms for all tenants'))
                        ->default('This software is licensed for use by the organization. All rights reserved.'),
                ])->columns(1),
        ];
    }

    public function save(): void
    {
        $this->callHook('beforeValidate');

        $fields = collect($this->form->getFlatFields(true));
        $fieldsWithNestedFields = $fields->filter(fn ($field) => count($field->getDefaultChildComponents()) > 0);

        $fieldsWithNestedFields->each(function ($fieldWithNestedFields, string $fieldWithNestedFieldsKey) use (&$fields) {
            $fields = $fields->reject(function ($field, string $fieldKey) use ($fieldWithNestedFieldsKey) {
                return Str::startsWith($fieldKey, $fieldWithNestedFieldsKey.'.');
            });
        });

        $data = $fields->mapWithKeys(function ($field, string $fieldKey) {
            return [$fieldKey => data_get($this->form->getState(), $fieldKey)];
        })->toArray();

        $this->callHook('afterValidate');

        $this->callHook('beforeSave');

        // Save settings to master database (not tenant-specific)
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                [
                    'key' => $key,
                ],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        // Clear master settings cache
        cache()->forget('settings');

        Notification::make()->success()->title(fn () => __('Master settings saved successfully'))->send();
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        // Get settings from master database
        $data = Setting::get()->pluck('value', 'key')->toArray();
        $this->callHook('beforeFill');

        // Loop through each item in the array and decode if it's a valid JSON string
        foreach ($data as $key => $value) {
            if ($this->isJson($value)) {
                $data[$key] = json_decode($value, true);
            }
        }

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    private function isJson(?string $string): bool
    {
        json_decode($string);

        return json_last_error() == JSON_ERROR_NONE;
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')->label(fn () => __('Save changes'))->submit('save'),
        ];
    }
}

