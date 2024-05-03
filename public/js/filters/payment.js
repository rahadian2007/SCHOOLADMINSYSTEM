const url = new URL(window.location.href);
const statusFilter = document.getElementById("filter-status");
if (statusFilter) {
    statusFilter.addEventListener("change", ({ target: { value } }) => {
        if (value) {
            url.searchParams.set("status", value);
        } else {
            url.searchParams.delete("status");
        }
        url.searchParams.delete("page");
        window.location.href = url;
    });
}

const periodFilter = document.getElementById("filter-period");
if (periodFilter) {
    periodFilter.addEventListener("change", ({ target: { value } }) => {
        if (value) {
            url.searchParams.set("period", value);
        } else {
            url.searchParams.delete("period");
        }
        url.searchParams.delete("page");
        window.location.href = url;
    });
}

const qFilter = document.getElementsByName("q")[0];
if (qFilter) {
    qFilter.addEventListener("blur", ({ target: { value } }) => {
        if (value) {
            url.searchParams.set("q", value);
        } else {
            url.searchParams.delete("q");
        }
        url.searchParams.delete("page");
        window.location.href = url;
    });
}
