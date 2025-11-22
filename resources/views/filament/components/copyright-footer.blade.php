{{-- Copyright Footer Component --}}
<div class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-6 py-4 text-center">
    <div class="flex flex-col sm:!flex-row justify-between items-center text-sm text-gray-600 dark:text-gray-400">
        <div class="mb-2 sm:mb-0">
            © {{ date('Y') }} 
            @if(masterSettings('global_legal_entity_name'))
                {{ masterSettings('global_legal_entity_name') }}
            @else
                {{ settings('app_name', config('app.name')) }}
            @endif
            . {{ __('All rights reserved.') }}
            @if(masterSettings('global_registration_number'))
                <span class="hidden sm:inline">• {{ __('Reg. No') }}: {{ masterSettings('global_registration_number') }}</span>
            @endif
        </div>
        <div class="text-xs">
            @if(masterSettings('global_copyright_text'))
                {{ masterSettings('global_copyright_text') }}
            @else
                {{ __('Ali Fusion ERP') }}
            @endif
            @if(masterSettings('global_system_version'))
                v{{ masterSettings('global_system_version') }}
            @endif
        </div>
    </div>
    
    @if(masterSettings('global_license_information'))
        <div class="mt-2 text-xs text-gray-500 dark:text-gray-500">
            {{ masterSettings('global_license_information') }}
        </div>
    @endif
</div>