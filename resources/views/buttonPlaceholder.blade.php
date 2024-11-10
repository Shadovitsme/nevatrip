<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>plaсeholder</title>
</head>

<body>
    <x-menu>$menu</x-menu>
    <button id="book_button">book</button>
    <div class="result"></div>
</body>

</html>