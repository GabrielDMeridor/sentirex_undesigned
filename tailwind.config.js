module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#6D28D9',
        success: '#10B981',
        error: '#EF4444',
        warning: '#F59E0B',
        info: '#3B82F6',
      },
    },
  },
  plugins: [require("daisyui")],
  daisyui: {
    themes: [
      {
        light: {
          "primary": "#6D28D9",
          "primary-content": "#ffffff",
          "secondary": "#5B21B6",
          "accent": "#4C1D95",
          "neutral": "#2A2E37",
          "base-100": "#FFFFFF",
          "base-200": "#F2F2F2",
          "base-300": "#E5E6E6",
          "success": "#10B981",
          "warning": "#F59E0B",
          "error": "#EF4444",
          "info": "#3B82F6",
        },
        dark: {
          "primary": "#6D28D9",
          "primary-content": "#ffffff",
          "secondary": "#5B21B6",
          "accent": "#4C1D95",
          "neutral": "#1F2937",
          "base-100": "#1E1E1E",
          "base-200": "#2D2D2D",
          "base-300": "#1F1F1F",
          "success": "#10B981",
          "warning": "#F59E0B",
          "error": "#EF4444",
          "info": "#3B82F6",
        },
      },
    ],
    darkTheme: "dark",
  },
}