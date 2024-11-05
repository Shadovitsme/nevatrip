import './bootstrap';

function getRandomInt(max) {
    return Math.floor(Math.random() * max);
}

function randomDate(start, end, startHour, endHour) {
    var date = new Date(+start + Math.random() * (end - start));
    var hour = (startHour + Math.random() * (endHour - startHour)) | 0;
    date.setHours(hour);
    var options = {
        year: "numeric",
        month: "2-digit",
        day: "numeric",
    };
    date = date.toLocaleString("ru", options);
    return date;
}

function generateBarcode() {
    let barcode = "";

    for (let i = 0; i < 120; i++) {
        barcode = barcode + getRandomInt(9);
    }
    return barcode;
}
$("#book_button").on("click", () => {
    let barcode = generateBarcode();
    $.ajax({
        url: "/book/" + barcode,
        method: "GET",
        dataType: "json",
        data: barcode,
        success: function (data, textStatus, xhr) {},
    }).done(approve(barcode));
    // TODO сделать тут при феил вызов аякса для перегенерации баркода, а лучше сразу к нему обращаться
});
function approve(barcode) {
    $.ajax({
        url: "/approve/" + barcode,
        method: "GET",
        dataType: "json",
        data: { barcode: barcode },
        success: function (data) {
            console.log("data");
        },
    }).done(addToDatabase(barcode));
}
function addToDatabase(barcode) {
    let payload = {
        event_id: getRandomInt(1000000),
        event_date: randomDate(new Date(2020, 0, 1), new Date(), 0, 24),
        ticket_adult_price: getRandomInt(1000),
        ticket_adult_quantity: getRandomInt(20),
        ticket_kid_price: getRandomInt(1000),
        ticket_kid_quantity: getRandomInt(20),
        barcode: barcode,
    };
    $.ajax({
        url: "/addToDatabase",
        method: "get",
        dataType: "json",
        data: {
            event_id: getRandomInt(1000000),
            event_date: randomDate(new Date(2020, 0, 1), new Date(), 0, 24),
            ticket_adult_price: getRandomInt(1000),
            ticket_adult_quantity: getRandomInt(20),
            ticket_kid_price: getRandomInt(1000),
            ticket_kid_quantity: getRandomInt(20),
            barcode: barcode,
        },
        success: function (data) {
            console.log("data");
        },
    });
};