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
    let payload = dataMock();
    $.ajax({
        url: "/book",
        method: "GET",
        dataType: "json",
        data: payload,
        success: function (data, textStatus, xhr) {
            if (xhr.status != 200) {
                book();
            } else {
                payload["barcode"] = data.barcode;
                approve(data.barcode, payload);
            }
        },
    });
}

$("#book_button").on("click", () => {
    book();
});

function approve(barcode, payload) {
    $.ajax({
        url: "/approve/" + barcode,
        method: "GET",
        dataType: "json",
        data: { barcode: barcode },
        success: function (data, textStatus, xhr) {
            if (xhr.status == 200) {
                addToDatabase(payload);
            }
        },
    });
}

function dataMock() {
    let payload = {
        event_id: getRandomInt(1000000),
        event_date: randomDate(new Date(2020, 0, 1), new Date(), 0, 24),
        ticket_adult_price: getRandomInt(1000),
        ticket_adult_quantity: getRandomInt(20),
        ticket_kid_price: getRandomInt(1000),
        ticket_kid_quantity: getRandomInt(20),
    };
    return payload;
}

function addToDatabase(payload) {
    $.ajax({
        url: "/addToDatabase",
        method: "get",
        dataType: "json",
        data: payload,
        success: function (data) {
            console.log("data");
        },
    });
};