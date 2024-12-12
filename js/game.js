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
    for (var i = 1; i <= 5; i++) $(`#star-${i}`).attr("class", $(`#star-${i}`).attr("data-original-class"));
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
        $(`#ratingText`).text(`${response.data.total} lượt đánh giá`);
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
Comment function
===========================================================
*/

async function comment() {
    try {
        var content = $("#commentContent").val(), button = $("#commentBtn");
        if (!content) return toastr.error("Vui lòng nhập nội dung bình luận.", "Thông báo");
        button.prop("disabled", true);
        var response = await apiRequest({
            url: "/api/games/comments/",
            type: "PUT",
            cache: false,
            contentType: false,
            processData: false,
            data: JSON.stringify({
                id: gameId,
                content
            }),
            json: true
        });
        button.prop("disabled", false);
        if (response?.success) {
            $("#comments").prepend(response.data);
            $("#commentContent").val("");
            document.location.href = "#comments";
        }
    }
    catch (err) {
        console.error(err);
        button.prop("disabled", false);
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
        delete replying[id];
    }
}

/*
===========================================================
Comment managing function
===========================================================
*/

async function deleteComment(id) {
    if (!confirm("Xác nhận xoá bình luận này?")) return;
    var response = await apiRequest({
        url: "/api/games/comments/",
        type: "DELETE",
        cache: false,
        contentType: false,
        processData: false,
        data: JSON.stringify({
            comment_id: id
        }),
        json: true
    });
    if (response?.success) {
        toastr.success(response.message, "Thông báo");
        $(`#comment-${id}`).remove();
    }
}

var originalContent = {};

function editComment(id) {
    originalContent[id] = {
        content: $(`#comment-${id}-content`).text(),
        options: $(`#comment-${id}-options`).html()
    };
    $(`#comment-${id}-content`).html(`<textarea class="comment_edit_box" id="comment-${id}-editbox">${originalContent[id].content}</textarea>`);
    $(`#comment-${id}-options`).html(`<a href="javascript:void(0)" onclick="processCommentEdit(${id})">Chỉnh sửa</a> • <a href="javascript:void(0)" onclick="cancelCommentEdit(${id})">Huỷ</a>`);
}

function cancelCommentEdit(id) {
    if (!originalContent[id]) return;
    $(`#comment-${id}-content`).html(originalContent[id].content);
    $(`#comment-${id}-options`).html(originalContent[id].options);
    delete originalContent[id];
}

async function processCommentEdit(id) {
    var content = $(`#comment-${id}-editbox`).val();
    if (!content) return toastr.error("Vui lòng nhập nội dung bình luận.", "Thông báo");
    var response = await apiRequest({
        url: "/api/games/comments/",
        type: "POST",
        cache: false,
        contentType: false,
        processData: false,
        data: JSON.stringify({
            comment_id: id,
            content
        }),
        json: true
    });
    if (response?.success) {
        toastr.success(response.message, "Thông báo");
        var dummy = document.createElement("div");
        dummy.innerHTML = response.data;
        $(`#comment-${id}`).html(dummy.getElementsByClassName("comment_container")[0].innerHTML);
        dummy.remove();
    }
}

var replying = {};

function replyComment(id, mention) {
    if (replying[id]) return $(`#comment-${id}-reply-box`).val(mention ? `@${mention} ` : "");
    replying[id] = true;
    var div = document.createElement("div");
    div.id = `comment-${id}-reply-container`;
    div.innerHTML = `
        <textarea id="comment-${id}-reply-box" class="comment_reply_box">${mention ? `@${mention} ` : ""}</textarea>
        <button onclick="processCommentReply(${id})" class="comment_reply_btn"><i class="fa fa-location-arrow"></i> Trả lời</button> <button onclick="cancelCommentReply(${id})" class="comment_reply_btn cancel"><i class="fa fa-times"></i> Huỷ</button> 
    `;
    document.getElementById(`comment-${id}-replies`).appendChild(div);
}

function cancelCommentReply(id) {
    $(`#comment-${id}-reply-container`).remove();
    delete replying[id];
}

async function processCommentReply(id) {
    var content = $(`#comment-${id}-reply-box`).val();
    if (!content) return toastr.error("Vui lòng nhập nội dung bình luận.", "Thông báo");
    cancelCommentReply(id);
    var response = await apiRequest({
        url: "/api/games/comments/",
        type: "PUT",
        cache: false,
        contentType: false,
        processData: false,
        data: JSON.stringify({
            id: gameId,
            content,
            replied_to: id
        }),
        json: true
    });
    if (response?.success) $(`#comment-${id}-replies`).append(response.data);
}

/*
===========================================================
Page navigation
===========================================================
*/

async function previousPage() {
    if (Pagination.page() < 2) return;
    $("#comments").html("");
    $("#comments").html(await Pagination.previous(`/api/games/comments/?id=${gameId}&page={page}&html=true`));
}

async function nextPage() {
    if (Pagination.page() >= Pagination.maxPages()) return;
    $("#comments").html("");
    $("#comments").html(await Pagination.next(`/api/games/comments/?id=${gameId}&page={page}&html=true`));
}

async function jumpToPage() {
    if (Pagination.page() >= Pagination.maxPages()) return;
    $("#comments").html("");
    $("#comments").html(await Pagination.jumpToPage(`/api/games/comments/?id=${gameId}&page={page}&html=true`, Pagination.page()));
}