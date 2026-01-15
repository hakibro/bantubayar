import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            colors: {
                primary: "#2563EB",
                accent: "#EF4444",
                success: "#10B981",
                bgBody: "#F3F4F6",
                textMuted: "#6B7280",
            },
        },
    },

    plugins: [forms],
};
