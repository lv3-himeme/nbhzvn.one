/*
===========================================================
Page navigation
===========================================================
*/

async function previousPage() {
    if (Pagination.page() < 2) return;
    $("#games").html("");
    $("#games").html(await Pagination.previous(`/api/games/${repo}/?page={page}&html=true`));
}

async function nextPage() {
    if (Pagination.page() >= Pagination.maxPages()) return;
    $("#games").html("");
    $("#games").html(await Pagination.next(`/api/games/${repo}/?page={page}&html=true`));
}

async function jumpToPage() {
    if (Pagination.page() >= Pagination.maxPages()) return;
    $("#games").html("");
    $("#games").html(await Pagination.jumpToPage(`/api/games/${repo}/?page={page}&html=true`, Pagination.page()));
}