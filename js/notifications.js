/*
===========================================================
Page navigation
===========================================================
*/

async function previousPage() {
    if (Pagination.page() < 2) return;
    $("#notifications").html("");
    $("#notifications").html(await Pagination.previous(`/api/users/notifications?page={page}&html=true`));
}

async function nextPage() {
    if (Pagination.page() >= Pagination.maxPages()) return;
    $("#notifications").html("");
    $("#notifications").html(await Pagination.next(`/api/users/notifications?page={page}&html=true`));
}

async function jumpToPage() {
    if (Pagination.page() >= Pagination.maxPages()) return;
    $("#notifications").html("");
    $("#notifications").html(await Pagination.jumpToPage(`/api/users/notifications?page={page}&html=true`, Pagination.page()));
}

/*
===========================================================
Notifications deleting function
===========================================================
*/

async function deleteNotification(id) {
    if (!confirm(`Xác nhận xoá ${id ? "thông báo này" : "tất cả các thông báo"}?`)) return;
    var response = await apiRequest({
        url: "/api/users/notifications",
        type: "DELETE",
        cache: false,
        contentType: false,
        processData: false,
        data: JSON.stringify({
            id
        }),
        json: true
    });
    if (response?.success) {
        toastr.success(response.message, "Thông báo");
        if (id) $(`#notification-${id}`).remove();
        else {
            $("#notifications").html("<p>Bạn chưa có thông báo nào.</p>");
            if ($("#pagination")) $("#pagination").remove();
        }
    }
}