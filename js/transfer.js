var modal = new Modal();

/*
===========================================================
Searching members
===========================================================
*/

async function search() {
    var query = $("#query").val();
    if (!query) return toastr.error("Vui lòng nhập từ khoá cần tìm kiếm.", "Lỗi");
    $("#members").html(`<p><i>Đang tìm kiếm thành viên, vui lòng đợi...</i></p>`);
    var response = await apiRequest({
        url: `/api/users/?query=${encodeURIComponent(query)}`,
        type: "GET",
        cache: false,
        contentType: false,
        processData: false
    });
    if (response?.success) $("#members").html(response.data.length ? response.data.map(user => {
        return `
            <div class="transfer_member">
                <div>
                    <h4>${user.display_name || user.username}</h4>
                    <p><b>ID:</b> ${user.id}</p>
                </div>
                <div>
                    <button onclick="transfer(${user.id}, '${user.display_name || user.username}')"><i class="fa fa-check"></i></button>
                </div>
            </div>
        `;
    }) : "<p><i>Không tìm thấy thành viên nào.</i></p>")
}

/*
===========================================================
Process transferring
===========================================================
*/

function transfer(id, displayName) {
    modal.title = `Chuyển Quyền Quản Lý Game`;
    modal.body = `
        <p>Nhập mật khẩu của bạn để xác nhận muốn chuyển quyền quản lý game cho thành viên <b>${displayName}</b>:</p>
        <p><input class="form-control" type="password" id="password" /></p>
    `;
    modal.footer = `
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy bỏ</button>
        <button type="button" class="btn btn-primary" style="background-color: #af1932; border: 1px solid rgb(209, 55, 81)" onclick="processTransfer(${id}, '${displayName}')">Xác nhận</button>
    `;
    modal.update();
    modal.show();
}

async function processTransfer(id, displayName) {
    try {
        var password = $("#password").val();
        if (!password) return toastr.error("Vui lòng nhập mật khẩu của bạn.", "Lỗi");
        modal.body = `<p><i>Đang xử lý yêu cầu, bạn vui lòng chờ một lát...</i></p>`;
        modal.footer = ``;
        modal.update();
        var response = await apiRequest({
            url: "/api/games/transfer",
            type: "POST",
            cache: false,
            contentType: false,
            processData: false,
            data: JSON.stringify({
                game_id: gameId,
                owner_id: id,
                password
            }),
            json: true
        });
        if (response?.success) document.location.href = `/transfer/${gameId}?success=1&display_name=${encodeURIComponent(displayName)}`;
        modal.hide();
    }
    catch (err) {
        console.error(err);
        modal.hide();
    }
}