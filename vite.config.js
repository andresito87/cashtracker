import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import {google} from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import inertia from "@inertiajs/vite";
import react from '@vitejs/plugin-react';

export default defineConfig({
	plugins: [laravel({
		input: ['resources/css/app.css', 'resources/js/inertia.tsx', 'resources/js/app.js'],
		refresh: true,
		fonts: [google('Plus Jakarta Sans', {
			weights: [400, 500, 600, 700, 800],
		}),],
	}), tailwindcss(), react(), inertia()], server: {
		watch: {
			ignored: ['**/storage/framework/views/**'],
		},
	},
});
