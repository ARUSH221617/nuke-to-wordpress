export function initializeMigration() {
  const migrationNonce = document.getElementById("migration_nonce");
  const manualStepButtons = document.querySelectorAll(".manual-step-btn");
  const migrationForm = document.getElementById("migration-form");

  // Initialize manual step buttons if they exist
  if (manualStepButtons.length > 0 && migrationNonce) {
    initializeMigrationButtons(manualStepButtons, migrationNonce);
  }

  // Initialize migration form if it exists
  if (migrationForm && migrationNonce) {
    initializeMigrationForm(migrationForm, migrationNonce);
  }

  // Initialize progress handling if we're on the progress page
  if (document.querySelector("#migration-progress")) {
    initializeProgressHandling();
  }
}

function initializeMigrationButtons(buttons, nonceElement) {
  buttons.forEach((btn) => {
    btn.addEventListener("click", function () {
      const step = this.dataset.step;
      if (!step) return;

      this.disabled = true;
      const statusEl = this.querySelector(".step-status");
      if (!statusEl) return;

      statusEl.innerHTML =
        '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>';

      fetch(ajaxurl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "manual_migration_step",
          step: step,
          nonce: nonceElement.value,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            statusEl.innerHTML = '<span class="text-success">✓ Complete</span>';

            // Enable next step button if it exists
            const nextBtn = this.nextElementSibling;
            if (nextBtn?.classList.contains("manual-step-btn")) {
              nextBtn.disabled = false;
            }

            // Update progress if available
            if (data.data?.progress) {
              updateProgress(data.data.progress);
            }
          } else {
            statusEl.innerHTML =
              '<span class="text-destructive">✗ Failed</span>';
            this.disabled = false;
            showError(data.data?.message || "Step failed");
          }
        })
        .catch(() => {
          statusEl.innerHTML = '<span class="text-destructive">✗ Error</span>';
          this.disabled = false;
          showError("Network error occurred");
        });
    });
  });
}

function initializeMigrationForm(form, nonceElement) {
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    startMigration(nonceElement.value);
  });
}

function startMigration(nonce) {
  fetch(ajaxurl, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      action: "start_migration",
      nonce: nonce,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        showError(data.data?.message || "Failed to start migration");
      }
    })
    .catch(() => {
      showError("Failed to start migration");
    });
}

function updateProgress(progress) {
  const { processed_items, total_items } = progress;
  const percentage = Math.round((processed_items / total_items) * 100);

  const progressBar = document.getElementById("progress-bar");
  const progressText = document.getElementById("progress-text");

  if (progressBar) {
    progressBar.style.width = percentage + "%";
  }
  if (progressText) {
    progressText.textContent = `${processed_items} of ${total_items} items processed (${percentage}%)`;
  }
}

function showError(message) {
  const errorContainer = document.querySelector(".error-message");
  if (errorContainer) {
    errorContainer.innerHTML = `
      <div class="bg-destructive/15 text-destructive rounded-lg p-4 mb-6">
        <p class="text-sm font-medium">${message}</p>
      </div>
    `;
    errorContainer.classList.remove("hidden");
  }
}

function initializeProgressHandling() {
  // Add any additional progress handling initialization here
}
