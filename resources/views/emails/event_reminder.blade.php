<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nhắc nhở sự kiện</title>
</head>
<body>
    <p>Chào {{ $user->name }},</p>
    <p>Đây là lời nhắc nhở về sự kiện "<strong>{{ $event->title }}</strong>" sẽ diễn ra vào <strong>{{ $event->start_time }}</strong>.</p>
    <p>Nội dung sự kiện: {{ $event->description }}</p>
    <p>Trân trọng,<br>Ban quản trị</p>
</body>
</html>
