function secondsToString(seconds) {
    var hours = Math.floor(seconds / 3600);
    seconds -= hours * 3600;
    var minutes = Math.floor(seconds / 60);
    seconds -= minutes * 60;
    return `${`0${hours}`.slice(-2)}:${`0${minutes}`.slice(-2)}:${`0${seconds}`.slice(-2)}`;
}

const Speedrun = {
    infobox: null,
    module: null,
    username: "",
    playtime: 0,
    realPlaytime: 0,
    altTabCount: Number(window.localStorage.getItem("altTabCount") || 0),
    altTabSwitch: false,
    interval: null,
    createInfobox: function() {
        if (this.infobox) return;
        var infobox = this.infobox = document.createElement("div");
        infobox.style = `position: fixed; top: 0; left: 0; padding: 10px; background-color: #1f2122; border: 1px solid #ccc; color: #ccc; display: none`;
        infobox.innerHTML = `
            <div id="speedrunUsername" style="font-size: 12pt; margin: 5px"></div>
            <div id="speedrunPlaytime" style="font-size: 22pt; font-weight: bold; text-align: center; margin: 5px"></div>
            <div id="speedrunRealPlaytime" style="font-size: 14pt; text-align: center; margin: 5px"></div>
            <div style="font-size: 12pt; text-align: center; margin: 5px"><b>Số lần chuyển đổi cửa sổ:</b> <span id="speedrunAltTabCount"></span></div>
        `;
        document.body.appendChild(infobox);
    },
    showInfobox: function() {
        if (!this.infobox) this.createInfobox();
        this.infobox.style.display = "block";
    },
    hideInfobox: function() {
        if (!this.infobox) this.createInfobox();
        this.infobox.style.display = "none";
    },
    updateTime: function() {
        document.getElementById("speedrunUsername").innerText = this.username;
        document.getElementById("speedrunPlaytime").innerText = secondsToString(this.playtime);
        document.getElementById("speedrunRealPlaytime").innerText = secondsToString(this.realPlaytime);
        document.getElementById("speedrunAltTabCount").innerText = this.altTabCount;
    },
    altTab: function() {
        this.altTabCount++;
        if (this.altTabCount >= 10) {
            this.module.ccall("AltTabMute");
            this.altTabCount = 0;
        }
        window.localStorage.setItem("altTabCount", this.altTabCount.toString());
        this.updateTime();
    },
    addTime: function() {
        this.playtime++;
        this.realPlaytime++;
        this.updateTime();
    },
    start: function(username, playtime, realPlaytime) {
        this.createInfobox();
        this.showInfobox();
        this.username = username;
        this.playtime = playtime;
        this.realPlaytime = realPlaytime;
        this.updateTime();
        if (!this.interval) this.interval = setInterval((function() {
            this.addTime();
        }).bind(this), 1000);
    },
    stopTime: function() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        this.playtime = 0;
        this.realPlaytime = 0;
        if (this.infobox) this.updateTime();
    },
    stop: function() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        this.hideInfobox();
    }
};

document.addEventListener("visibilitychange", () => {
    if (!Speedrun.playtime) return;
    if (document.visibilityState === "hidden") {
        if (!Speedrun.altTabSwitch) {
            Speedrun.altTabSwitch = true;
            Speedrun.altTab();
        }
    }
    else if (document.visibilityState === "visible") {
        Speedrun.altTabSwitch = false;
    }
});

