/* ===============================
   COMMON UTILITIES
================================ */

// Show alert message (temporary)
function showAlert(message, type = "success") {
  const alert = document.createElement("div");
  alert.className = `
    fixed top-5 right-5 px-4 py-2 rounded shadow text-white z-50
    ${type === "success" ? "bg-green-600" : "bg-red-600"}
  `;
  alert.innerText = message;

  document.body.appendChild(alert);

  setTimeout(() => {
    alert.remove();
  }, 3000);
}

/* ===============================
   PROFILE PAGE JS
================================ */

document.addEventListener("DOMContentLoaded", () => {
  const profileForm = document.querySelector("form");

  if (profileForm && window.location.pathname.includes("profile")) {
    profileForm.addEventListener("submit", (e) => {
      e.preventDefault();

      // Collect form data
      const inputs = profileForm.querySelectorAll("input");
      const profileData = {};

      inputs.forEach(input => {
        profileData[input.previousElementSibling.innerText] = input.value;
      });

      // Save to localStorage (mock backend)
      localStorage.setItem("studentProfile", JSON.stringify(profileData));

      showAlert("Profile updated successfully!");
    });
  }
});

/* ===============================
   FILE UPLOAD PREVIEW (SUBMIT PAGE)
================================ */

document.addEventListener("DOMContentLoaded", () => {
  const fileInput = document.getElementById("fileUpload");

  if (fileInput) {
    fileInput.addEventListener("change", () => {
      const file = fileInput.files[0];

      if (!file) return;

      const allowedTypes = [
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/zip"
      ];

      if (!allowedTypes.includes(file.type)) {
        showAlert("Invalid file type!", "error");
        fileInput.value = "";
        return;
      }

      if (file.size > 10 * 1024 * 1024) {
        showAlert("File size exceeds 10MB!", "error");
        fileInput.value = "";
        return;
      }

      showAlert(`File selected: ${file.name}`);
    });
  }
});

/* ===============================
   ASSIGNMENT SUBMIT (FRONTEND ONLY)
================================ */

document.addEventListener("DOMContentLoaded", () => {
  const submitForm = document.querySelector("form");

  if (submitForm && window.location.pathname.includes("submit-assignment")) {
    submitForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const fileInput = document.getElementById("fileUpload");

      if (!fileInput || !fileInput.files.length) {
        showAlert("Please upload a file before submitting!", "error");
        return;
      }

      // Mock submission save
      const submission = {
        fileName: fileInput.files[0].name,
        submittedAt: new Date().toLocaleString(),
        status: "Submitted"
      };

      localStorage.setItem("lastSubmission", JSON.stringify(submission));

      showAlert("Assignment submitted successfully!");

      setTimeout(() => {
        window.location.href = "my-submissions.html";
      }, 1500);
    });
  }
});
