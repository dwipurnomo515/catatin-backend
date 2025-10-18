import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],

    // START KOREKSI PENTING
    build: {
        // Mengatur base path untuk aset yang di-generate.
        // Ini memastikan aset diload dari root domain di Vercel.
        base: "/",
    },
    // END KOREKSI PENTING
});
