document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registrationForm");

    if (form) {
        form.addEventListener("submit", function (e) {
            const username = document.getElementById("regUsername").value.trim();
            const email = document.getElementById("regEmail").value.trim();
            const password = document.getElementById("regPassword").value;

            // 1. Definition Vectors for validation expressions
            const usernameRegex = /^[a-zA-Z0-9_]{4,20}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const passwordRegex = /(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}/;

            // Remove any old frontend alert elements if they exist
            const oldAlerts = document.querySelectorAll(".frontend-alert");
            oldAlerts.forEach(alert => alert.remove());

            let errorMsg = "";

            // Evaluate field entry states systematically
            if (!usernameRegex.test(username)) {
                errorMsg = "Username must be 4-20 characters and contain only letters, numbers, or underscores.";
            } else if (!emailRegex.test(email)) {
                errorMsg = "Please enter a completely valid email structure layout.";
            } else if (!passwordRegex.test(password)) {
                errorMsg = "Password must be at least 8 characters long, including 1 uppercase, 1 lowercase letter, and a number constraint.";
            }

            // If verification logic flags a layout failure pattern, block state transition execution
            if (errorMsg !== "") {
                e.preventDefault(); // Stop form submission entirely

                // Create a dynamic, temporary styling error card matching your glass UI theme 
                const alertDiv = document.createElement("div");
                alertDiv.className = "alert error frontend-alert";
                alertDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${errorMsg}`;

                // Insert the message right above the form elements inside the card layout
                form.parentNode.insertBefore(alertDiv, form);
                
                // Smoothly scroll to the top of the card view frame matrix area
                alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
});