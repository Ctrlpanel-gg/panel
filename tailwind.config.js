/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: "selector",

    content: [
        "./themes/default/views/**/*.blade.php",
        "./themes/default/js/**/*.js",
        "./themes/default/css/**/*.css",
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    100: "rgb(var(--primary-100) / <alpha-value>)",
                    200: "rgb(var(--primary-200) / <alpha-value>)",
                    300: "rgb(var(--primary-300) / <alpha-value>)",
                    400: "rgb(var(--primary-400) / <alpha-value>)",
                    500: "rgb(var(--primary-500) / <alpha-value>)",
                    600: "rgb(var(--primary-600) / <alpha-value>)",
                    700: "rgb(var(--primary-700) / <alpha-value>)",
                },
                accent: {
                    50: "rgb(var(--accent-50) / <alpha-value>)",
                    100: "rgb(var(--accent-100) / <alpha-value>)",
                    200: "rgb(var(--accent-200) / <alpha-value>)",
                    300: "rgb(var(--accent-300) / <alpha-value>)",
                    400: "rgb(var(--accent-400) / <alpha-value>)",
                    500: "rgb(var(--accent-500) / <alpha-value>)",
                    600: "rgb(var(--accent-600) / <alpha-value>)",
                    700: "rgb(var(--accent-700) / <alpha-value>)",
                    800: "rgb(var(--accent-800) / <alpha-value>)",
                    blue: "#3b82f6",
                    amber: "#f59e0b",
                    emerald: "#10b981",
                    red: "#ef4444",
                },
                success: "rgb(var(--success) / <alpha-value>)",
                warning: "rgb(var(--warning) / <alpha-value>)",
                info: "rgb(var(--info) / <alpha-value>)",
                danger: "rgb(var(--danger) / <alpha-value>)",
                cyan: "rgb(var(--cyan) / <alpha-value>)",
                gray: {
                    50: "rgb(var(--gray-50) / <alpha-value>)",
                    100: "rgb(var(--gray-100) / <alpha-value>)",
                    200: "rgb(var(--gray-200) / <alpha-value>)",
                    300: "rgb(var(--gray-300) / <alpha-value>)",
                    400: "rgb(var(--gray-400) / <alpha-value>)",
                    500: "rgb(var(--gray-500) / <alpha-value>)",
                    600: "rgb(var(--gray-600) / <alpha-value>)",
                    700: "rgb(var(--gray-700) / <alpha-value>)",
                    800: "rgb(var(--gray-800) / <alpha-value>)",
                    900: "rgb(var(--gray-900) / <alpha-value>)",
                },
                surface: {
                    DEFAULT: "rgb(24 24 27 / 0.5)",
                    border: "rgb(39 39 42 / 0.5)",
                },
                black: {
                    DEFAULT: "#000000",
                    100: "#05091D",
                },
            },

            boxShadow: {
                100: "0px 4px 4px rgba(0, 0, 0, 0.25), 0px 16px 24px rgba(0, 0, 0, 0.25), inset 0px 3px 6px #1959AD",
                200: "0px 4px 4px rgba(0, 0, 0, 0.25), 0px 16px 24px rgba(0, 0, 0, 0.25), inset 0px 4px 10px #3391FF",
                300: "0px 4px 4px rgba(0, 0, 0, 0.25), 0px 16px 24px rgba(0, 0, 0, 0.25), inset 0px 3px 6px #1959AD",
                400: "inset 0px 2px 4px 0 rgba(255, 255, 255, 0.05)",
                500: "0px 16px 24px rgba(0, 0, 0, 0.25), 0px -14px 48px rgba(40, 51, 111, 0.7)",
            },

            fontFamily: {
                inter: ["Inter", "sans-serif"],
                poppins: ["Poppins", "sans-serif"],
                oxanium: ["Oxanium", "sans-serif"],
            },

            transitionProperty: {
                borderColor: "border-color",
            },

            spacing: {
                "1/5": "20%",
                "2/5": "40%",
                "3/5": "60%",
                "4/5": "80%",
                "3/20": "15%",
                "7/20": "35%",
                "9/20": "45%",
                "11/20": "55%",
                "13/20": "65%",
                "15/20": "75%",
                "17/20": "85%",
                "19/20": "95%",
                22: "88px",
                100: "100px",
                330: "330px",
                388: "388px",
                400: "400px",
                440: "440px",
                512: "512px",
                640: "640px",
                960: "960px",
                1230: "1230px",
            },

            zIndex: {
                1: "1",
                2: "2",
                4: "4",
            },

            lineHeight: {
                12: "48px",
            },

            borderRadius: {
                14: "14px",
                20: "20px",
                40: "40px",
                half: "50%",
                "7xl": "40px",
            },

            flex: {
                50: "0 0 50%",
                100: "0 0 100%",
                280: "0 0 280px",
                256: "0 0 256px",
                300: "0 0 300px",
                320: "1px 0 320px",
                540: "0 0 540px",
            },

            backgroundImage: {
                "gradient-radial": "radial-gradient(var(--tw-gradient-stops))",
            },
        },
    },

    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
};
