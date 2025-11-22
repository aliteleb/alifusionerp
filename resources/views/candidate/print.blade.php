<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Information - {{ $candidate->full_name }}</title>
    <style>
        body {
            font-family: sans-serif;
        }
        .container {
            width: 80%;
            margin: auto;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        .section {
            margin-bottom: 2rem;
        }
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .item {
            margin-bottom: 0.5rem;
        }
        .item label {
            font-weight: bold;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            @if($candidate->picture_url)
                <img src="{{ $candidate->picture_url }}" alt="{{ $candidate->full_name }}">
            @endif
            <h1>{{ $candidate->full_name }}</h1>
            <p>Candidate Information</p>
        </div>

        <div class="section">
            <h2 class="section-title">Personal Information</h2>
            <div class="grid">
                <div class="item">
                    <label>Phone:</label>
                    <span>{{ $candidate->phone }}</span>
                </div>
                <div class="item">
                    <label>Email:</label>
                    <span>{{ $candidate->email }}</span>
                </div>
                <div class="item">
                    <label>Present Address:</label>
                    <span>{{ $candidate->present_address }}</span>
                </div>
                <div class="item">
                    <label>Permanent Address:</label>
                    <span>{{ $candidate->permanent_address }}</span>
                </div>
                 <div class="item">
                    <label>SSN:</label>
                    <span>{{ $candidate->ssn }}</span>
                </div>
                 <div class="item">
                    <label>City:</label>
                    <span>{{ $candidate->city }}</span>
                </div>
                 <div class="item">
                    <label>Country:</label>
                    <span>{{ $candidate->country?->name }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">Education</h2>
            <table>
                <thead>
                    <tr>
                        <th>Degree</th>
                        <th>University</th>
                        <th>CGPA</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($candidate->educations as $education)
                        <tr>
                            <td>{{ $education->degree }}</td>
                            <td>{{ $education->university }}</td>
                            <td>{{ $education->cgpa }}</td>
                            <td>{{ $education->comments }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">Past Experience</h2>
             <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Working Period</th>
                        <th>Duties</th>
                        <th>Supervisor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($candidate->workExperiences as $experience)
                        <tr>
                            <td>{{ $experience->company_name }}</td>
                            <td>{{ $experience->working_period }}</td>
                            <td>{{ $experience->duties }}</td>
                            <td>{{ $experience->supervisor }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 