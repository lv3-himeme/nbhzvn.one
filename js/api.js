var requests = {}, chunkName = {};
const CHUNK_SIZE = 8388608;

async function apiRequest(data, code) {
    return new Promise((resolve, reject) => {
        var temp = $.ajax(data)
        .done(function(data) {
            resolve(data);
        })
        .fail(function(data) {
            var res = data.responseText;
            try {res = JSON.parse(data.responseText)} catch {}
            if (typeof(res) == "object") toastr.error(res.message, `Lỗi ${res.status_code}`)
            else if (code && !cancelling[code]) toastr.error("Không thể kết nối tới máy chủ. Vui lòng kiểm tra thông tin lỗi bằng nút F12.", `Lỗi không xác định`);
            reject(res);
        });
        if (code) requests[code] = temp;
    });
}

/**
 * @param {File} file
 * @param {HTMLElement} progressBar
 */
async function uploadFile(file, progressBar, code) {
    var progressBarInner = progressBar.getElementsByClassName("progressbar")[0];
    if (progressBar && progressBarInner) {
        progressBar.style.display = "block";
        progressBarInner.style.width = "0%";
    }
    const chunks = Math.ceil(file.size / CHUNK_SIZE);
    for (var cursor = 0; cursor < chunks; cursor++) {
        const start = cursor * CHUNK_SIZE, end = Math.min(file.size, start + CHUNK_SIZE), chunk = file.slice(start, end);
        var formData = new FormData();
        formData.append("type", "chunk");
        formData.append("cursor", cursor);
        formData.append("chunks", chunks);
        formData.append("chunk", chunk);
        formData.append("file_name", file.name);
        if (chunkName[code]) formData.append("generated_name", chunkName[code]);
        try {
            var response = await apiRequest({
                url: "/api/upload",
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", (function(cursor, chunks, evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            var part = 100 / chunks;
                            if (progressBar && progressBarInner) progressBarInner.style.width = `${(part * cursor) + (part * percentComplete)}%`;
                        }
                    }).bind(this, cursor, chunks), false);
                    return xhr;
                },
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData
            }, code);
            if (response?.success) {
                if (cursor < chunks - 1) chunkName[code] = response.data;
                else {
                    if (progressBar && progressBarInner) progressBar.style.display = "none";
                    toastr.success(response.message, "Thông báo");
                    delete chunkName[code];
                    return response.data;
                }
            }
        }
        catch (err) {
            console.error(err);
            if (progressBar && progressBarInner) {
                progressBar.innerHTML = `<i style="color: #af1932">Lỗi!</i>`;
                progressBar.classList.remove("progressbar_container");
                progressBar.style.display = "inline";
            }
            try {
                if (chunkName[code]) {
                    apiRequest({
                        url: "/api/delete_chunk",
                        type: "POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: JSON.stringify({
                            chunk: chunkName[code]
                        }),
                        json: true
                    });
                    delete chunkName[code];
                }
            }
            catch {}
            return null;
        }
    }
}

let Pagination = {
    page: function() {
        return parseInt($("#currentPage").val());
    },
    maxPages: function() {
        return parseInt($("#currentPage").prop("max"));
    },
    previous: async function(apiUrl) {
        var page = this.page();
        if (page < 2) return null;
        page--;
        return await this.jump(apiUrl, page);
    },
    next: async function(apiUrl) {
        var page = this.page();
        if (page >= this.maxPages()) return null;
        page++;
        return await this.jump(apiUrl, page);
    },
    jump: async function(apiUrl, page = 1) {
        $("#currentPage").val(page.toString());
        var response = await apiRequest({
            url: apiUrl.replaceAll("{page}", page.toString()),
            type: "GET",
            cache: false,
            contentType: false,
            processData: false
        });
        if (response?.success) return response.data;
        return null;
    }
}