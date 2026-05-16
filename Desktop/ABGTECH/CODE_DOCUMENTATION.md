# ABGTECH Website - Code Documentation

## Project Structure Overview

This document provides comprehensive documentation of the ABGTECH website codebase, organized by file and section.

---

## 📁 File Structure

```
ABGTECH/
├── index.php                 # Homepage - main landing page
├── about.php                 # About Us page
├── contact.php               # Contact form page
├── software.php              # Custom Software Development page
├── digitalmarketing.php      # Digital Marketing Services page
├── products.php              # Products overview page
├── sacco-system.php          # Sacco & Microfinance System product
├── school-management-system.php  # School Management System product
├── retail-pos-system.php     # Retail POS System product
├── medical-clinic-pharmacy-system.php  # Medical Clinic & Pharmacy System
├── assets/
│   ├── abg.css              # Main stylesheet with all CSS
│   ├── abg.js               # JavaScript for interactions
│   └── logo.png             # ABGTECH circular logo
├── includes/
│   └── site.php             # Shared PHP template functions
└── CODE_DOCUMENTATION.md    # This file
```

---

## 📄 File Documentation

### 1. **includes/site.php** - Shared Template Functions
Main file containing reusable PHP functions for all pages.

#### Functions:

##### `abg_head($title, $description)`
- **Purpose**: Output HTML head section with meta tags and stylesheets
- **Parameters**:
  - `$title` (string): Page title for `<title>` and meta tags
  - `$description` (string): Page meta description
- **Used in**: All pages
- **CSS Loaded**: Bootstrap, Bootstrap Icons, abg.css
- **Output**: `<html>`, `<head>`, and `<body>` opening tags

##### `abg_nav($active = '')`
- **Purpose**: Output navigation bar with mobile drawer menu
- **Parameters**:
  - `$active` (string): Current page identifier ('home', 'services', 'products', 'about', 'contact')
- **Components**:
  - Logo section (circular logo + company name + tagline)
  - Desktop navigation menu with dropdowns
  - Mobile hamburger button
  - Mobile drawer (hidden by default)
  - Drawer backdrop (semi-transparent overlay)
- **CSS Classes**: `.site-nav`, `.navbar-brand`, `.logo-img`, `.logo-text`, `.nav-link`, `.dropdown-menu`, `.drawer-backdrop`, `.mobile-drawer`, `.drawer-link`

##### `abg_footer()`
- **Purpose**: Output footer with company info, links, and social media
- **Sections**:
  - Company info with logo and tagline
  - Social media icons (Facebook, LinkedIn, Instagram, WhatsApp, X)
  - Quick links (Homepage, Products, About, Contact)
  - Services (Custom Software, Digital Marketing)
  - Contact info (Email, Phone, Location)
  - Copyright year (auto-updated via JavaScript)
- **CSS Classes**: `.footer`, `.footer-logo-wrapper`, `.footer-logo`, `.footer-logo-text`, `.social-row`

---

### 2. **assets/abg.css** - Main Stylesheet
Comprehensive CSS file organized in sections with comments.

#### CSS Sections:

**Section 1: CSS Custom Properties (Variables)**
- Color palette variables (`--ink`, `--navy`, `--royal`, `--blue`, `--cyan`, etc.)
- Shadow and spacing variables (`--shadow`, `--radius`)
- Glass morphism variables (`--glass`, `--glass-strong`)

**Section 2: Global & Reset Styles**
- `* { box-sizing: border-box; }`
- `html { scroll-behavior: smooth; }`
- `body` styling with gradient background
- Link styling

**Section 3: Navbar Styling - Fixed Navigation**
- `.site-nav` - Main navbar container (fixed at top)
- `.site-nav.is-scrolled` - Scrolled state (added by JS)
- `.brand-mark` - Legacy logo badge (for reference)
- `.logo-img` - Circular logo image (72px)
- `.logo-text` - Text container for company name and tagline
- `.logo-company` - Company name (ABGTECH CO. LTD)
- `.logo-tagline` - Tagline (Solutions That Drive Success)
- `.navbar-brand` - Brand container

