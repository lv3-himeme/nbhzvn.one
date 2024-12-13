var gameFiles = {}, screenshots = {}, cancelling = {};

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
    var input = document.createElement("input"), fileId = Math.floor(Math.random() * 16777216).toString();
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
    }
    delete cancelling[id];
    delete gameFiles[id];
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
    var input = document.createElement("input"), fileId = Math.floor(Math.random() * 16777216).toString();
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
            var file = files[i], id = Math.floor(Math.random() * 16777216).toString();
            screenshots[id] = {
                path: null
            };
            createScreenshotElement(id);
            const path = await uploadFile(file, document.getElementById(`screenshotProgressBar-${id}`), id);
            if (screenshots[id]) {
                screenshots[id].path = path;
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

function processSubmit() {
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

if ($(`#thumbnail`).val()) showThumbnailImage($(`#thumbnail`).val());
if ($(`#linksInput`).val()) {
    var items = JSON.parse($(`#linksInput`).val());
    for (var item of items) {
        var id = Math.floor(Math.random() * 16777216).toString();
        gameFiles[id] = item;
        createGameFileElementPreload(id, gameFiles[id]);
    }
}
if ($(`#screenshotsInput`).val()) {
    var items = JSON.parse($(`#screenshotsInput`).val());
    for (var item of items) {
        var id = Math.floor(Math.random() * 16777216).toString();
        screenshots[id] = {path: item};
        createScreenshotElementPreload(id, item);
    }
}