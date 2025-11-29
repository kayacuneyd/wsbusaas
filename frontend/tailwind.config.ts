import typography from "@tailwindcss/typography";
import type { Config } from "tailwindcss";

export default {
  content: ["./src/**/*.{html,js,svelte,ts}"],

  theme: {
    extend: {
      colors: {
        brand: {
          light: '#D0DECB',
          dark: '#243B4D',
          text: '#212529',
          bg: '#F8F9FA'
        }
      }
    }
  },

  plugins: [typography]
} as Config;
