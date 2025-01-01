var gameFiles = {}, screenshots = {}, cancelling = {}, gameFilesIndex = 0, screenshotIndex = 0;

/*
===========================================================
Thumbnail uploading
===========================================================
*/

function uploadThumbnail() {
    $("#thumbnailFile").click();
}

function showThumbnailImage(path) {
    $("#thumbnailImage").prop("src", `/uploads/${path}`);
    $("#thumbnailImage").css("display", "inline");
}

$("#thumbnailFile").change(async function() {
    try {
        var file = document.getElementById("thumbnailFile").files[0];
        if (!file) return toastr.error("Vui lòng chọn một tệp tin.", "Lỗi");
        const path = await uploadFile(file, document.getElementById("thumbnailProgressBar"));
        $("#thumbnail").val(path);
        showThumbnailImage(path);
    }
    catch (err) {
        console.error(err);
        toastr.error("Có lỗi xảy ra trong khi tải tệp tin lên. Vui lòng kiểm tra thông tin lỗi bằng nút F12.", `Lỗi không xác định`);
    }
});

/*
===========================================================
Game files uploading
===========================================================
*/

function addGameFile() {
    gameFilesIndex++;
    var input = document.createElement("input"), fileId = `gf${gameFilesIndex.toString()}`;
    input.type = "file";
    input.id = `gameFileInput-${fileId}`;
    input.classList.add("hidden");
    input.setAttribute("file-id", fileId);
    input.setAttribute("accept", ".zip, .rar, .7z");
    input.onchange = function() {
        processGameFile(this.getAttribute("file-id"));
    }
    document.getElementById("files").appendChild(input);
    input.click();
}

/**
 * @param {string} id
 */
async function processGameFile(id) {
    try {
        var file = document.getElementById(`gameFileInput-${id}`).files[0];
        if (!file) return toastr.error("Vui lòng chọn một tệp tin.", "Lỗi");
        gameFiles[id] = {
            name: file.name,
            path: null
        };
        createGameFileElement(id, file);
        const path = await uploadFile(file, document.getElementById(`gameFileProgressBar-${id}`), id);
        if (gameFiles[id]) {
            document.getElementById(`gameFileDisplay-${id}`).style.display = "block";
            document.getElementById(`gameFileDisplay-${id}`).style.flexDirection = null;
            document.getElementById(`gameFileDisplay-${id}`).style.textAlign = "right";
            gameFiles[id].path = path;
            AutoSave.save();
            document.getElementById(`gameFileInput-${id}`).remove();
        }
    }
    catch (err) {
        console.error(err);
        document.getElementById(`gameFileContainer-${id}`)?.remove();
    }
}

/**
 * @param {string} id 
 * @param {File} file 
 */
function createGameFileElement(id, file) {
    var div = document.createElement("div");
    div.classList.add("upload_game_file");
    div.id = `gameFileContainer-${id}`;
    div.innerHTML = `
        <div class="row" style="color: #fff!important">
            <div class="col-md-8 col-lg-8">
                <div style="text-overflow: elipsis"><b>${file.name}</b></div>
            </div>
            <div class="col-md-4 col-lg-4">
                <div id="gameFileDisplay-${id}" style="display: flex; flex-direction: row; text-align: right">
                    <div class="progressbar_container" id="gameFileProgressBar-${id}" style="width: 80%">
                        <div class="progressbar"></div>
                    </div>
                    <button type="button" onclick="deleteGameFile('${id}')" class="upload_close_btn">X</button>
                </div>
            </div>
        </div>
    `;
    document.getElementById("gameFiles").appendChild(div);
}

/**
 * 
 * @param {string} id 
 */
function deleteGameFile(id, permanent = true) {
    cancelling[id] = true;
    var path = gameFiles[id]?.path;
    if (!gameFiles[id]?.path && requests[id]) {
        requests[id].abort();
        delete requests[id];
        if (chunkName[id]) {
            apiRequest({
                url: "/api/delete_chunk",
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: JSON.stringify({
                    chunk: chunkName[id]
                }),
                json: true
            });
            delete chunkName[id];
        }
        if (fileName[id]) window.localStorage.removeItem(fileName[id]);
    }
    delete cancelling[id];
    delete gameFiles[id];
    AutoSave.save();
    if (permanent) {
        try {
            if (path) apiRequest({
                url: "/api/delete_file",
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: JSON.stringify({file: path}),
                json: true
            })
        }
        catch (err) {
            console.error(err);
        }
    }
    document.getElementById(`gameFileContainer-${id}`).remove();
}

/*
===========================================================
Screenshots uploading
===========================================================
*/

function addScreenshot() {
    screenshotIndex++;
    var input = document.createElement("input"), fileId = `scr${screenshotIndex.toString()}`;
    input.type = "file";
    input.id = `screenshotInput-${fileId}`;
    input.multiple = true;
    input.classList.add("hidden");
    input.setAttribute("file-id", fileId);
    input.setAttribute("accept", ".jpg, .png, .jpeg, .webp");
    input.onchange = function() {
        processScreenshot(this);
    }
    document.getElementById("files").appendChild(input);
    input.click();
}

