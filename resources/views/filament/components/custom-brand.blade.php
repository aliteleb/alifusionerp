<div class="flex items-center w-full">
    @if (settings('sidebar_logo'))
        <img src="{{ settings('sidebar_logo') }}" alt="{{ settings('app_name', config('app.name')) }}" class="w-full h-8 me-3 hidden lg:block">
    @elseif (settings('logo'))
        <img src="{{ settings('logo') }}" alt="{{ settings('app_name', config('app.name')) }}" class="w-full h-8 me-3 hidden lg:block">
    @endif
    
    @if (settings('sidebar_collapsed_logo'))
        <img src="{{ settings('sidebar_collapsed_logo') }}" alt="{{ settings('app_name', config('app.name')) }}" class="w-full h-8 me-3 lg:hidden">
    @elseif (settings('favicon'))
        <img src="{{ settings('favicon') }}" alt="{{ settings('app_name', config('app.name')) }}" class="w-full h-8 me-3 lg:hidden">
    @endif
    
    <span class="text-xl font-bold leading-5 tracking-tight filament-brand hidden lg:block">
        {{ settings('app_name', config('app.name')) }}
    </span>
</div>