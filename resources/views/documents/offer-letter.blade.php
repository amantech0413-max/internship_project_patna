<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Offer Letter</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        h1 { color: #1e40af; }
    </style>
</head>
<body>
    <h1>{{ $organization['name'] }}</h1>
    <p>{{ $organization['location'] }}</p>
    <hr>
    <h2>Internship Offer Letter</h2>
    <p>Date: {{ now()->format('d M Y') }}</p>
    <p>Dear <strong>{{ $student->name }}</strong>,</p>
    <p>
        We are pleased to offer you an internship at M/s Bhagya Laxmi.
        Your student code is <strong>{{ $student->student_code }}</strong>
        and student code is <strong>{{ $student->student_code }}</strong>.
    </p>
    <p>Mode: {{ ucfirst($student->internship_mode?->value ?? 'online') }}</p>
    <p>College: {{ $student->college_name }}</p>
    <p>We look forward to your contribution.</p>
    <br>
    <p>Authorized Signatory<br>M/s Bhagya Laxmi</p>
</body>
</html>