/**
 * @param {HTMLInputElement} input
 */
async function processScreenshot(input) {
    try {
        var files = input.files;
        if (!files.length) return toastr.error("Vui lòng chọn một tệp tin.", "Lỗi");
        for (var i = 0; i < files.length; i++) {
			screenshotIndex++;
            var file = files[i], id = `scr${screenshotIndex.toString()}`;
            screenshots[id] = {
                path: null
            };
            createScreenshotElement(id);
            const path = await uploadFile(file, document.getElementById(`screenshotProgressBar-${id}`), id);
            if (screenshots[id]) {
                screenshots[id].path = path;
                AutoSave.save();
                $(`#screenshotContent-${id}`).html(`
                    <img src="/uploads/${path}" class="upload_screenshot_image" />
                `);
            }
        }
        input.remove();
    }
    catch (err) {
        console.error(err);
        document.getElementById(`screenshotContainer-${id}`)?.remove();
    }
}

/**
 * @param {string} id 
 * @param {File} file 
 */
function createScreenshotElement(id) {
    var div = document.createElement("div");
    div.classList.add("upload_screenshot");
    div.id = `screenshotContainer-${id}`;
    div.innerHTML = `
        <div style="text-align: right"><button type="button" onclick="deleteScreenshot('${id}')" class="upload_close_btn">X</button></div>
        <div id="screenshotContent-${id}" style="margin-top: 5px">
            <div class="progressbar_container" id="screenshotProgressBar-${id}">
                <div class="progressbar"></div>
            </div>
        </div>
    `;
    document.getElementById("screenshots").appendChild(div);
}

/**
 * 
 * @param {string} id 
 */
function deleteScreenshot(id, permanent = true) {
    cancelling[id] = true;
    var path = screenshots[id]?.path;
    if (!screenshots[id]?.path && requests[id]) {
        requests[id].abort();
        delete requests[id];
    }
    delete cancelling[id];
    delete screenshots[id];
    AutoSave.save();
    if (permanent) {
        try {
            if (path) apiRequest({
                url: "/api/delete_file",
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: JSON.stringify({file: path}),
                json: true
            })
        }
        catch (err) {
            console.error(err);
        }
    }
    document.getElementById(`screenshotContainer-${id}`).remove();
}

function alertNoSubmit(text) {
    toastr.error(text, "Lỗi");
    return false;
}

function processSubmit() {
    var gameFilesArr = Object.values(gameFiles);
    if (!gameFilesArr.length) return alertNoSubmit("Vui lòng tải lên một tệp tin trước.");
    for (var i = 0; i < gameFilesArr.length; i++) {
        if (!gameFilesArr[i]?.path) return alertNoSubmit(`Tệp tin số ${i + 1} bị lỗi trong quá trình tải lên, vui lòng xoá tệp tin đó và tải lên lại.`);
    }
    var screenshotArr = Object.values(screenshots);
    if (!screenshotArr.length) return alertNoSubmit("Vui lòng tải lên một ảnh chụp màn hình trước.");
    for (var i = 0; i < screenshotArr.length; i++) {
        if (!screenshotArr[i]) return alertNoSubmit(`Ảnh chụp màn hình số ${i + 1} bị lỗi trong quá trình tải lên, vui lòng xoá ảnh chụp đó và tải lên lại.`);
    }
    $("#linksInput").val(JSON.stringify(Object.values(gameFiles)));
    $("#screenshotsInput").val(JSON.stringify(Object.values(screenshots).map(obj => obj.path)));
    var checkboxes = document.getElementsByClassName("supported_os_checkbox"), supportedOS = [];
    for (var i = 0; i < checkboxes.length; i++) if (checkboxes[i].checked) supportedOS.push(checkboxes[i].value);
    $("#supportedOSInput").val(supportedOS.join(","));
    return true;
}

/*
===========================================================
Uploaded data preload
===========================================================
*/

function createGameFileElementPreload(id, file) {
    var div = document.createElement("div");
    div.classList.add("upload_game_file");
    div.id = `gameFileContainer-${id}`;
    div.innerHTML = `
        <div class="row" style="color: #fff!important">
            <div class="col-md-8 col-lg-8">
                <div style="text-overflow: elipsis"><b>${file.name}</b></div>
            </div>
            <div class="col-md-4 col-lg-4">
                <div id="gameFileDisplay-${id}" style="text-align: right">
                    <button type="button" onclick="deleteGameFile('${id}', false)" class="upload_close_btn">X</button>
                </div>
            </div>
        </div>
    `;
    document.getElementById("gameFiles").appendChild(div);
}

