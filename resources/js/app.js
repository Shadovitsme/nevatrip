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

function book() {
    // Как-то передать сюды кучу данных
    $.ajax({
        url: "/book",
        method: "GET",
        dataType: "json",
        success: function (data, textStatus, xhr) {
            if (xhr.status != 200) {
                book();
            } else {
                approve(data.barcode);
            }
        },
    });
}

$("#book_button").on("click", () => {
    book();
});
function approve(barcode) {
    $.ajax({
        url: "/approve/" + barcode,
        method: "GET",
        dataType: "json",
        data: { barcode: barcode },
        success: function (data, textStatus, xhr) {
            if (xhr.status == 200) {
                addToDatabase(barcode);
            }
        },
    });
}

function dataMock(barcode) {
    let payload = {
        event_id: getRandomInt(1000000),
        event_date: randomDate(new Date(2020, 0, 1), new Date(), 0, 24),
        ticket_adult_price: getRandomInt(1000),
        ticket_adult_quantity: getRandomInt(20),
        ticket_kid_price: getRandomInt(1000),
        ticket_kid_quantity: getRandomInt(20),
        barcode: barcode,
    };
    return payload;
}

function addToDatabase(barcode) {
    $.ajax({
        url: "/addToDatabase",
        method: "get",
        dataType: "json",
        data: dataMock(barcode),
        success: function (data) {
            console.log("data");
        },
    });
};