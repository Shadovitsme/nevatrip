<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>plaxeholder</title>
</head>

<body>
    <button id="book_button">book</button>
    <?php
    function generateBarcode()
    {
        $barcode = '';
        for ($i = 1; $i <= 120; $i++) {
            $barcode .= rand(0, 9);
        }
        echo $barcode;
    }
    ?>
    <div id="barcode"><?php generateBarcode() ?></div>
</body>

</html>