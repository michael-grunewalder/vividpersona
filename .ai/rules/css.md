---
paths:
  - 'resources/css/**'
---

# Css

## Branding lives in the DaisyUI theme blocks
Brand colors/theming are defined by two `@plugin "daisyui/theme"` blocks in resources/css/app.css: `vividpersona` (light, default) and `vividpersona-dark` (prefersdark). Change tokens there, never hardcode brand colors in views. Theme names are also referenced in components/layouts/head.blade.php (prepaint), layouts/app.blade.php, layouts/dashboard.blade.php, and components/theme-toggle.blade.php — update all five when renaming. Documented in README "Adjusting branding colors".