function createScreenshotElementPreload(id, path) {
    var div = document.createElement("div");
    div.classList.add("upload_screenshot");
    div.id = `screenshotContainer-${id}`;
    div.innerHTML = `
        <div style="text-align: right"><button type="button" onclick="deleteScreenshot('${id}', false)" class="upload_close_btn">X</button></div>
        <div id="screenshotContent-${id}" style="margin-top: 5px">
            <img src="/uploads/${path}" class="upload_screenshot_image" />
        </div>
    `;
    document.getElementById("screenshots").appendChild(div);
}

function preload() {
    if ($(`#thumbnail`).val()) showThumbnailImage($(`#thumbnail`).val());
    if ($(`#linksInput`).val()) {
        var items = JSON.parse($(`#linksInput`).val());
        for (var item of items) {
            gameFilesIndex++;
            var id = `gf${gameFilesIndex.toString()}`;
            gameFiles[id] = item;
            createGameFileElementPreload(id, gameFiles[id]);
        }
    }
    if ($(`#screenshotsInput`).val()) {
        var items = JSON.parse($(`#screenshotsInput`).val());
        for (var item of items) {
            screenshotIndex++;
            var id = `scr${screenshotIndex.toString()}`;
            screenshots[id] = {path: item};
            createScreenshotElementPreload(id, item);
        }
    }
}

/*
===========================================================
Autosave
===========================================================
*/

var AutoSave = {
    _div: null,
    _timeout: null,
    div: function() {
        if (this._div) return this._div;
        var div = document.createElement("div");
        div.style = `position: fixed; top: 0px; right: 0px; padding: 10px; background-color: #af1932; color: #fff; font-size: 10pt; display: none`;
        document.body.appendChild(div);
        this._div = div;
        return div;
    },
    show: function() {
        this.div().style.display = "block";
    },
    hide: function() {
        var div = this.div();
        div.style.display = "none";
        div.style.backgroundColor = "#af1932";
    },
    changeText: function(text) {
        this.div().innerText = text;
    },
    changeColor: function(color) {
        this.div().style.backgroundColor = color;
    },
    props: ["name", "image", "description", "engine", "tags", "release_year", "author", "language", "translator", "status", "links", "screenshots", "supported_os"],
    elements: {
        description: "textarea",
        engine: "select",
        language: "select",
        status: "select"
    },
    save: function() {
        this.show();
        this.changeText("Đang tiến hành lưu bản nháp...");
        $("#linksInput").val(JSON.stringify(Object.values(gameFiles).filter(obj => obj.path != null)));
        $("#screenshotsInput").val(JSON.stringify(Object.values(screenshots).filter(obj => obj.path != null).map(obj => obj.path)));
        var checkboxes = document.getElementsByClassName("supported_os_checkbox"), supportedOS = [];
        for (var i = 0; i < checkboxes.length; i++) if (checkboxes[i].checked) supportedOS.push(checkboxes[i].value);
        var save = {};
        for (var i = 0; i < this.props.length; i++) if (!["supported_os"].includes(this.props[i])) save[this.props[i]] = $(`${this.elements[this.props[i]] || "input"}[name="${this.props[i]}"]`).val();
        save.supported_os = supportedOS;
        window.localStorage.setItem("nbhzvn_upload_autosave", JSON.stringify(save));
        this.changeColor("rgb(46, 142, 19)");
        this.changeText("Đã lưu dữ liệu hiện tại thành một bản nháp.");
        if (this._timeout) clearTimeout(this._timeout);
        this._timeout = setTimeout((function() {this.hide()}).bind(this), 5000);
    },
    load: function() {
        var json = window.localStorage.getItem("nbhzvn_upload_autosave");
        if (!json) return $("#deleteDraftBtn").css("display", "none");
        this.show();
        this.changeText("Đang tải bản nháp đã lưu trước đó...");
        var save = JSON.parse(json);
        var savePart = Object.keys(save).filter(key => !(["supported_os"].includes(key)));
        for (var i = 0; i < savePart.length; i++) {
            var prop = savePart[i];
            $(`${this.elements[prop] || "input"}[name="${prop}"]`).val(save[savePart[i]]);
        }
        for (var i = 0; i < save.supported_os.length; i++) $(`input[class="supported_os_checkbox"][value="${save.supported_os[i]}"]`).prop("checked", true);
        preload();
        this.hide();
        toastr.success("Đã tải bản nháp đã lưu trước đó.", "Thông báo");
    },
    delete: function() {
        if (!confirm("Xác nhận muốn xoá bản nháp đã lưu?")) return;
        window.localStorage.removeItem("nbhzvn_upload_autosave");
        toastr.success("Đã xoá bản nháp đã lưu.", "Thông báo");
        window.location.reload();
    },
    events: function() {
        for (var i = 0; i < this.props.length; i++) {
            var prop = this.props[i];
            $(`${this.elements[prop] || "input"}[name="${prop}"]`).blur(function() {
                AutoSave.save();
            });
        }
    }
}

if (document.location.pathname.includes("upload")) {
    AutoSave.load();
    AutoSave.events();
}
else preload();