**Section 4: Navigation Links & Menus**
- `.nav-link` - Main navigation links
- `.nav-link:hover`, `.nav-link.active` - Active/hover states
- `.dropdown-menu` - Dropdown menu container
- `.dropdown-item` - Individual dropdown items

**Section 5: Button Styles**
- `.btn-premium` - Primary CTA button (blue gradient)
- `.btn-premium:hover` - Hover effect (lift + shadow)
- `.btn-ghost` - Secondary button (glass morphism)
- `.btn-ghost:hover` - Hover effect (increased transparency)

**Section 6: Hero Sections - Page Headers**
- `.page-hero`, `.product-hero` - Hero section containers
- `.page-hero::before`, `.product-hero::before` - Grid pattern background
- `.hero-content` - Content inside hero
- `.breadcrumb-soft` - Breadcrumb navigation

**Section 7: Footer Styling**
- `.footer` - Footer container
- `.footer-logo-wrapper` - Logo and company info wrapper
- `.footer-logo` - Circular footer logo (64px)
- `.footer-logo-text` - Company name and tagline in footer
- `.footer h3` - Section headings
- `.footer a` - Footer links
- `.social-row a` - Social media icons

**Section 8: Animations & Reveal Effects**
- `.reveal` - Fade in and slide up animation
- `.reveal.visible` - Visible state (animation complete)

**Section 9: Mobile Drawer & Menu**
- `.drawer-backdrop` - Semi-transparent overlay
- `.mobile-drawer` - Side menu drawer (slides in from right)
- `body.drawer-open` - Active states for backdrop and drawer
- `.drawer-link` - Individual drawer menu links

**Section 10: Responsive Design - Mobile & Tablet**
- `@media (max-width: 991.98px)` - Tablet and smaller devices
- Hide desktop nav, show mobile menu button
- Responsive grid adjustments
- Tagline hide on mobile

**Section 11: Responsive Design - Small Mobile**
- `@media (max-width: 575.98px)` - Extra small devices
- Full-width buttons
- Adjusted padding and spacing

---

### 3. **assets/abg.js** - JavaScript Functionality
Handles interactive features and animations.

#### Functions:

##### Utility Functions
- `qs(selector, scope)` - Query selector shorthand
- `qsa(selector, scope)` - Query selector all (returns array)

##### `animateCounter(el)`
- **Purpose**: Animate numbers from 0 to target value
- **Used for**: Statistics, metrics, counter displays
- **Data Attributes**: `data-counter`, `data-suffix`
- **Duration**: 1500ms
- **Easing**: Cubic ease-out animation

##### `initReveal()`
- **Purpose**: Intersection Observer for scroll reveal animations
- **Features**:
  - Triggers animations when elements enter viewport
  - Animates counters when visible
  - Animates bar widths and heights
  - Observes: `.reveal`, `.hero-mock`, `.mockup-card`, `.stats-inner`
- **Threshold**: 0.15 (15% of element visible)

##### `initNavigation()`
- **Purpose**: Initialize all navigation functionality
- **Features**:
  - Navbar scroll effect (adds `is-scrolled` class)
  - Mobile drawer open/close
  - Drawer backdrop click to close
  - Menu link clicks to close drawer
- **Elements**:
  - `#siteNav` - Navbar
  - `#openDrawer` - Hamburger button
  - `#closeDrawer` - Close button in drawer
  - `#drawerBackdrop` - Overlay
  - `.drawer-link` - Menu links

---

### 4. **index.php** - Homepage
Main landing page with all sections.

#### Page Structure:
1. **HTML Head** - Metadata, stylesheets, scripts
2. **Navigation** - Fixed navbar with mobile drawer
3. **Hero Section** - Large banner with headline
4. **Services Section** - Showcase main services
5. **Products Section** - Highlight key products
6. **Features Section** - Highlight company features
7. **Call to Action Section** - Get Quote button
8. **Footer** - Company info and links
9. **Scripts** - Bootstrap and custom JavaScript

