<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
    dir="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Work Commencement Declaration') }} - {{ safeOutput($employee_name) }}</title>

    @php
        // Enhanced helper function to ensure safe UTF-8 output
        function safeOutput($text)
        {
            if (!$text) {
                return '';
            }

            // Ensure proper UTF-8 encoding
            if (!mb_check_encoding($text, 'UTF-8')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            }

            // Remove problematic characters
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

            // Ensure proper UTF-8 encoding again
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

            return e($text); // Laravel's escape function
}

// Safe number formatting
function safeNumber($number, $decimals = 2)
{
    return number_format(floatval($number ?? 0), $decimals);
}

// Function to generate repeated non-breaking spaces for line rendering
function renderLine($length = 30)
{
    return str_repeat('&nbsp;', $length);
}

// Check if current locale is RTL
$isRTL = in_array(app()->getLocale(), ['ar', 'ku']);
    @endphp

    <!-- Tajawal Font for Arabic Support -->
    @if (!isset($is_pdf))
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    @else
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap');
        </style>
    @endif

    <!-- Work Commencement Declaration Styles -->
    @include('documents.partials.work-commencement-styles')
</head>

<body>
    @if(file_exists($logo_path))
        <div style="position: fixed; top: 40%; left: 40%; transform: translate(50%, 50%) rotate(45deg); z-index: -10; user-select: none; pointer-events: none; opacity: 0.05;">
            <img src="{{ $logo_path }}" alt="Watermark Logo" style="max-width: 300px; max-height: 300px; opacity: 0.1; filter: grayscale(80%);">
        </div>
    @elseif(!isset($is_pdf))
        <!-- Debug info (only visible in browser, not PDF) -->
        <div style="position: fixed; top: 10px; right: 10px; background: yellow; padding: 5px; font-size: 10px; z-index: 1000;">
            Debug: use_watermark={{ $use_watermark ? 'true' : 'false' }}<br>
            watermark_logo_path={{ $watermark_logo_path ?? 'null' }}<br>
            watermark_opacity={{ $watermark_opacity ?? 'null' }}<br>
            file_exists={{ ($watermark_logo_path && file_exists($watermark_logo_path)) ? 'true' : 'false' }}
        </div>
    @endif
    <div class="page">
        <div class="content">
            <!-- Header -->
            <div class="header">
                <div class="company-info">
                    <div class="company-name">{{ safeOutput($company_name) }}</div>
                    @if ($company_name_ar && $company_name_ar !== $company_name)
                        <div class="company-name-ar">{{ safeOutput($company_name_ar) }}</div>
                    @endif
                    @if ($company_address)
                        <div style="font-size: 14px; color: #64748b; margin-top: 5px; z-index: 10; position: relative;">
                            {{ safeOutput($company_address) }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Document Title -->
            <div class="document-title">
                <h2>{{ __('Work Commencement Declaration') }}</h2>
                <div class="document-number">
                    {{ __('Document Number') }}: {{ safeOutput($declaration_number) }}
                </div>
            </div>

            <!-- Employee Information Section -->
            <div class="content-section">
                <div class="section-header">
                    {{ __('Employee Information') }}
                </div>
                <div class="section-content">
                    <div class="employee-info-grid">
                        <div class="info-field">
                            <span class="field-label">{{ __('Employee Name') }}:</span>
                            <span class="field-value">{{ safeOutput($employee_name) }}</span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">{{ __('Employee ID') }}:</span>
                            <span class="field-value number-field">{{ safeOutput($employee_id) }}</span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">{{ __('Position') }}:</span>
                            <span class="field-value">{{ safeOutput($position_name) }}</span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">{{ __('Department') }}:</span>
                            <span class="field-value">{{ safeOutput($department_name) }}</span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">{{ __('Work Location') }}:</span>
                            <span class="field-value">{{ safeOutput($work_location) }}</span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">{{ __('Start Date') }}:</span>
                            <span class="field-value number-field">
                                {{ $joining_date ? $joining_date->format('Y/m/d') : '____/____/____' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Declaration Section -->
            <div class="content-section">
                <div class="section-header">
                    {{ __('Work Commencement Declaration') }}
                </div>
                <div class="section-content">
                    <div class="declaration-text rtl-content">
                        <p>
                            {{ __('I, the undersigned') }} <strong>{{ safeOutput($employee_name) }}</strong>
                            {{ __('acknowledge that I have commenced work at') }}
                            <strong>{{ safeOutput($company_name) }}</strong>
                            {{ __('effective from') }}
                            <strong>{{ $joining_date ? $joining_date->format('Y/m/d') : '____/____/____' }}</strong>
                            {{ __('in the department of') }} <strong>{{ safeOutput($department_name) }}</strong>.
                        </p>

                        <p>
                            {{ __('Work Location') }}: <span class="field-line">{!! renderLine(30) !!}</span>
                        </p>

                        <p>
                            {{ __('Employee Signature') }}: <span class="field-line">{!! renderLine(30) !!}</span>
                        </p>
                    </div>

                    <div class="declaration-text" style="margin-top: 20px;">
                        <p>
                            <strong>{{ __('For Administrative Use Only') }}</strong>
                        </p>
                        <p>
                            {{ __('Manager Name') }}: <span class="field-line">{!! renderLine(30) !!}</span>
                        </p>
                        <p>
                            {{ __('Manager Signature') }}: <span class="field-line">{!! renderLine(30) !!}</span>
                        </p>
                        <p>
                            {{ __('Department') }}: <span class="field-line">{!! renderLine(30) !!}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- For HR Use Section -->
            <div class="content-section">
                <div class="section-header">
                    {{ __('For HR Department Use') }}
                </div>
                <div class="section-content">
                    <div class="declaration-text">
                        <p>
                            {{ __('HR Manager Name') }}: <span class="field-line">{!! renderLine(30) !!}</span>
                        </p>
                        <p>
                            {{ __('Salary Amount') }}: <span class="field-line">{!! renderLine(30) !!}</span>
                        </p>
                        <p>
                            {{ __('Approval / Rejection') }}: <span class="field-line">{!! renderLine(30) !!}</span>
                        </p>
                        <p>
                            {{ __('Signature') }}: <span class="field-line">{!! renderLine(30) !!}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-box">
                    <h3>{{ __('Employee Signature') }}</h3>
                    <div class="signature-date">{{ __('Date') }}: <span
                            class="field-line">{!! renderLine(15) !!}</span></div>
                    <br>
                    <h3>{{ __('Company Representative') }}</h3>
                    <div class="signature-date">{{ __('Date') }}: <span
                            class="field-line">{!! renderLine(15) !!}</span></div>
                    <br>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>{{ __('This document was generated on') }}
            {{ $generated_date?->format('F d, Y \\a\\t H:i') ?? now()->format('F d, Y \\a\\t H:i') }}</p>
        <p>{{ __('Document Number') }}: {{ safeOutput($declaration_number) }}</p>
    </div>

    <!-- Action Buttons (hidden when printing or generating PDF) -->
    @if (!isset($is_pdf))
        <div class="actions-container no-print">
            <button onclick="window.print()" class="action-button print-button">
                {{ __('Print Declaration') }}
            </button>
            <a href="{{ route('employee.work-commencement.download', $employee) }}" class="action-button">
                {{ __('Download PDF') }}
            </a>
        </div>
    @endif
</body>

</html>
