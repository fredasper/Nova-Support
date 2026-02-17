/* ===== AUTHENTICATION ===== */

const API_BASE = 'http://localhost:8080/appdib/backend/api';

function isAdminLoggedIn() {
    return localStorage.getItem('isAdmin') === 'true';
}

/**
 * Checks if user is logged in on page load
 * Redirects to dashboard if already authenticated
 */
function checkIfLoggedIn() {
    if (isAdminLoggedIn()) {
        window.location.href = 'admin-dashboard.html';
        return;
    }

    const studentName = localStorage.getItem('studentName');
    if (studentName) {
        window.location.href = 'dashboard.html';
    }
}

async function postJson(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    let data = {};
    try {
        data = await response.json();
    } catch (error) {
        data = {};
    }

    return { response, data };
}

function setStudentSession(user) {
    localStorage.setItem('studentName', user.full_name || 'User');
    localStorage.setItem('studentEmail', user.email || '');
    localStorage.setItem('studentId', user.student_id || '');
    localStorage.setItem('studentProgram', user.program || '');
    localStorage.setItem('studentYear', user.year_level || '');
    localStorage.setItem('studentSubjects', user.subjects || '');
    localStorage.setItem('isLoggedIn', 'true');

    localStorage.removeItem('isAdmin');
    localStorage.removeItem('adminName');
    localStorage.removeItem('adminEmail');
}

function setAdminSession(admin) {
    localStorage.setItem('isAdmin', 'true');
    localStorage.setItem('adminName', admin.full_name || 'Admin');
    localStorage.setItem('adminEmail', admin.email || '');

    localStorage.removeItem('studentName');
    localStorage.removeItem('studentEmail');
    localStorage.removeItem('studentId');
    localStorage.removeItem('studentProgram');
    localStorage.removeItem('studentYear');
    localStorage.removeItem('studentSubjects');
    localStorage.removeItem('isLoggedIn');
}

/**
 * Handles login form submission
 * @param {Event} event - Form submit event
 */
async function handleLogin(event) {
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    if (!email || !password) {
        alert('Please enter email and password.');
        return;
    }

    try {
        const studentResult = await postJson(`${API_BASE}/auth/login.php`, { email, password });

        if (studentResult.response.ok && studentResult.data.user) {
            setStudentSession(studentResult.data.user);
            showGreeting(studentResult.data.user.full_name || 'User', 'dashboard.html');
            return;
        }

        const adminResult = await postJson(`${API_BASE}/auth/admin-login.php`, { email, password });

        if (adminResult.response.ok && adminResult.data.admin) {
            setAdminSession(adminResult.data.admin);
            showGreeting(adminResult.data.admin.full_name || 'Admin', 'admin-dashboard.html', true);
            return;
        }

        alert(adminResult.data.error || studentResult.data.error || 'Login failed');
    } catch (error) {
        console.error(error);
        alert('Server error. Please try again.');
    }
}

/**
 * Shows greeting popup and redirects to target page
 * @param {string} name
 * @param {string} redirectPage
 * @param {boolean} adminMode
 */
function showGreeting(name, redirectPage, adminMode = false) {
    const greetingPopup = document.getElementById('greetingPopup');
    const greetingText = document.getElementById('greetingText');
    const greetingDescription = document.getElementById('greetingDescription');

    if (greetingPopup && greetingText && greetingDescription) {
        greetingText.textContent = adminMode ? `Welcome Admin ${name}!` : `Welcome ${name}!`;
        greetingDescription.textContent = adminMode
            ? 'Loading your admin dashboard with chatbot and survey insights.'
            : "I'm Nova, your AI student support assistant. Ask me anything about enrollment, schedules, and more!";
        greetingPopup.classList.remove('hidden');
    }

    setTimeout(() => {
        window.location.href = redirectPage;
    }, 1200);
}

/**
 * Handles logout action - clears data and redirects to login
 */
function handleLogout() {
    localStorage.removeItem('studentName');
    localStorage.removeItem('studentEmail');
    localStorage.removeItem('studentId');
    localStorage.removeItem('studentProgram');
    localStorage.removeItem('studentYear');
    localStorage.removeItem('studentSubjects');
    localStorage.removeItem('isLoggedIn');

    localStorage.removeItem('isAdmin');
    localStorage.removeItem('adminName');
    localStorage.removeItem('adminEmail');

    window.location.href = 'login.html';
}

/**
 * Loads student name from localStorage on dashboard page
 */
function loadStudentName() {
    const studentName = localStorage.getItem('studentName');
    if (studentName) {
        const dashboardUserName = document.getElementById('dashboardUserName');
        if (dashboardUserName) {
            dashboardUserName.textContent = studentName;
        }
    }
}

function loadAdminName() {
    const adminName = localStorage.getItem('adminName') || 'Admin';
    const adminNameElement = document.getElementById('adminName');

    if (adminNameElement) {
        adminNameElement.textContent = adminName;
    }
}

/**
 * Checks if student is authenticated
 */
function checkAuthentication() {
    if (isAdminLoggedIn()) {
        window.location.href = 'admin-dashboard.html';
        return;
    }

    const studentName = localStorage.getItem('studentName');
    if (!studentName) {
        window.location.href = 'login.html';
    }
}

function checkAdminAuthentication() {
    if (!isAdminLoggedIn()) {
        window.location.href = 'login.html';
    }
}

/**
 * Initializes student dashboard on page load
 */
function initializeDashboard() {
    checkAuthentication();
    loadStudentName();
    if (typeof initializeChat === 'function') {
        initializeChat();
    }
    if (typeof loadFaqs === 'function') {
        loadFaqs();
    }
}


