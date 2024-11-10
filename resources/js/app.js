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
        tickets: [
            {
                type: "1",
                quantity: getRandomInt(10),
            },
            {
                type: "2",
                quantity: getRandomInt(10),
            },
        ],
    };
    $.ajax({
        url: "/chooseAction",
        method: "post",
        dataType: "json",
        contentType: "applicatio/json",
        data: JSON.stringify(payload),
        success: function (data, textStatus, xhr) {
            show(payload, data);
        },
        error: function (data) {
            show(payload, data);
        },
    });
}

$("#book_button").on("click", () => {
    book();
});

function show(request, result) {
    $(".result").html(
        JSON.stringify(request) + "<br><br>" + JSON.stringify(result)
    );
}