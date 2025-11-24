<?php

namespace Modules\Core\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
use Modules\Core\Entities\Currency;
use Modules\Core\Entities\Setting;
use Modules\Core\Entities\User;

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
                Tabs::make('organization-settings')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('organization')
                            ->id('organization')
                            ->label(__('Organization Settings'))
                            ->icon('heroicon-o-building-office-2')
                            ->schema($this->getGeneralSchema()),
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
