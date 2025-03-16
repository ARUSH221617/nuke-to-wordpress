import Swal from "sweetalert2";
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

// Code below this line was moved from admin/migration.php
jQuery(document).ready(function ($) {
  const CHECK_INTERVAL = 5000; // Check every 5 seconds
  let checkTimer;

  // Migration mode toggle
  $('input[name="migration_mode"]').on("change", function () {
    const isManual = $(this).val() === "manual";
    $("#manual-controls").toggleClass("hidden", !isManual);
    $("#migration-form").toggleClass("hidden", !isManual);
  });

  // Start migration form submission
  $("#migration-form").on("submit", function (e) {
    e.preventDefault();
    startMigration();
  });

  function startMigration() {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "start_migration",
        nonce: $("#migration_nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          showError(response.data.message || "Failed to start migration");
        }
      },
      error: function () {
        showError("Failed to start migration");
      },
    });
  }

  // Manual step execution
  $(".manual-step-btn").on("click", function () {
    const $btn = $(this);
    const step = $btn.data("step");

    if ($btn.prop("disabled")) {
      return;
    }

    executeManualStep($btn, step);
  });

  function executeManualStep($btn, step) {
    $btn
      .prop("disabled", true)
      .find(".step-status")
      .html(
        '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>'
      );

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "manual_migration_step",
        step: step,
        nonce: $("#migration_nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          $btn
            .find(".step-status")
            .html('<span class="text-success">✓ Complete</span>');
          $btn.next(".manual-step-btn").prop("disabled", false);
          updateProgress(response.data.progress);
        } else {
          $btn
            .find(".step-status")
            .html('<span class="text-destructive">✗ Failed</span>');
          $btn.prop("disabled", false);
          showError(response.data.message || "Step failed");
        }
      },
      error: function () {
        $btn
          .find(".step-status")
          .html('<span class="text-destructive">✗ Error</span>');
        $btn.prop("disabled", false);
        showError("Network error occurred");
      },
    });
  }

  // Progress checking for automatic mode
  if ($("#migration-progress").is(":visible")) {
    startStatusCheck();
  }

  function startStatusCheck() {
    checkMigrationStatus();
    checkTimer = setInterval(checkMigrationStatus, CHECK_INTERVAL);
  }

  function checkMigrationStatus() {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "check_migration_status",
        nonce: $("#migration_nonce").val(),
      },
      success: function (response) {
        if (response.success && response.data) {
          updateProgress(response.data);

          if (response.data.status === "completed") {
            clearInterval(checkTimer);
            location.reload();
          } else if (response.data.status === "failed") {
            clearInterval(checkTimer);
            location.reload();
          }
        }
      },
    });
  }

  function updateProgress(data) {
    if (!data) return;

    const progress = Math.round(data.progress || 0);
    $(".progress-bar").css("width", progress + "%");
    $(".progress-percentage").text(progress + "% Complete");
    $(".current-task").text(data.current_task || "Initializing...");
    $(".last-run span").text(data.last_run || "Just now");
  }

  function showError(message) {
    const errorHtml = `
            <div class="bg-destructive/15 text-destructive rounded-lg p-4 mb-6">
                <p class="text-sm font-medium">${message}</p>
            </div>
        `;

    $(".error-message").html(errorHtml).removeClass("hidden");
  }

  // Action buttons
  $("#retry-migration").on("click", function () {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "retry_migration",
        nonce: $("#migration_nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          showError("Failed to retry migration");
        }
      },
      error: function () {
        showError("Failed to retry migration");
      },
    });
  });

  $("#cancel-migration").on("click", function () {
    Swal.fire({
      title: "Are you sure?",
      text: "This will stop the migration process and rollback changes.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, cancel it!",
    }).then((result) => {
      if (!result.isConfirmed) {
        return;
      }

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "cancel_migration",
          nonce: $("#migration_nonce").val(),
        },
        success: function (response) {
          if (response.success) {
            location.reload();
          } else {
            showError("Failed to cancel migration");
          }
        },
        error: function () {
          showError("Failed to cancel migration");
        },
      });
    });
  });
});
