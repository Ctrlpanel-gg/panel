import Alpine from "alpinejs";
import axios from "axios";
import Swal from "sweetalert2";
import tippy from "tippy.js";
import "tippy.js/dist/tippy.css";

import "@fortawesome/fontawesome-free/css/all.min.css";

window.Alpine = Alpine;
window.axios = axios;
window.Swal = Swal;
window.Tippy = tippy;

// Axios defaults
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Attach CSRF token from meta tag if present (Laravel)
const tokenMeta = document.head.querySelector('meta[name="csrf-token"]');
if (tokenMeta) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = tokenMeta.content;
}

// Alpine magic helper for currency formatting
Alpine.magic("currency", () => {
    return {
        format: (amount) => {
            return (amount / 1000).toFixed(2);
        },
    };
});

// Custom SweetAlert2 styling with accent colors
const SwalCustom = Swal.mixin({
    customClass: {
        popup: "rounded-xl bg-gray-800 border border-gray-700 shadow-2xl",
        title: "text-white font-semibold",
        htmlContainer: "text-gray-300",
        confirmButton:
            "bg-gradient-to-r from-accent-600 to-accent-500 hover:from-accent-500 hover:to-accent-600 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-200 shadow-lg hover:shadow-accent-500/50 hover:scale-105",
        cancelButton:
            "bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-200",
        actions: "gap-3",
    },
    buttonsStyling: false,
});

// Expose custom Swal
window.SwalCustom = SwalCustom;

// Start Alpine
Alpine.start();

// Initialize plugins when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
    // Initialize Select2 with Tailwind styling
    if (window.$ && typeof window.$.fn.select2 === "function") {
        window.$(".select2").select2({
            theme: "default",
            width: "100%",
            dropdownAutoWidth: true,
        });
    }

    // Initialize Tippy.js tooltips
    if (window.Tippy) {
        window.Tippy("[data-tippy-content]", {
            theme: "dark",
            placement: "top",
            arrow: true,
            animation: "scale",
            allowHTML: true,
            interactive: true,
            maxWidth: "28rem",
        });
    }
});
