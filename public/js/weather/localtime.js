document.addEventListener("DOMContentLoaded", function(event) {
    var date;
    var times = document.getElementsByClassName('time');

    for (var i = 0; i < times.length; i++) {
        date = new Date(times[i].textContent);
        times[i].textContent = date.toLocaleString();
    }
});
