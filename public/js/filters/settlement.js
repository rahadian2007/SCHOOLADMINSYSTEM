const url = new URL(window.location.href);

flatpickr("#settlement-start-date", {});
flatpickr("#settlement-end-date", {});

const startDate = document.getElementById("settlement-start-date");
const endDate = document.getElementById("settlement-end-date");
const vendor = document.getElementById("filter-vendor");

startDate.addEventListener("change", function ({ target: { value } }) {
    url.searchParams.set("start-date", value);
    window.location.href = url;
});

endDate.addEventListener("change", function ({ target: { value } }) {
    url.searchParams.set("end-date", value);
    window.location.href = url;
});

vendor.addEventListener("change", function ({ target: { value } }) {
    url.searchParams.set("vendor", value);
    window.location.href = url;
});

const currStartDate = url.searchParams.get("start-date");
const currEndDate = url.searchParams.get("end-date");

startDate.value = currStartDate;
endDate.value = currEndDate;
