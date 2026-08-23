document.addEventListener("DOMContentLoaded", function() {
    const tabBtns = document.querySelectorAll(".tab-btn[data-tab]");
    const tabContents = document.querySelectorAll(".tab-content");

    tabBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            // Remove active class from all buttons and contents
            tabBtns.forEach(b => b.classList.remove("active"));
            tabContents.forEach(c => c.classList.remove("active"));
            tabContents.forEach(c => c.hidden = true);

            // Add active class to clicked button
            this.classList.add("active");

            // Show corresponding content
            const tabId = this.getAttribute("data-tab");
            const targetContent = document.getElementById(tabId);
            if (targetContent) {
                targetContent.classList.add("active");
                targetContent.hidden = false;
            }
        });
    });
});
