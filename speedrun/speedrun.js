var modal = new Modal();

async function check() {
    try {
        $("#registerBtn").prop("disabled", "true");
        var response = await apiRequest({
            url: "./api/check",
            cache: false,
            method: "GET"
        });
        $("#registerBtn").prop("disabled", null);
        if (response.data) openRegisterModal();
        else {
            modal.title = "Thông báo";
            modal.body = response.message;
            modal.footer = `<button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>`;
            modal.update();
            modal.show();
        }
    }
    catch (err) {
        console.error(err);
        $("#registerBtn").prop("disabled", null);
    }
}

function openRegisterModal() {
    modal.title = "Đăng ký tham gia";
    modal.body = `
        <p>Bằng cách trả lời câu hỏi bên dưới và nhấn vào nút <b>Đăng ký</b>, bạn xác nhận là bạn đã đọc hết nội quy ở bên dưới, cũng như là có tinh thần tốt trong việc tham gia sự kiện sắp tới:</p>
        <p><b>Chọn thiết bị bạn sẽ sử dụng để tham gia sự kiện:</b></p>
        <p>
            <select class="form-control" id="speedrunOS">
                <option value="windows">Máy tính chạy Windows</option>
                <option value="mac">Máy tính chạy macOS (MacBook, iMac, Mac Mini, v.v.)</option>
                <option value="linux">Máy tính chạy Linux</option>
                <option value="android">Điện thoại chạy Android (Samsung, Xiaomi, Oppo, v.v.)</option>
                <option value="ios">Điện thoại chạy iOS (iPhone, iPad, iPod)</option>
            </select>
        </p>
        <p><b>Nội quy cơ bản khi tham gia sự kiện:</b></p>
        <h4><b>Về cách thức tham gia</b></h4>
        <ul>
            <li>Bạn xác nhận là bạn sẽ có mặt đúng giờ khi sự kiện bắt đầu và có một tinh thần thật tốt khi tham gia. Nếu bạn không có mặt đúng giờ và gây ảnh hưởng đến tinh thần của những người khác, bạn có thể bị nhắc nhở hoặc xử phạt tùy theo mức độ vi phạm.</li>
            <li>Bạn đảm bảo thiết bị của bạn sẽ có khả năng chạy game trong suốt quá trình tham gia sự kiện. Bạn có thể chơi thử trước bằng cách nhấn vào nút Thi Thử ở đầu website này.</li>
            <li>Game sẽ được chơi trực tiếp trên trang web này. Khi sự kiện bắt đầu, bạn sẽ nhận được một thông báo trên Discord, và nút để bạn bắt đầu chơi cũng sẽ được mở trên trang chính của trang web. Bản game nội bộ được tải về thiết bị sẽ không được hỗ trợ.</li>
            <li>Trong suốt quá trình chơi, bạn phải chia sẻ màn hình (screen share) thiết bị đang chơi (máy tính hoặc điện thoại) trên kênh được chỉ định trong máy chủ Discord.</li>
        </ul>
        <h4><b>Về quá trình tham gia</b></h4>
        <ul>
            <li>Thời gian bắt đầu, kết thúc và dữ liệu chơi sẽ được hệ thống tự động ghi nhận trong quá trình tham gia.</li>
            <li>Khi đến thời gian xét thứ hạng, hệ thống sẽ tự động xếp hạng dựa trên dữ liệu đã ghi. Không có tác động thủ công từ BTC trừ khi có khiếu nại hợp lệ.</li>
            <li>Trong trường hợp phát hiện vi phạm, BTC có quyền tạm dừng hoặc hủy lượt chơi từ xa thông qua hệ thống.</li>
            <li>Người chơi bắt buộc phải <b>stream toàn bộ màn hình chơi</b> trong suốt quá trình speedrun.</li>
            <li>Nếu màn hình không hiển thị rõ ràng, bị gián đoạn hoặc bị che khuất, BTC có quyền tạm dừng cho đến khi màn hình được hiển thị trở lại. Nếu tình hình quá xấu, BTC có thể yêu cầu chơi lại hoặc hủy lượt chơi.</li>
        </ul>
        <h4><b>Quy chế thi</b></h4>
        <ul>
            <li>Người chơi không được phép sử dụng phần mềm hỗ trợ gian lận như auto-click, macro, tool cheat hay giả lập không được cho phép.</li>
            <li>Nếu phát hiện bug hoặc lỗi hệ thống, người chơi cần có trách nhiệm báo cáo ngay với BTC. Nếu cố tình khai thác lỗi để đạt lợi thế, BTC có quyền huỷ kết quả và cấm tham gia vĩnh viễn.</li>
            <li>Trong một số trường hợp sự cố bất khả kháng (mất mạng, server lag, crash…), BTC có quyền cho phép người chơi thực hiện lại lượt chơi, hoặc hủy kết quả nếu dữ liệu không thể phục hồi.</li>
            <li>Mọi hành động bất thường trong dữ liệu hệ thống (như thời gian bất hợp lý, số lần chơi vượt giới hạn, v.v.) sẽ được kiểm tra lại thủ công, và kết quả có thể bị tạm hoãn hoặc huỷ nếu không xác minh được tính hợp lệ.</li>
            <li>Không được chia sẻ tài khoản hoặc dùng tài khoản người khác để chơi thay. Mỗi người chỉ được phép dùng một tài khoản duy nhất, và một thiết bị duy nhất đã đăng ký trước đó để tham gia sự kiện.</li>
            <li>Trong suốt lượt chơi, cửa sổ game phải luôn hiển thị ở vị trí chính (foreground). Không được phép thu nhỏ game (minimize), chuyển sang cửa sổ khác, hay giấu cửa sổ game ra sau ứng dụng khác.</li>
            <li>Việc sử dụng nhiều màn hình (multi-monitor) là không bị cấm, nhưng người chơi phải đảm bảo game vẫn được hiển thị rõ ràng trong stream, và không dùng màn hình phụ để hỗ trợ bất kỳ hình thức gian lận nào.</li>
            <li>Stream phải bao phủ toàn bộ màn hình đang chơi, không được crop chỉ phần game (trừ khi được BTC cho phép trước). Nếu phát hiện thao tác bất thường ngoài khung stream, BTC có quyền yêu cầu cung cấp thêm bằng chứng hoặc huỷ kết quả.</li>
            <li>Việc liên tục chuyển cửa sổ hoặc có hành vi đáng ngờ (như alt-tab liên tục, chuyển task nhanh chóng) sẽ bị hệ thống ghi nhận và có thể bị xem xét kỹ hơn.</li>
            <li>BTC khuyến khích người chơi đóng các ứng dụng không cần thiết trong quá trình chơi để tránh ảnh hưởng hiệu năng, đồng thời tăng tính minh bạch.</li>
        </ul>
        <h4><b>Về phần thưởng</b></h4>
        <ul>
            <li>Phần thưởng sẽ không thể quy đổi thành tiền mặt.</li>
            <li>BTC có quyền thay đổi, hoãn hoặc huỷ phần thưởng nếu phát hiện gian lận.</li>
            <li>Trong trường hợp có khiếu nại, BTC sẽ kiểm tra dữ liệu hệ thống và stream để đưa ra quyết định cuối cùng.</li>
            <li>Quyết định xét hạng và trao thưởng cuối cùng sẽ thuộc về BTC.</li>
        </ul>
        <h4><b>Về cách ứng xử</b></h4>
        <ul>
            <li>Giữ thái độ lịch sự, tôn trọng BTC và người chơi khác.</li>
            <li>Không spam, gây rối, hoặc dùng từ ngữ kích động trong thời gian diễn ra sự kiện.</li>
            <li>Nếu bị phát hiện cố tình vi phạm, người chơi sẽ bị loại khỏi sự kiện và có thể bị cấm tham gia các sự kiện sau.</li>
        </ul>
    `;
    modal.footer = `
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy bỏ</button>
        <button type="button" class="btn btn-primary" onclick="register()" id="registerModalBtn">Đăng ký</button>
    `;
    modal.update();
    modal.show();
}

async function register() {
    try {
        $("#registerModalBtn").prop("disabled", "true");
        var response = await apiRequest({
            url: `./api/participate?os=${$("#speedrunOS").val()}`,
            cache: false,
            method: "GET"
        });
        modal.title = "Đăng ký thành công";
        modal.body = response.message;
        modal.footer = `<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="document.location.reload()">OK</button>`;
        modal.update();
    }
    catch (err) {
        console.error(err);
        modal.hide();
    }
}