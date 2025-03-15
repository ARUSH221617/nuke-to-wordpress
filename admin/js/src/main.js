import "sweetalert2/dist/sweetalert2.min.css";
import { initializeSettings } from "./modules/settings";
import { initializeMigration } from "./modules/migration";

// Initialize modules when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  initializeSettings();
  initializeMigration();
});