#### CSS Sections in index.php:
- Variables and global styles (same as abg.css)
- Navbar styling with comments
- Button styling with comments
- Footer styling with comments
- And more...

---

## 🎨 Color Scheme

| Variable | Hex Value | Usage |
|----------|-----------|-------|
| `--ink` | `#07152f` | Dark text |
| `--navy` | `#0d2b67` | Dark backgrounds |
| `--royal` | `#1f55c7` | Primary brand blue |
| `--blue` | `#3d86e8` | Light accents |
| `--cyan` | `#59d7f1` | Highlights and hovers |
| `--deep-teal` | `#17384c` | Footer background |
| `--green` | `#20c997` | Success state |
| `--muted` | `#6d7d99` | Muted text |

---

## 🎯 Key Components

### Logo
- **File**: `assets/logo.png`
- **Size**: Circular, 72px in navbar, 64px in footer, 60px mobile
- **Style**: `border-radius: 50%;`, `object-fit: contain;`
- **Shadow**: `0 6px 20px rgba(89,215,241,.32)`
- **Display**: Navbar, mobile drawer, footer

### Navigation
- **Type**: Fixed top navbar
- **Mobile**: Hamburger menu → Side drawer
- **Dropdown**: Services and Products have submenus
- **Active State**: Highlighted with cyan color
- **Scroll Effect**: Background fills when scrolling

### Buttons
- **Primary**: Blue gradient, shadow, lift on hover
- **Secondary**: Glass morphism style with blur
- **Size**: Fixed padding and border-radius (18px)

### Footer
- **Layout**: 4-column grid (responsive)
- **Logo**: 64px circular in footer
- **Social**: 5 platform icons (circular buttons)
- **Links**: Quick links and services

---

## 🚀 JavaScript Features

### Auto-initialized Features
All features initialize automatically when the page loads:
- Navbar scroll effect
- Mobile drawer functionality
- Reveal animations on scroll
- Counter animations

### Data Attributes (HTML)
- `data-counter="1000"` - Number to animate to
- `data-suffix="+"` - Suffix to add after number
- `data-width="80%"` - Width animation for bars
- `data-height="150px"` - Height animation for bars

---

## 📱 Responsive Breakpoints

- **Desktop**: > 992px (all features)
- **Tablet**: ≤ 992px (mobile drawer, simplified layout)
- **Mobile**: ≤ 576px (full-width buttons, larger spacing)

---

## ✨ Special Features

1. **Glass Morphism**: Frosted glass effect with blur
2. **Smooth Scroll**: HTML `scroll-behavior: smooth;`
3. **Intersection Observer**: Efficient scroll animations
4. **Mobile First**: Progressive enhancement for mobile
5. **Accessibility**: ARIA labels, semantic HTML, keyboard navigation

---

## 🔧 Development Notes

### Adding New Pages
1. Create new `.php` file
2. At top: `<?php require __DIR__ . '/includes/site.php'; abg_head('Title', 'Description'); abg_nav('page-id'); ?>`
3. Add content
4. At bottom: `<?php abg_footer(); ?>`
5. Include Bootstrap scripts

### Modifying Styles
1. Edit `assets/abg.css` (used by all pages through site.php)
2. OR edit inline styles in `index.php` for homepage-specific styles
3. Clear browser cache to see changes

### Adding Features
1. Add HTML markup in `.php` files
2. Add CSS to `assets/abg.css` with section comments
3. Add JavaScript to `assets/abg.js` with function comments
4. Initialize with `initFeatureName()` call

---

## 📞 Contact Information

- **Email**: info@abgtech.co
- **Phone**: +256 700 000 000
- **Location**: Kampala, Uganda
- **Website**: abgtech.co
- **Social**: Facebook, LinkedIn, Instagram, WhatsApp, X

---

**Last Updated**: May 17, 2026
**Version**: 1.0
**Maintainer**: ABGTECH Development Team
