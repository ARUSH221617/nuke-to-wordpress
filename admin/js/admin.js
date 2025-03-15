jQuery(document).ready(function ($) {
  // Store initial form values
  const initialValues = {
    nuke_db_host: $("#nuke_db_host").val(),
    nuke_db_name: $("#nuke_db_name").val(),
    nuke_db_user: $("#nuke_db_user").val(),
    nuke_db_password: $("#nuke_db_password").val(),
  };

  // Toggle password visibility
  $("#toggle-password").on("click", function () {
    const passwordInput = $("#nuke_db_password");
    const type =
      passwordInput.attr("type") === "password" ? "text" : "password";
    passwordInput.attr("type", type);

    // Update icon
    const svg = $(this).find("svg");
    if (type === "text") {
      svg.html(
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>'
      );
    } else {
      svg.html(
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>'
      );
    }
  });

  // Test connection
  $("#test-connection").on("click", function () {
    const formData = $("#nuke-to-wordpress-settings-form").serialize();
    const connectionStatus = $("#connection-status");
    const spinner = connectionStatus.find(".connection-spinner");
    const statusText = connectionStatus.find(".connection-text");

    connectionStatus
      .removeClass("bg-emerald-50 bg-destructive/15")
      .addClass("bg-secondary")
      .show();
    spinner.show();
    statusText.text("Testing connection...");

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "test_nuke_connection",
        nonce: $("#nuke_to_wordpress_settings_nonce").val(),
        formData: formData,
      },
      success: function (response) {
        spinner.hide();
        if (response.success) {
          connectionStatus
            .removeClass("bg-secondary bg-destructive/15")
            .addClass("bg-emerald-50");
          statusText.html(
            '<span class="text-emerald-700">✓ Connection successful</span>'
          );
        } else {
          connectionStatus
            .removeClass("bg-secondary bg-emerald-50")
            .addClass("bg-destructive/15");
          statusText.html(
            `<span class="text-destructive">✕ ${response.data.message}</span>`
          );
        }
      },
      error: function () {
        spinner.hide();
        connectionStatus
          .removeClass("bg-secondary bg-emerald-50")
          .addClass("bg-destructive/15");
        statusText.html(
          '<span class="text-destructive">✕ Connection test failed</span>'
        );
      },
    });
  });

  // Form submission handler
  $("#nuke-to-wordpress-settings-form").on("submit", function (e) {
    e.preventDefault();

    const form = $(this);
    const messageDiv = $("#settings-message");
    
    // Get form data as an object
    const formDataObj = {};
    form.serializeArray().forEach(item => {
        // Handle both regular fields and array notation fields
        if (item.name.includes('nuke_to_wordpress_settings')) {
            const matches = item.name.match(/\[(.*?)\]$/);
            if (matches && matches[1]) {
                formDataObj[matches[1]] = item.value;
            } else {
                // For checkboxes and other fields that might not use array notation
                const fieldName = item.name.replace('nuke_to_wordpress_settings_', '');
                formDataObj[fieldName] = item.value;
            }
        }
    });

    // Add checkbox values (they're only included in serialization when checked)
    formDataObj['disable_wp_cron'] = $('#nuke_to_wordpress_settings_disable_wp_cron').is(':checked');
    formDataObj['enable_debug'] = $('#nuke_to_wordpress_settings_enable_debug').is(':checked');

    $.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: 'json',
        data: {
            action: 'save_nuke_settings',
            nonce: $('#nuke_to_wordpress_settings_nonce').val(),
            settings: formDataObj
        },
        beforeSend: function () {
            $("#submit, #test-connection, #cancel-settings")
                .prop("disabled", true)
                .addClass("opacity-50");
            
            messageDiv
                .removeClass()
                .addClass("bg-secondary text-secondary-foreground rounded-lg border px-4 py-3 text-sm")
                .html('<div class="flex items-center gap-2"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving settings...</div>')
                .show();
        },
        success: function (response) {
            $("#submit, #test-connection, #cancel-settings")
                .prop("disabled", false)
                .removeClass("opacity-50");

            if (response.success) {
                messageDiv
                    .removeClass()
                    .addClass("bg-emerald-50 text-emerald-700 border-emerald-200 rounded-lg border px-4 py-3 text-sm")
                    .html(
                        `<div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>${response.data.message}</span>
                        </div>`
                    )
                    .show();

                // Update initial values after successful save
                initialValues.nuke_db_host = $("#nuke_db_host").val();
                initialValues.nuke_db_name = $("#nuke_db_name").val();
                initialValues.nuke_db_user = $("#nuke_db_user").val();
                initialValues.nuke_db_password = $("#nuke_db_password").val();
            } else {
                messageDiv
                    .removeClass()
                    .addClass("bg-destructive/15 text-destructive border-destructive/20 rounded-lg border px-4 py-3 text-sm")
                    .html(
                        `<div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span>Error: ${response.data.message}</span>
                        </div>`
                    )
                    .show();
            }
        },
        error: function (xhr, status, error) {
            $("#submit, #test-connection, #cancel-settings")
                .prop("disabled", false)
                .removeClass("opacity-50");

            messageDiv
                .removeClass()
                .addClass("bg-destructive/15 text-destructive border-destructive/20 rounded-lg border px-4 py-3 text-sm")
                .html(
                    `<div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Error: Network error occurred</span>
                    </div>`
                )
                .show();
        }
    });
  });

  // Cancel button handler
  $("#cancel-settings").on("click", function () {
    // Reset form to initial values
    $("#nuke_db_host").val(initialValues.nuke_db_host);
    $("#nuke_db_name").val(initialValues.nuke_db_name);
    $("#nuke_db_user").val(initialValues.nuke_db_user);
    $("#nuke_db_password").val(initialValues.nuke_db_password);

    // Hide any messages
    $("#settings-message, #connection-status").hide();

    // Remove focus from the cancel button
    $(this).blur();
  });

  // Add this to your existing jQuery ready function
  $("#disable_wp_cron").on("change", function () {
    const isDisabled = $(this).is(":checked");
    const statusDiv = $("#cron-status");

    statusDiv
      .html(
        '<div class="flex items-center gap-2"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>Updating cron configuration...</div>'
      )
      .removeClass("hidden");

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "toggle_wp_cron",
        nonce: $("#nuke_to_wordpress_settings_nonce").val(),
        disable_cron: isDisabled,
      },
      success: function (response) {
        if (response.success) {
          statusDiv.html(
            `<div class="text-success">${response.data.message}</div>`
          );
        } else {
          statusDiv.html(
            `<div class="text-destructive">${response.data.message}</div>`
          );
          // Revert checkbox if operation failed
          $("#disable_wp_cron").prop("checked", !isDisabled);
        }
      },
      error: function () {
        statusDiv.html(
          '<div class="text-destructive">Failed to update cron configuration</div>'
        );
        $("#disable_wp_cron").prop("checked", !isDisabled);
      },
    });
  });

  // Add to your existing jQuery ready function
  $('input[name="migration_mode"]').on("change", function () {
    const isManual = $(this).val() === "manual";
    $("#manual-controls").toggleClass("hidden", !isManual);
    $("#migration-form").toggleClass("hidden", isManual);
  });

  $(".manual-step-btn").on("click", function () {
    const $btn = $(this);
    const step = $btn.data("step");

    if ($btn.prop("disabled")) {
      return;
    }

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

          // Enable next step button
          $btn.next(".manual-step-btn").prop("disabled", false);

          // Update progress
          if (response.data.progress) {
            updateProgress(response.data.progress);
          }
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
  });

  function updateProgress(progress) {
    const { processed_items, total_items } = progress;
    const percentage = Math.round((processed_items / total_items) * 100);

    $("#progress-bar").css("width", percentage + "%");
    $("#progress-text").text(
      `${processed_items} of ${total_items} items processed (${percentage}%)`
    );
  }

  function showError(message) {
    $("#error-message")
      .removeClass("hidden")
      .html(`<div class="text-destructive">${message}</div>`);
  }

  // Add to your existing jQuery ready function
  $("#view-logs").on("click", function () {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "view_debug_logs",
        nonce: $("#nuke_to_wordpress_settings_nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          const logViewer = $(`
            <div class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50">
              <div class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] 
                translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 
                rounded-lg">
                <div class="flex flex-col space-y-4">
                  <h2 class="text-lg font-semibold">Debug Logs</h2>
                  <pre class="bg-muted p-4 rounded-md text-sm overflow-auto max-h-[400px]">${response.data.logs}</pre>
                  <button type="button" class="close-logs inline-flex items-center justify-center 
                    rounded-md text-sm font-medium bg-primary text-primary-foreground 
                    hover:bg-primary/90 h-9 px-4 py-2">
                    Close
                  </button>
                </div>
              </div>
            </div>
          `);
          
          $("body").append(logViewer);
          logViewer.find(".close-logs").on("click", function() {
            logViewer.remove();
          });
        }
      }
    });
  });

  $("#clear-logs").on("click", function () {
    if (!confirm("Are you sure you want to clear all debug logs?")) {
      return;
    }

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "clear_debug_logs",
        nonce: $("#nuke_to_wordpress_settings_nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          $("#settings-message")
            .removeClass()
            .addClass("bg-emerald-50 text-emerald-700 border-emerald-200 p-4 rounded-md mb-6")
            .html("Debug logs cleared successfully")
            .show();
        }
      }
    });
  });
});
