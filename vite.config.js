import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite"; // <--- Tambahkan import ini

export default defineConfig({
    plugins: [
        tailwindcss(), // <--- Tambahkan plugin ini
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
});
