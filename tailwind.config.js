/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Services/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
            },
            colors: {
                brand: {
                    50: '#eef7f2',
                    100: '#d5ece0',
                    600: '#16794c',
                    700: '#11603c',
                    800: '#0d4c30',
                    900: '#0a3a25',
                },
            },
        },
    },
    plugins: [],
};
