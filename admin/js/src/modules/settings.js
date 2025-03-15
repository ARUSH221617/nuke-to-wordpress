import Swal from "sweetalert2";

export function initializeSettings() {
  // Early return if we're not on the settings page
  if (!document.querySelector("#nuke-to-wordpress-settings-form")) {
    return;
  }

  // Initialize form handling
  initializeForm();
  initializePasswordToggle();
  initializeTestConnection();
  initializeCronToggle();
  initializeMigrationMode();
}

function initializeForm() {
  const settingsForm = document.getElementById(
    "nuke-to-wordpress-settings-form"
  );
  const nonceInput = document.getElementById(
    "nuke_to_wordpress_settings_nonce"
  );

  if (!settingsForm || !nonceInput) {
    console.warn("Settings form or nonce not found");
    return;
  }

  settingsForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Show loading state
    Swal.fire({
      title: "Saving settings...",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    const formData = new FormData(this);
    const formDataObj = {};

    formData.forEach((value, key) => {
      const matches = key.match(/nuke_to_wordpress_settings_\[(.*?)\]/);
      if (matches && matches[1]) {
        formDataObj[matches[1]] = value;
      } else {
        const fieldName = key.replace("nuke_to_wordpress_settings_", "");
        formDataObj[fieldName] = value;
      }
    });

    // Safely get checkbox values
    const cronCheckbox = document.getElementById(
      "nuke_to_wordpress_settings_disable_wp_cron"
    );
    const debugCheckbox = document.getElementById(
      "nuke_to_wordpress_settings_enable_debug"
    );

    if (cronCheckbox) {
      formDataObj["disable_wp_cron"] = cronCheckbox.checked;
    }
    if (debugCheckbox) {
      formDataObj["enable_debug"] = debugCheckbox.checked;
    }

    try {
      const response = await fetch(ajaxurl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "save_nuke_settings",
          nonce: nonceInput.value,
          settings: JSON.stringify(formDataObj),
        }),
      });

      const data = await response.json();

      if (data.success) {
        Swal.fire({
          icon: "success",
          title: "Success!",
          text: data.data?.message || "Settings saved successfully",
          confirmButtonColor: "hsl(var(--primary))",
          customClass: {
            confirmButton: "px-4 py-2 rounded-md",
          },
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.data?.message || "Failed to save settings",
          confirmButtonColor: "hsl(var(--primary))",
          customClass: {
            confirmButton: "px-4 py-2 rounded-md",
          },
        });
      }
    } catch (error) {
      console.error("Settings submission error:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Failed to save settings. Please try again.",
        confirmButtonColor: "hsl(var(--primary))",
        customClass: {
          confirmButton: "px-4 py-2 rounded-md",
        },
      });
    }
  });
}

function initializePasswordToggle() {
  const toggleBtn = document.getElementById("toggle-password");
  const passwordInput = document.getElementById("nuke_db_password");

  if (!toggleBtn || !passwordInput) return;

  toggleBtn.addEventListener("click", function () {
    const type =
      passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);

    const svg = this.querySelector("svg");
    if (!svg) return;

    if (type === "text") {
      svg.innerHTML =
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
    } else {
      svg.innerHTML =
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
    }
  });
}

function initializeTestConnection() {
  const testBtn = document.getElementById("test-connection");
  if (!testBtn) return;

  testBtn.addEventListener("click", function () {
    const formData = new FormData(
      document.getElementById("nuke-to-wordpress-settings-form")
    );
    const connectionStatus = document.getElementById("connection-status");
    if (!connectionStatus) return;

    const spinner = connectionStatus.querySelector(".connection-spinner");
    const statusText = connectionStatus.querySelector(".connection-text");
    if (!spinner || !statusText) return;

    // Test connection logic here...
  });
}

function initializeCronToggle() {
  const cronToggle = document.getElementById("disable_wp_cron");
  if (!cronToggle) return;

  cronToggle.addEventListener("change", function () {
    // Cron toggle logic here...
  });
}

function initializeMigrationMode() {
  const modeInputs = document.querySelectorAll('input[name="migration_mode"]');
  if (!modeInputs.length) return;

  modeInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const isManual = this.value === "manual";
      const manualControls = document.getElementById("manual-controls");
      const migrationForm = document.getElementById("migration-form");

      if (manualControls) {
        manualControls.classList.toggle("hidden", !isManual);
      }
      if (migrationForm) {
        migrationForm.classList.toggle("hidden", isManual);
      }
    });
  });
}

// Add this function to show notifications anywhere in your code
export function showNotification(options) {
  return Swal.fire({
    icon: options.icon || "info",
    title: options.title || "",
    text: options.text || "",
    toast: options.toast || false,
    position: options.position || "center",
    showConfirmButton:
      options.showConfirmButton !== undefined
        ? options.showConfirmButton
        : true,
    timer: options.timer || null,
    timerProgressBar: options.timerProgressBar || false,
    confirmButtonColor: "hsl(var(--primary))",
    customClass: {
      confirmButton: "px-4 py-2 rounded-md",
    },
    ...options,
  });
}
