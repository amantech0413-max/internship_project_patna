<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Bhagya Laxmi Internship') }}</title>
    <script>
        window.__BLI__ = { apiBase: @json(url('/api/v1')) };
    </script>
    @vite(['resources/css/admin.css', 'resources/js/admin/main.js'])
</head>
<body>
    <div id="admin-app"></div>
</body>
</html>
