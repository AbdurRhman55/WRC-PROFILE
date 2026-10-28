# Welfare Relief Committee (WRC) NGO Website

Welcome to the official website template for **Welfare Relief Committee (WRC)**. Built with **HTML5**, **Tailwind CSS**, and **Vanilla JavaScript**.

---

## 📁 Clean File Structure

```text
WRC WEBSITE/
├── index.html              # Homepage (Hero, Stats, Featured Causes, Pillars, Footer)
├── about.html              # About WRC (Vision, Mission, Core Values, Team)
├── causes.html             # Relief Projects (Water Aid, Food Packs, Orphan Care)
├── contribute-with-us.html   # Collaboration & Contribution Portal (Partnership Inquiry Form)
├── contact.html            # Contact Form & Volunteer Portal + FAQ Accordion
├── README.md               # Project documentation & editing guide
└── assets/
    ├── css/
    │   └── custom.css      # Custom animations, fonts, and scrollbar styling
    ├── js/
    │   └── main.js         # Mobile menu toggle, stats counters, active links
    └── images/
        ├── hero.jpg        # Hero section background image
        ├── water.jpg       # Clean water project cause photo
        └── food.jpg        # Emergency food ration distribution photo
```

---

## 🚀 How to Run the Project

1. **No Build Step Required!**
   Simply open `index.html` in any web browser (Chrome, Edge, Firefox, Safari).
2. **VS Code Live Server (Optional):**
   Install the "Live Server" extension in VS Code and click **"Go Live"** at the bottom right.

---

## 🎨 Customization Guide

### 1. Changing Brand Colors & Fonts
- Tailored custom colors and Google Fonts (`Inter` & `Outfit`) are configured inside the Tailwind config script tag in each HTML file:
```js
tailwind.config = {
  theme: {
    extend: {
      colors: {
        brand: { 900: '#0f172a' },
        relief: { 500: '#10b981' }
      }
    }
  }
}
```

### 2. Updating Partnership Details
- Open `contribute-with-us.html` and edit the collaboration inquiry form fields and contribution opportunity cards as needed.

### 3. Adding New Images
- Save your new images in the `assets/images/` folder and reference them in your HTML image tags (`<img src="assets/images/your-image.jpg">`).

---

## 🛠 Tech Stack Used
- **HTML5**: Semantic, accessible markup.
- **Tailwind CSS**: Utility-first CSS framework via CDN.
- **FontAwesome 6**: Modern SVG/Icon font icons.
- **Vanilla JavaScript**: Lightweight menu toggle and counter animations.
