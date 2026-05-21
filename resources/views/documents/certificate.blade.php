<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; text-align: center; }
        h1 { color: #15803d; margin-top: 80px; }
    </style>
</head>
<body>
    <h1>Certificate of Completion</h1>
    <p>This is to certify that</p>
    <h2>{{ $student->name }}</h2>
    <p>Student Code: {{ $student->student_code }}</p>
    <p>has successfully completed the internship program at</p>
    <h3>{{ $organization['name'] }}</h3>
    <p>{{ $organization['location'] }}</p>
    <p>Date: {{ now()->format('d M Y') }}</p>
</body>
</html>
