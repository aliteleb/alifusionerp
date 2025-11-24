<?php

namespace Modules\Survey\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected function getCredentialsFromFormData(array $data): array
    {
        $credentials = parent::getCredentialsFromFormData($data);

        // $tenant = \App\Models\Facility::where('subdomain', getCurrentSubdomain())->first();

        // if ($tenant) {
        //     $credentials['facility_id'] = $tenant->getKey();
        // }

        return $credentials;
    }

    public function getView(): string
    {
        return 'filament.pages.auth.login';
    }

    public function hasLogo(): bool
    {
        // Hide the default Filament logo since we're using our own
        return false;
    }

    public function getHeading(): string|Htmlable
    {
        // Return empty string to hide the default heading
        return '';
    }
}
