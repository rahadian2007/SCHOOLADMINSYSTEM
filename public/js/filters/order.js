const vendorFilter = document.getElementById("filter-vendor");
if (vendorFilter) {
    vendorFilter.addEventListener("change", ({ target: { value } }) => {
        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set("vendor", value);
        } else {
            url.searchParams.delete("vendor");
        }
        url.searchParams.delete("page");
        window.location.href = url;
    });
}

flatpickr("#date-picker-start,#date-picker-end", {});

const url = new URL(window.location.href);

const datePickerStart = document.getElementById("date-picker-start");
const datePickerEnd = document.getElementById("date-picker-end");
const resetBtn = document.getElementById("filter-reset");

datePickerStart.addEventListener("change", function ({ target: { value } }) {
    url.searchParams.set("start-date", value);
    window.location.href = url;
});

datePickerEnd.addEventListener("change", function ({ target: { value } }) {
    url.searchParams.set("end-date", value);
    window.location.href = url;
});

resetBtn.addEventListener("click", () => {
    url.searchParams.delete("start-date");
    url.searchParams.delete("end-date");
    url.searchParams.delete("vendor");
    window.location.href = url;
});

const currStartDate = url.searchParams.get("start-date");
const currEndDate = url.searchParams.get("end-date");

datePickerStart.value = currStartDate;
datePickerEnd.value = currEndDate;
