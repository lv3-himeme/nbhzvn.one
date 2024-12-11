/*
===========================================================
Rating icon hovering mechanics
===========================================================
*/

function highlightStars(cursor) {
    for (var i = 1; i <= cursor; i++) $(`#star-${i}`).attr("class", "fa fa-star");
    for (var i = cursor + 1; i <= 5; i++) $(`#star-${i}`).attr("class", "fa fa-star-o");
}

function unhighlightStars() {
    for (var i = 1; i <= 5; i++) $(`#star-${i}`).attr("class", element.attr("data-original-class"));
}

var hovering = {};
for (var i = 1; i <= 5; i++) {
    var element = $(`#star-${i}`);
    element.attr("star-id", i.toString());
    element.attr("data-original-class", element.attr("class"));
    if (!element.attr("data-rated")) {
        element.hover(function() {
            if ($(this).attr("data-rated")) return;
            var starId = parseInt($(this).attr("star-id"));
            hovering[starId] = true;
            highlightStars(starId);
        });
        element.mouseout(function() {
            var starId = parseInt($(this).attr("star-id"));
            hovering[starId] = false;
            if (!Object.values(hovering).filter(val => val).length) unhighlightStars();
        });
    }
}

/*
===========================================================
Rating command
===========================================================
*/

async function rate(id, rating) {
    if ($("#star-1").attr("data-rated")) return;
    var response = await apiRequest({
        url: "/api/games/rate",
        type: "POST",
        cache: false,
        contentType: false,
        processData: false,
        data: JSON.stringify({
            id,
            rating
        }),
        json: true
    });
    if (response?.success) {
        toastr.success(response.message, "Thông báo");
        var average = response.data.average,
            full = Math.floor(average), remain = average - full, index = 0, ostar = 4 - full, html = "";
        for (var i = 0; i < full; i++) {
            index++;
            html += `<a href="javascript:void(0)"><i data-rated="true" id="star-${i}" class="fa fa-star"></i></a> `;
        }
        if (index < 5) {
            index++;
            html += `<a href="javascript:void(0)"><i data-rated="true" id="star-${i}" class="fa fa-star${(remain >= 0.5) ? "-half" : ""}-o"></i></a> `;
        }
        for (var i = 0; i < ostar; i++) {
            index++;
            html += `<a href="javascript:void(0)"><i data-rated="true" id="star-${i}" class="fa fa-star-o"></i></a> `;
        }
        $("#rating").html(html);
        $(`#ratingText`).text(`${response.data.count} lượt đánh giá`);
        $(`#ratingText2`).text(`${response.data.average.toFixed(1).toLocaleString()} / ${response.data.count} lượt đánh giá`);
    }
}

/*
===========================================================
Follow command
===========================================================
*/

async function toggleFollow(id) {
    var response = await apiRequest({
        url: "/api/games/follow",
        type: "POST",
        cache: false,
        contentType: false,
        processData: false,
        data: JSON.stringify({
            id
        }),
        json: true
    });
    if (response?.success) {
        $("#followText").text(response.data.type == "follow" ? "Bỏ theo dõi" : "Theo dõi");
        $("#followCount").text(response.data.followers.toLocaleString());
    }
}

/*
===========================================================
View replies command
===========================================================
*/

async function viewReplies(id) {
    $(`#comment-${id}-repliesbtn`).remove();
    var response = await apiRequest({
        url: `/api/games/comments/replies/?id=${id}&html=true`,
        type: "GET",
        cache: false,
        contentType: false,
        processData: false
    });
    if (response?.success) {
        $(`#comment-${id}-replies`).html(response.data);
    }
}