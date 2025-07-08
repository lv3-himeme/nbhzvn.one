function convertSeconds(seconds) {
    var days = Math.floor(seconds / 86400);
    seconds -= days * 86400;
    var hours = Math.floor(seconds / 3600);
    seconds -= hours * 3600;
    var minutes = Math.floor(seconds / 60);
    seconds -= minutes * 60;
    return {days, hours, minutes, seconds};
}

var interval = setInterval(function() {
    var remainingTime = endingTime - Math.ceil(new Date().getTime() / 1000);
    if (remainingTime <= 0) {
        clearInterval(interval);
        return document.location.reload();
    }
    var {days, hours, minutes, seconds} = convertSeconds(remainingTime);
    $("#countdownDays").text(`0${days}`.slice(-2));
    $("#countdownHours").text(`0${hours}`.slice(-2));
    $("#countdownMinutes").text(`0${minutes}`.slice(-2));
    $("#countdownSeconds").text(`0${seconds}`.slice(-2));
}, 1000);