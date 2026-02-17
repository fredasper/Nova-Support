# Nova - Student Support Assistant

## Project Structure

The project has been refactored into a fully modular, organized architecture with separate folders for HTML, CSS, JavaScript, and images.

```
appdib/
├── index.html                 # Entry point (redirects to html/login.html)
│
├── html/
│   ├── login.html             # Login page with greeting popup
│   └── dashboard.html         # Dashboard with all student features
│
├── css/
│   ├── common.css             # Base styles, resets, CSS variables
│   ├── animations.css         # All keyframe animations
│   ├── login.css              # Login page & greeting popup styles
│   └── dashboard.css          # Dashboard, chat, FAQ, footer styles
│
├── js/
│   ├── auth.js                # Login/logout + page navigation
│   ├── ui.js                  # Navigation, UI interactions
│   ├── chat.js                # Chatbot message handling
│   ├── utils.js               # Helper functions, support data
│   └── main.js                # Initialization & event listeners
│
├── images/
│   └── campus-bg.jpg          # Background image for login & dashboard
│
├── boo.css                    # (deprecated - use css/ folder)
├── boo.js                     # (deprecated - use js/ folder)
├── campus-bg.jpg              # (deprecated - use images/campus-bg.jpg)
│
└── README.md                  # This file
```

## HTML Files

### index.html (Root)
- **Purpose**: Entry point for the application
- **Function**: Automatically redirects to `html/login.html` for backward compatibility

### html/login.html
- **Purpose**: Login page and greeting popup
- **Features**:
  - Email/password login form
  - Animated greeting popup after successful login
  - Beautiful illustrated right-side panel
  - CSS: common, animations, login
- **Path updates**: Includes `../css/` and `../js/` paths
  
### html/dashboard.html
- **Purpose**: Main student dashboard with all features
- **Features**:
  - Dashboard header with user name and navigation links
  - Nova intro section with feature navigation
  - Announcements section
  - AI chatbot (Ask Nova)
  - FAQ section
  - Footer with support links
  - CSS: common, animations, dashboard
- **Path updates**: Includes `../css/` and `../js/` paths

## Module Descriptions

### CSS Modules (css/)

**common.css**
- CSS variables for colors and shadows
- Reset and base styles
- Shared form styles
- Utility classes (`.hidden`)
- Uses `../images/campus-bg.jpg` for background references

**animations.css**
- All `@keyframes` animations
- Includes: twinkle, float, robot-play, bounce, slideUp, slideIn, etc.

**login.css**
- Login page layout and styling
- Greeting popup styles
- Illustration elements (clouds, stars, robot, terrain)
- Background image path: `../images/campus-bg.jpg`

**dashboard.css**
- Dashboard header and content
- Navigation icons
- Announcements, chatbot, FAQ sections
- Footer styles
- Responsive media queries
- Background image path: `../images/campus-bg.jpg`

### JavaScript Modules (js/)

**auth.js** - Authentication & Navigation
- `handleLogin(event)` - Process login, store name, show greeting
- `showGreeting(name)` - Display greeting popup then redirect to dashboard.html
- `handleLogout()` - Clear data and redirect to login.html
- `loadStudentName()` - Retrieve name from localStorage on dashboard
- `initializeDashboard()` - Initialize dashboard on page load

**ui.js** - UI Interactions
- `scrollToSection(sectionId, event)` - Smooth scroll with nav updates
- `toggleFAQ(element)` - Open/close FAQ items
- `handleAnnouncementClick(id, event)` - Handle announcement interactions
- `scrollToChatbot()` - Helper to jump to chat

**chat.js** - Chatbot Operations
- `initializeChat()` - Start chatbot with welcome message
- `sendMessage()` - Send user message and get bot response
- `handleChatKeyPress(event)` - Handle Enter key
- `addMessage(text, sender)` - Add message to chat window
- `getBotResponse(userMessage)` - Generate bot responses

**utils.js** - Utilities
- `supportData` - Object with support contact information
- `handleFooterLink(target)` - Process footer link clicks

**main.js** - App Initialization
- DOMContentLoaded event listener
- Setup footer link event listeners
- Initialize dashboard when on dashboard page

### Images Folder (images/)

- **campus-bg.jpg** - Background image used in login and dashboard pages
- Referenced from CSS via `../images/campus-bg.jpg`

## Data Flow

1. **User opens app** → goes to `index.html`
2. **index.html redirects** → opens `html/login.html`
3. **User logs in** → student name stored in `localStorage`
4. **Greeting popup shows** → after 3 seconds redirects to `html/dashboard.html`
5. **Dashboard loads** → calls `initializeDashboard()` which:
   - Loads student name from `localStorage`
   - Initializes chat
   - Sets up footer links
6. **User logs out** → clears `localStorage` and redirects to `html/login.html`

## Path References

Since HTML files are in `html/` folder, they use relative paths:
- **CSS**: `../css/common.css`, `../css/animations.css`, etc.
- **JS**: `../js/auth.js`, `../js/ui.js`, etc.
- **Images**: `../images/campus-bg.jpg` (in CSS files)

## Benefits of This Architecture

✅ **Separation of Concerns**: Login and Dashboard are separate pages  
✅ **Better Organization**: Dedicated folders for HTML, CSS, JS, and images  
✅ **Modular CSS**: Styles split by feature/page  
✅ **Organized JS**: Functions grouped by responsibility  
✅ **Asset Management**: Images folder for easy asset handling  
✅ **Scalability**: Easy to add new pages or features  
✅ **Team Development**: Multiple developers can work independently  
✅ **Performance**: Only load necessary styles and scripts per page  
✅ **Data Persistence**: localStorage maintains session across pages

## How to Use

### Starting the Application
1. Open `index.html` in a browser
2. Automatically redirected to `html/login.html`
3. Enter any email and password
4. View the greeting popup
5. Automatic redirect to `html/dashboard.html`

### Storage System
- **Student Name**: Stored in `localStorage` as `studentName`
- **Persistence**: Survives page refresh (until logout)
- **Security Note**: localStorage is not secure - use proper backend authentication in production

## Cleanup (Optional)

You can delete these deprecated files:
- Root level `dashboard.html` (use `html/dashboard.html` instead)
- Root level `login.html` (use `html/login.html` instead)
- `boo.css` (replaced by `css/` folder)
- `boo.js` (replaced by `js/` folder)
- Root level `campus-bg.jpg` (move to `images/` folder or delete)

## File Management

### Keep:
- All files in `html/`, `css/`, `js/`, and `images/` folders
- `index.html` (root entry point)
- `README.md`

### Can Delete:
- Old `dashboard.html` in root (if exists)
- Old `login.html` in root (if exists)
- `boo.css` and `boo.js` (deprecated)
- Old `campus-bg.jpg` in root (after moving to images/)

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Requires JavaScript enabled
- CSS Grid and Flexbox support

## Production Considerations

- Replace localStorage with secure authentication (JWT, sessions)
- Move sensitive data to backend
- Add form validation and error handling
- Implement proper password security
- Add API integration for real data
- Consider bundling with webpack or Vite
- Add HTTPS for secure communication

## Quick Start

1. Open `index.html` in browser
2. Login with any email/password
3. View 3-second greeting animation
4. Auto-redirect to dashboard
5. Click logout to return to login
