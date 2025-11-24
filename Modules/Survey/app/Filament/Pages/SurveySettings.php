<?php

namespace Modules\Survey\Filament\Pages;

use App\Enums\SurveyThemeEnum;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Field;
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
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Modules\Core\Entities\Setting;

class SurveySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::Cog6Tooth;

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.admin.pages.settings';

    public Authenticatable $user;

    public ?array $data = [];

    public function getTitle(): string|Htmlable
    {
        return __('Survey Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Survey Settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'survey-settings';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('survey-settings-tabs')
                    ->persistTab()
                    ->tabs([
                        Tab::make('branding')
                            ->id('branding')
                            ->label(__('Branding & Portal'))
                            ->icon('heroicon-o-paint-brush')
                            ->schema($this->getBrandingSchema()),
                        Tab::make('defaults')
                            ->id('defaults')
                            ->label(__('Invitations & Defaults'))
                            ->icon('heroicon-o-document-text')
                            ->schema($this->getDefaultsSchema()),
                        Tab::make('email')
                            ->id('email')
                            ->label(__('Email Delivery'))
                            ->icon('heroicon-o-envelope')
                            ->schema($this->getEmailSchema()),
                        Tab::make('whatsapp')
                            ->id('whatsapp')
                            ->label(__('WhatsApp Automation'))
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema($this->getWhatsAppSchema()),
                    ]),
            ])
            ->statePath('data');
    }

    private function getBrandingSchema(): array
    {
        $localeOptions = [
            'en' => __('English'),
            'ar' => __('Arabic').' (العربية)',
            'ku' => __('Kurdish').' (کوردی)',
        ];

        return [
            Section::make(__('Portal Identity'))
                ->schema([
                    TextInput::make('survey_brand_name')
                        ->label(__('Brand Name'))
                        ->maxLength(120)
                        ->helperText(__('Shown across survey dashboards and communications.')),
                    TextInput::make('survey_dashboard_title')
                        ->label(__('Portal Title'))
                        ->maxLength(120)
                        ->default(__('Customer Experience Portal')),
                    Textarea::make('survey_brand_message')
                        ->label(__('Hero Message'))
                        ->rows(3)
                        ->helperText(__('Short message rendered on the survey landing page.')),
                    Select::make('survey_default_locale')
                        ->label(__('Default Locale'))
                        ->options($localeOptions)
                        ->default('en'),
                    Select::make('survey_enabled_locales')
                        ->label(__('Enabled Locales'))
                        ->multiple()
                        ->options($localeOptions)
                        ->default(['en', 'ar', 'ku'])
                        ->helperText(__('Available languages for survey content and invitations.')),
                    Select::make('survey_theme')
                        ->label(__('Theme'))
                        ->options(SurveyThemeEnum::class)
                        ->default(SurveyThemeEnum::DEFAULT->value),
                    ColorPicker::make('survey_brand_color')
                        ->label(__('Accent Color'))
                        ->default('#7C3AED')
                        ->helperText(__('Used for buttons, charts, and highlights.')),
                    Toggle::make('survey_public_portal_enabled')
                        ->label(__('Enable Public Survey Portal')),
                    Toggle::make('survey_auto_sync_branding')
                        ->label(__('Sync Branding with Core Settings'))
                        ->helperText(__('When enabled, logo/color fall back to organization settings.')),
                ])->columns(2),

            Section::make(__('Assets & Media'))
                ->schema([
                    FileUpload::make('survey_portal_logo')
                        ->label(__('Portal Logo'))
                        ->image()
                        ->imageEditor()
                        ->disk('tenant')
                        ->helperText(__('Displayed on survey pages and emails.')),
                    FileUpload::make('survey_email_banner')
                        ->label(__('Email Banner'))
                        ->image()
                        ->imageEditor()
                        ->disk('tenant')
                        ->helperText(__('Recommended size: 1200x400px.')),
                    FileUpload::make('survey_portal_favicon')
                        ->label(__('Portal Favicon'))
                        ->image()
                        ->disk('tenant')
                        ->helperText(__('Recommended size: 32x32px.')),
                ])->columns(3),
        ];
    }

    private function getDefaultsSchema(): array
    {
        return [
            Section::make(__('Invitation Defaults'))
                ->schema([
                    TextInput::make('survey_invitation_subject')
                        ->label(__('Default Email Subject'))
                        ->maxLength(160)
                        ->placeholder(__('We value your feedback')),
                    Textarea::make('survey_invitation_message')
                        ->label(__('Default Invitation Message'))
                        ->rows(4)
                        ->helperText(__('Supports placeholders: {customer_name}, {survey_title}, {survey_url}, {expires_at}.')),
                    Textarea::make('survey_welcome_message')
                        ->label(__('Welcome Message'))
                        ->rows(3),
                    Textarea::make('survey_thank_you_message')
                        ->label(__('Thank You Message'))
                        ->rows(3),
                    Toggle::make('survey_auto_expire_invitations')
                        ->label(__('Auto-expire invitations'))
                        ->default(true),
                    TextInput::make('survey_invitation_expiry_hours')
                        ->label(__('Expiry window (hours)'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(720)
                        ->default(72),
                    Toggle::make('survey_send_completion_notification')
                        ->label(__('Send completion notifications'))
                        ->default(true),
                    TagsInput::make('survey_notification_recipients')
                        ->label(__('Notification recipients'))
                        ->placeholder(__('ops@example.com'))
                        ->helperText(__('Email addresses separated by Enter or comma.'))
                        ->separator(',')
                        ->distinct(),
                ])->columns(2),
        ];
    }

    private function getEmailSchema(): array
    {
        return [
            Section::make(__('SMTP Configuration'))
                ->schema([
                    TextInput::make('mail_mailer')
                        ->label(__('Mail Driver'))
                        ->placeholder(__('smtp')),
                    TextInput::make('mail_host')
                        ->label(__('Mail Host'))
                        ->placeholder(__('smtp.example.com')),
                    TextInput::make('mail_port')
                        ->label(__('Mail Port'))
                        ->numeric()
                        ->placeholder('587'),
                    TextInput::make('mail_username')
                        ->label(__('Mail Username')),
                    TextInput::make('mail_password')
                        ->label(__('Mail Password / API Key'))
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => $state ?: null),
                    TextInput::make('mail_encryption')
                        ->label(__('Encryption'))
                        ->placeholder(__('tls')),
                    TextInput::make('mail_from_address')
                        ->label(__('From Address'))
                        ->email()
                        ->placeholder(__('noreply@example.com')),
                    TextInput::make('mail_from_name')
                        ->label(__('From Name'))
                        ->placeholder(__('Survey Team')),
                ])->columns(2),
        ];
    }

    private function getWhatsAppSchema(): array
    {
        return [
            Section::make(__('UltraMsg Configuration'))
                ->description(__('Enable automated WhatsApp reminders and invitations.'))
                ->schema([
                    TextInput::make('ultramsg_instance_id')
                        ->label(__('Instance ID')),
                    TextInput::make('ultramsg_token')
                        ->label(__('API Token'))
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => $state ?: null),
                    TextInput::make('ultramsg_timeout')
                        ->label(__('Timeout (seconds)'))
                        ->numeric()
                        ->minValue(5)
                        ->maxValue(120)
                        ->default(30),
                    TextInput::make('ultramsg_retry_attempts')
                        ->label(__('Retry Attempts'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(10)
                        ->default(3),
                    TextInput::make('ultramsg_country_code')
                        ->label(__('Default Country Code'))
                        ->placeholder(__('+964'))
                        ->default('+964')
                        ->regex('/^\+\d{1,4}$/')
                        ->validationMessages([
                            'regex' => __('Country code must start with + followed by 1-4 digits'),
                        ]),
                    Textarea::make('survey_whatsapp_message_template')
                        ->label(__('Invitation Template'))
                        ->rows(4)
                        ->helperText(__('Supports placeholders: {customer_name}, {survey_title}, {survey_url}, {expires_at}.')),
                    Toggle::make('survey_whatsapp_send_on_creation')
                        ->label(__('Send WhatsApp message automatically'))
                        ->default(false),
                ])->columns(2),
        ];
    }

    public function save(): void
    {
        $this->callHook('beforeValidate');

        $fields = collect($this->form->getFlatFields(true));
        $fieldsWithNestedFields = $fields->filter(fn (Field $field) => count($field->getChildComponents()) > 0);

        $fieldsWithNestedFields->each(function (Field $fieldWithNestedFields, string $fieldWithNestedFieldsKey) use (&$fields) {
            $fields = $fields->reject(function (Field $field, string $fieldKey) use ($fieldWithNestedFieldsKey) {
                return Str::startsWith($fieldKey, $fieldWithNestedFieldsKey.'.');
            });
        });

        $data = $fields->mapWithKeys(function (Field $field, string $fieldKey) {
            return [$fieldKey => data_get($this->form->getState(), $fieldKey)];
        })->toArray();

        $this->callHook('afterValidate');

        $this->callHook('beforeSave');

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                [
                    'key' => $key,
                ],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        $subdomain = getCurrentSubdomain();
        $cacheKey = $subdomain ? $subdomain.'_settings' : 'settings';
        cache()->forget($cacheKey);
        settings();

        Notification::make()
            ->success()
            ->title(fn () => __('Survey settings saved'))
            ->send();
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->user = Filament::getCurrentPanel()->auth()->user();

        $data = Setting::get()->pluck('value', 'key')->toArray();
        $this->callHook('beforeFill');

        foreach ($data as $key => $value) {
            if ($this->isJson($value)) {
                $data[$key] = json_decode($value, true);
            }
        }

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    private function isJson(mixed $string): bool
    {
        if (! is_string($string)) {
            return false;
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(fn () => __('Save survey settings'))
                ->icon(Heroicon::CloudArrowUp)
                ->submit('save'),
        ];
    }

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user?->can('access_settings') ?? false;
    }

    public static function canView($record): bool
    {
        /** @var \App\Models\User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user?->can('view_settings') ?? false;
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user?->can('create_settings') ?? false;
    }

    public static function canEdit($record): bool
    {
        /** @var \App\Models\User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user?->can('edit_settings') ?? false;
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user?->can('delete_settings') ?? false;
    }
}
