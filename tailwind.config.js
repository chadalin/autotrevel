module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      fontFamily: {
        'montserrat': ['Montserrat', 'sans-serif'],
        'open-sans': ['Open Sans', 'sans-serif'],
      },
      colors: {
        'primary': {
          50: '#fef7f3',
          100: '#ffede3',
          500: '#FF7A45',
          600: '#e56a35',
          700: '#cc5a25',
        },
        'dark': {
          800: '#0F2A44',
          900: '#0a1c2e',
        }
      },
      backgroundImage: {
        'gradient-adventure': 'linear-gradient(135deg, #FF7A45 0%, #0F2A44 100%)',
      },
      animation: {
        'pulse-slow': 'pulse 3s ease-in-out infinite',
        'float': 'float 6s ease-in-out infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        }
      }
    },
  },
  plugins: [
    require('@tailwindcss/line-clamp'),
  ],
}