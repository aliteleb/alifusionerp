<?php

namespace Modules\Core\Filament\Pages;

use Modules\Core\Entities\Currency;
use Modules\Core\Entities\Setting;
use Modules\Core\Entities\User;
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

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.admin.pages.settings';

    protected static ?int $navigationSort = 4;

    public function getTitle(): string|Htmlable
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'admin-settings';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public Authenticatable $user;

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->user = Filament::getCurrentOrDefaultPanel()->auth()->user();

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

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('General')->id('general')->label(__('General Information'))->icon('icon-menu')->schema($this->getGeneralSchema()),
                        Tab::make('Tasks')->id('tasks')->label(__('Task Management'))->icon('heroicon-o-clipboard-document-list')->schema($this->getTaskManagementSchema()),
                        Tab::make('Tickets')->id('tickets')->label(__('Ticket Management'))->icon('heroicon-o-ticket')->schema($this->getTicketManagementSchema()),
                        Tab::make('Email')->id('email')->label(__('Email Settings'))->icon('heroicon-o-envelope')->schema($this->getEmailSchema()),
                    ])->persistTab(),
            ])
            ->statePath('data')
            ->columns(2);
    }

    private function getGeneralSchema(): array
    {
        return [
            Section::make(__('Application Details'))
                ->schema([
                    TextInput::make('app_name')
                        ->label(__('Title')),
                    TextInput::make('email')
                        ->label(__('Email address'))
                        ->email(),
                    TextInput::make('phone')
                        ->label(__('Phone'))
                        ->tel(),
                    TextInput::make('tax_no')
                        ->label(__('Tax no')),
                    Textarea::make('address')
                        ->label(__('Address')),
                    TextInput::make('footer_text')
                        ->label(__('Footer text')),
                    TextInput::make('website')
                        ->label(__('Website'))
                        ->url(),
                    Select::make('currency_id')
                        ->label(__('Currency'))
                        ->options(Currency::pluck('title', 'id'))
                        ->searchable(),
                    Select::make('timezone')
                        ->label(__('Timezone'))
                        ->options(collect(timezone_identifiers_list())->mapWithKeys(function ($timezone) {
                            return [$timezone => $timezone];
                        })->toArray())
                        ->searchable()
                        ->default('UTC')
                        ->helperText(__('Select the timezone for this tenant')),
                    TextInput::make('direction')
                        ->label(__('Direction')),
                    Toggle::make('floating_number')
                        ->label(__('Floating number')),
                ])->columns(2),

            Section::make(__('Localization Settings'))
                ->schema([
                    Select::make('locales')
                        ->label(__('Supported Locales'))
                        ->multiple()
                        ->options([
                            'en' => __('English'),
                            'ar' => __('Arabic').' (العربية)',
                            'ku' => __('Kurdish').' (کوردی)',
                        ])
                        ->default(['en', 'ar', 'ku'])
                        ->helperText(__('Select the languages that will be available for multi-language content')),
                ])->columns(1),

            Section::make(__('Logo & Images'))
                ->schema([
                    FileUpload::make('logo')
                        ->label(__('Logo'))
                        ->image()
                        ->imageEditor()
                        ->disk('tenant')
                        ->helperText(__('Recommended size: 200x50 pixels')),
                    FileUpload::make('login_image')
                        ->label(__('Login image'))
                        ->image()
                        ->disk('tenant')
                        ->helperText(__('Recommended size: 400x300 pixels')),
                    FileUpload::make('favicon')
                        ->label(__('Favicon'))
                        ->image()
                        ->disk('tenant')
                        ->helperText(__('Recommended size: 32x32 pixels')),
                ])->columns(2),
        ];
    }

    private function getSEOSchema(): array
    {
        return [

            Section::make([
                TextInput::make('app_title')->placeholder(fn () => __('Site title'))->hiddenLabel()->helperText(fn () => __('Website title in search engines'))->prefixIcon('icon-caret'),
            ])->compact()->columns(1)->heading(fn () => __('Site title')),

            Section::make([
                Textarea::make('app_description')->autosize()->placeholder(fn () => __('Site description'))->hiddenLabel()->helperText(fn () => __('Website description in search engines and social media')),
            ])->compact()->columns(1)->heading(fn () => __('Site description')),

            Section::make([
                TagsInput::make('app_tags')->placeholder(fn () => __('Site keywords'))->hiddenLabel(),
            ])->compact()->columns(1)->heading(fn () => __('Site keywords')),

        ];
    }

    private function getTaskManagementSchema(): array
    {
        return [
            Section::make(__('Task Status Change Settings'))
                ->schema([
                    Toggle::make('task_status_change_comment_mandatory')
                        ->label(__('Require Comments for Status Changes'))
                        ->helperText(__('When enabled, users must provide a comment when changing task status'))
                        ->default(false)
                        ->live(),

                    Toggle::make('task_status_change_comment_optional')
                        ->label(__('Allow Optional Comments for Status Changes'))
                        ->helperText(__('When enabled, users can optionally provide a comment when changing task status (even if comments are not mandatory)'))
                        ->default(true)
                        ->visible(fn ($get) => ! $get('task_status_change_comment_mandatory')),

                    Toggle::make('task_status_change_tracking')
                        ->label(__('Track Status Change History'))
                        ->helperText(__('When enabled, all status changes will be logged with timestamps and user information'))
                        ->default(true),
                ])->columns(1),
        ];
    }

    private function getTicketManagementSchema(): array
    {
        return [
            Section::make(__('Ticket Status Change Settings'))
                ->schema([
                    Toggle::make('ticket_status_change_comment_mandatory')
                        ->label(__('Require Comments for Status Changes'))
                        ->helperText(__('When enabled, users must provide a comment when changing ticket status'))
                        ->default(false)
                        ->live(),

                    Toggle::make('ticket_status_change_comment_optional')
                        ->label(__('Allow Optional Comments for Status Changes'))
                        ->helperText(__('When enabled, users can optionally provide a comment when changing ticket status (even if comments are not mandatory)'))
                        ->default(true)
                        ->visible(fn ($get) => ! $get('ticket_status_change_comment_mandatory')),

                    Toggle::make('ticket_status_change_tracking')
                        ->label(__('Track Status Change History'))
                        ->helperText(__('When enabled, all status changes will be logged with timestamps and user information'))
                        ->default(true),
                ])->columns(1),
        ];
    }

    private function getEmailSchema(): array
    {
        return [
            Section::make([
                TextInput::make('mail_mailer')
                    ->label(__('Mail Driver'))
                    ->placeholder(__('smtp'))
                    ->helperText(__('The mail service driver (smtp, sendmail, mailgun, etc.)')),
                TextInput::make('mail_host')
                    ->label(__('Mail Host'))
                    ->placeholder(__('smtp.example.com'))
                    ->helperText(__('The mail server host address')),
                TextInput::make('mail_port')
                    ->label(__('Mail Port'))
                    ->placeholder(__('587'))
                    ->helperText(__('The mail server port (usually 587 for TLS or 465 for SSL)'))
                    ->numeric(),
                TextInput::make('mail_username')
                    ->label(__('Mail Username'))
                    ->placeholder(__('username@example.com'))
                    ->helperText(__('Your mail server username')),
                TextInput::make('mail_password')
                    ->label(__('Mail Password'))
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => $state ? $state : null)
                    ->helperText(__('Your mail server password')),
                TextInput::make('mail_encryption')
                    ->label(__('Mail Encryption'))
                    ->placeholder(__('tls'))
                    ->helperText(__('The encryption type (tls, ssl, or null)')),
                TextInput::make('mail_from_address')
                    ->label(__('Mail From Address'))
                    ->placeholder(__('noreply@example.com'))
                    ->helperText(__('The email address that will appear in the `From` field'))
                    ->email(),
                TextInput::make('mail_from_name')
                    ->label(__('Mail From Name'))
                    ->placeholder(__('Ali Fusion ERP'))
                    ->helperText(__('The name that will appear in the `From` field')),
            ])->compact()->columns(2)->heading(fn () => __('SMTP Settings')),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            foreach ($data as $key => $value) {
                // Encode arrays as JSON
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            // Clear settings cache for this facility
            $subdomain = getCurrentSubdomain();
            $cacheKey = $subdomain.'_settings';
            cache()->forget($cacheKey);

            Notification::make()
                ->title(__('Settings saved successfully'))
                ->success()
                ->send();
        } catch (\Exception $exception) {
            Notification::make()
                ->title(__('Failed to save settings'))
                ->danger()
                ->send();
        }
    }

    // Helper function to check if a string is valid JSON
    private function isJson($string): bool
    {
        if (! is_string($string)) {
            return false;
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user ? $user->can('access_settings') : false;
    }
}
