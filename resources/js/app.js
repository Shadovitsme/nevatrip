import './bootstrap';

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

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
    let payload = {
        quantityType1: getRandomInt(100),
        quantityType2: getRandomInt(100),
        idType1: 1,
        idType2: 2,
    };
    $.ajax({
        url: "/book",
        method: "post",
        dataType: "json",
        contentType: "applicatio/json",
        data: JSON.stringify(payload),
        success: function (data, textStatus, xhr) {
            payload["barcodes"] = data.barcodes;
            approve(payload);
        },
        error: function (data) {
            book();
        },
    });
}

$("#book_button").on("click", () => {
    book();
});

function approve(payload) {
    $.ajax({
        url: "/approve",
        method: "POST",
        dataType: "json",
        success: function (data, textStatus, xhr) {
            if (xhr.status == 200) {
                addToDatabase(payload);
            }
        },
    });
}

function addToDatabase(payload) {
    $.ajax({
        url: "/addToDatabase",
        method: "post",
        dataType: "json",
        contentType: "applicatio/json",
        data: JSON.stringify(payload),
        success: function (data) {
            console.log("data");
        },
    });
};
