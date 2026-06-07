document.addEventListener("DOMContentLoaded", function () {

    const countries = [
        { code: "+91", iso: "in" },
        { code: "+1", iso: "us" },
        { code: "+44", iso: "gb" },
        { code: "+61", iso: "au" },
        { code: "+971", iso: "ae" }
    ];

    const countrySelect = document.getElementById("countryCode");
    const flagImg = document.getElementById("flag");

    // Safety check
    if (!countrySelect || !flagImg) {
        console.error("Country dropdown or flag image not found");
        return;
    }

    // Populate dropdown
    countries.forEach((country, index) => {
        const option = document.createElement("option");
        option.value = country.code;
        option.textContent = country.code;
        option.setAttribute("data-flag", country.iso);

        if (index === 0) option.selected = true;

        countrySelect.appendChild(option);
    });

    // Default flag
    flagImg.src = "https://flagcdn.com/w20/in.png";

    // Change flag
    countrySelect.addEventListener("change", function () {
        const iso = this.options[this.selectedIndex].dataset.flag;
        flagImg.src = `https://flagcdn.com/w20/${iso}.png`;
    });

});
