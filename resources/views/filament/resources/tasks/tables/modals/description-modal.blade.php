<div class="space-y-3" dir="auto">
    @php
        $description = trim((string) ($record->description ?? ''));
    @endphp

    @if($description !== '')
        <div class="text-sm leading-6 text-gray-700 dark:text-gray-300 break-words">
            {!! $description !!}
        </div>
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('No description available for this task.') }}
        </div>
    @endif
</div>

