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
