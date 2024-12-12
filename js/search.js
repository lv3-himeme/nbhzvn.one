/*
===========================================================
Page navigation
===========================================================
*/

async function previousPage() {
    if (Pagination.page() < 2) return;
    $("#games").html("");
    $("#games").html(await Pagination.previous(`/api/games/search${window.location.search}&page={page}&html=true`));
}

async function nextPage() {
    if (Pagination.page() >= Pagination.maxPages()) return;
    $("#games").html("");
    $("#games").html(await Pagination.next(`/api/games/search${window.location.search}&page={page}&html=true`));
}

async function jumpToPage() {
    if (Pagination.page() >= Pagination.maxPages()) return;
    $("#games").html("");
    $("#games").html(await Pagination.jumpToPage(`/api/games/search${window.location.search}&page={page}&html=true`, Pagination.page()));
}