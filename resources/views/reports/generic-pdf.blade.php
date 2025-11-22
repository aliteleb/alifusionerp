<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? __('Report') }}</title>
    <style>
        body {
            font-family: 'tajawal', 'dejavu sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #333;
        }
        
        .header .date {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #333;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-item .label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        @if(app()->getLocale() === 'ar')
        body {
            direction: rtl;
            text-align: right;
            font-feature-settings: "liga" 1, "kern" 1;
        }
        
        th, td {
            text-align: right;
        }
        
        .summary-grid {
            direction: rtl;
        }
        
        .header {
            text-align: center;
        }
        
        .footer {
            text-align: center;
        }
        
        /* Ensure proper Arabic text rendering */
        * {
            font-variant-ligatures: common-ligatures;
            font-feature-settings: "liga" 1, "kern" 1;
        }
        @endif
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? __('Report') }}</h1>
        <div class="date">{{ __('Generated on') }}: {{ now()->format('Y-m-d H:i:s') }}</div>
    </div>

    @if(isset($allData) && $type === 'all')
        {{-- Export All Reports --}}
        @foreach($allData as $reportType => $reportData)
            <div style="page-break-before: {{ $loop->first ? 'auto' : 'always' }};">
                <h2 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 20px;">
                    {{ ucfirst($reportType) }} {{ __('Reports') }}
                </h2>
                
                @if($reportData['records']->count() > 0)
                    <div class="summary">
                        <h3>{{ __('Summary') }}</h3>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <div class="label">{{ __('Total Records') }}</div>
                                <div class="value">{{ number_format($reportData['records']->count()) }}</div>
                            </div>
                        </div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                @php
                                    $firstRecord = $reportData['records']->first();
                                    $headers = [];
                                    
                                    if ($firstRecord) {
                                        switch ($reportType) {
                                            case 'client':
                                                $headers = ['ID', __('Name'), __('Email'), __('Phone'), __('Branch'), __('Client Group'), __('Status'), __('Created At')];
                                                break;
                                            case 'sales':
                                                $headers = ['ID', __('Title'), __('Client'), __('Branch'), __('Value'), __('Status'), __('Assigned To'), __('Created At')];
                                                break;
                                            case 'employee':
                                                $headers = ['ID', __('Name'), __('Email'), __('Phone'), __('Branch'), __('Department'), __('Designation'), __('Status'), __('Created At')];
                                                break;
                                            case 'task':
                                                $headers = ['ID', __('Title'), __('Description'), __('Branch'), __('Assigned To'), __('Project'), __('Status'), __('Created At')];
                                                break;
                                            case 'project':
                                                $headers = ['ID', __('Name'), __('Description'), __('Branch'), __('Client'), __('Assigned To'), __('Status'), __('Created At')];
                                                break;
                                            case 'financial':
                                                $headers = ['ID', __('Title'), __('Client'), __('Branch'), __('Value'), __('Status'), __('Created At')];
                                                break;
                                            default:
                                                $headers = ['ID', __('Name'), __('Created At')];
                                        }
                                    }
                                @endphp
                                
                                @foreach($headers as $header)
                                    <th>{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['records'] as $record)
                                <tr>
                                    @switch($reportType)
                                        @case('client')
                                            <td>{{ $record->id }}</td>
                                            <td>{{ $record->name }}</td>
                                            <td>{{ $record->email }}</td>
                                            <td>{{ $record->phone }}</td>
                                            <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->clientGroup?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->is_active ? __('Active') : __('Inactive') }}</td>
                                            <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                            @break
                                        @case('sales')
                                            <td>{{ $record->id }}</td>
                                            <td>{{ $record->title }}</td>
                                            <td>{{ $record->client?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                            <td>{{ number_format($record->value, 2) }}</td>
                                            <td>{{ $record->status }}</td>
                                            <td>{{ $record->assignedTo?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                            @break
                                        @case('employee')
                                            <td>{{ $record->id }}</td>
                                            <td>{{ $record->name }}</td>
                                            <td>{{ $record->email }}</td>
                                            <td>{{ $record->phone }}</td>
                                            <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->department?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->designation?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->is_active ? __('Active') : __('Inactive') }}</td>
                                            <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                            @break
                                        @case('task')
                                            <td>{{ $record->id }}</td>
                                            <td>{{ $record->title }}</td>
                                            <td>{{ Str::limit($record->description, 50) }}</td>
                                            <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->assignedTo?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->project?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->status }}</td>
                                            <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                            @break
                                        @case('project')
                                            <td>{{ $record->id }}</td>
                                            <td>{{ $record->name }}</td>
                                            <td>{{ Str::limit($record->description, 50) }}</td>
                                            <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->client?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->assignedTo?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->status }}</td>
                                            <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                            @break
                                        @case('financial')
                                            <td>{{ $record->id }}</td>
                                            <td>{{ $record->title }}</td>
                                            <td>{{ $record->client?->name ?? __('N/A') }}</td>
                                            <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                            <td>{{ number_format($record->value, 2) }}</td>
                                            <td>{{ $record->status }}</td>
                                            <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                            @break
                                        @default
                                            <td>{{ $record->id }}</td>
                                            <td>{{ $record->name ?? __('N/A') }}</td>
                                            <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                    @endswitch
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="no-data">
                        {{ __('No data found for') }} {{ $reportType }} {{ __('reports.') }}
                    </div>
                @endif
            </div>
        @endforeach
    @elseif(isset($records) && $records->count() > 0)
        <div class="summary">
            <h3>{{ __('Summary') }}</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="label">{{ __('Total Records') }}</div>
                    <div class="value">{{ number_format($records->count()) }}</div>
                </div>
                @if(isset($total_count))
                <div class="summary-item">
                    <div class="label">{{ __('Total Count') }}</div>
                    <div class="value">{{ number_format($total_count) }}</div>
                </div>
                @endif
                @if(isset($filters) && !empty($filters))
                <div class="summary-item">
                    <div class="label">{{ __('Filters Applied') }}</div>
                    <div class="value">{{ count(array_filter($filters)) }}</div>
                </div>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    @php
                        $firstRecord = $records->first();
                        $headers = [];
                        
                        if ($firstRecord) {
                            switch ($type) {
                                case 'client':
                                    $headers = ['ID', __('Name'), __('Email'), __('Phone'), __('Branch'), __('Client Group'), __('Status'), __('Created At')];
                                    break;
                                case 'sales':
                                    $headers = ['ID', __('Title'), __('Client'), __('Branch'), __('Value'), __('Status'), __('Assigned To'), __('Created At')];
                                    break;
                                case 'employee':
                                    $headers = ['ID', __('Name'), __('Email'), __('Phone'), __('Branch'), __('Department'), __('Designation'), __('Status'), __('Created At')];
                                    break;
                                case 'task':
                                    $headers = ['ID', __('Title'), __('Description'), __('Branch'), __('Assigned To'), __('Project'), __('Status'), __('Created At')];
                                    break;
                                case 'project':
                                    $headers = ['ID', __('Name'), __('Description'), __('Branch'), __('Client'), __('Assigned To'), __('Status'), __('Created At')];
                                    break;
                                case 'financial':
                                    $headers = ['ID', __('Title'), __('Client'), __('Branch'), __('Value'), __('Status'), __('Created At')];
                                    break;
                                default:
                                    $headers = ['ID', __('Name'), __('Created At')];
                            }
                        }
                    @endphp
                    
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                    <tr>
                        @switch($type)
                            @case('client')
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->name }}</td>
                                <td>{{ $record->email }}</td>
                                <td>{{ $record->phone }}</td>
                                <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                <td>{{ $record->clientGroup?->name ?? __('N/A') }}</td>
                                <td>{{ $record->is_active ? __('Active') : __('Inactive') }}</td>
                                <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                @break
                            @case('sales')
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->title }}</td>
                                <td>{{ $record->client?->name ?? __('N/A') }}</td>
                                <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                <td>{{ number_format($record->value, 2) }}</td>
                                <td>{{ $record->status }}</td>
                                <td>{{ $record->assignedTo?->name ?? __('N/A') }}</td>
                                <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                @break
                            @case('employee')
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->name }}</td>
                                <td>{{ $record->email }}</td>
                                <td>{{ $record->phone }}</td>
                                <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                <td>{{ $record->department?->name ?? __('N/A') }}</td>
                                <td>{{ $record->designation?->name ?? __('N/A') }}</td>
                                <td>{{ $record->is_active ? __('Active') : __('Inactive') }}</td>
                                <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                @break
                            @case('task')
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->title }}</td>
                                <td>{{ Str::limit($record->description, 50) }}</td>
                                <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                <td>{{ $record->assignedTo?->name ?? __('N/A') }}</td>
                                <td>{{ $record->project?->name ?? __('N/A') }}</td>
                                <td>{{ $record->status }}</td>
                                <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                @break
                            @case('project')
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->name }}</td>
                                <td>{{ Str::limit($record->description, 50) }}</td>
                                <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                <td>{{ $record->client?->name ?? __('N/A') }}</td>
                                <td>{{ $record->assignedTo?->name ?? __('N/A') }}</td>
                                <td>{{ $record->status }}</td>
                                <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                @break
                            @case('financial')
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->title }}</td>
                                <td>{{ $record->client?->name ?? __('N/A') }}</td>
                                <td>{{ $record->branch?->name ?? __('N/A') }}</td>
                                <td>{{ number_format($record->value, 2) }}</td>
                                <td>{{ $record->status }}</td>
                                <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                @break
                            @default
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->name ?? __('N/A') }}</td>
                                <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                        @endswitch
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            {{ __('No data found for the selected criteria.') }}
        </div>
    @endif

    <div class="footer">
        {{ __('Generated by') }} {{ config('app.name') }} | {{ __('Page') }} 1
    </div>
</body>
</html>
