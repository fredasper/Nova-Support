const MAIN_API_BASE_URL = window.NOVA_API_BASE || (window.location.hostname === 'localhost' ? '/appdib/backend/api' : '/api');

/* ===== MAIN INITIALIZATION ===== */

/**
 * Loads latest profile data from backend and updates local storage
 * @param {string} email - Student email
 */
async function refreshProfileFromBackend(email) {
    if (!email) {
        return;
    }

    try {
        const response = await fetch(`${MAIN_API_BASE_URL}/profile/get.php?email=${encodeURIComponent(email)}`);
        const data = await response.json();

        if (!response.ok || !data.user) {
            return;
        }

        localStorage.setItem('studentName', data.user.full_name || '');
        localStorage.setItem('studentEmail', data.user.email || '');
        localStorage.setItem('studentId', data.user.student_id || '');
        localStorage.setItem('studentProgram', data.user.program || '');
        localStorage.setItem('studentYear', data.user.year_level || '');
        localStorage.setItem('studentSubjects', data.user.subjects || '');
    } catch (err) {
        console.error('Profile fetch failed:', err);
    }
}

function escapeHtml(text) {
    return String(text || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getDefaultFaqs() {
    return [
        {
            question: 'How do I enroll in classes?',
            answer: 'To enroll in classes, log in to your student portal, navigate to Course Registration, select your courses, and confirm enrollment.'
        },
        {
            question: 'What is my class schedule?',
            answer: 'You can view your class schedule by logging into your student portal and clicking My Schedule.'
        },
        {
            question: 'How do I contact support?',
            answer: 'You can contact support through the offices page links or by visiting your school offices directly.'
        }
    ];
}

function renderFaqItems(items) {
    const faqContainer = document.getElementById('faqContainer');
    if (!faqContainer) {
        return;
    }

    faqContainer.innerHTML = items.map((item) => `
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFAQ(this)">${escapeHtml(item.question)}</div>
            <div class="faq-answer"><p>${escapeHtml(item.answer)}</p></div>
        </div>
    `).join('');
}

async function loadFaqs() {
    try {
        const response = await fetch(MAIN_API_BASE_URL + '/faq/list.php');
        const data = await response.json();

        if (response.ok && Array.isArray(data.faqs) && data.faqs.length > 0) {
            renderFaqItems(data.faqs);
            return;
        }

        renderFaqItems(getDefaultFaqs());
    } catch (error) {
        console.error('FAQ load failed:', error);
        renderFaqItems(getDefaultFaqs());
    }
}

/**
 * Renders active courses from saved subjects data
 */
function renderActiveCoursesFromSubjects() {
    const list = document.getElementById('activeCoursesList');
    if (!list) {
        return;
    }

    const rawSubjects = localStorage.getItem('studentSubjects') || '';
    const subjects = rawSubjects.split(',').map(s => s.trim()).filter(Boolean);

    if (subjects.length === 0) {
        return;
    }

    const items = subjects.map((subject, index) => {
        const code = 'SUB';
        const number = String(index + 1).padStart(2, '0');

        return `
            <div class="event-item">
                <div class="event-date">
                    <div class="date-month">${code}</div>
                    <div class="date-day">${number}</div>
                </div>
                <div class="event-details">
                    <div class="event-title">${subject}</div>
                    <div class="event-time">Enrolled Course</div>
                </div>
            </div>
        `;
    }).join('');

    list.innerHTML = items;
}

/**
 * Renders pending assignments aligned to saved subjects data
 */
function renderPendingAssignmentsFromSubjects() {
    const list = document.getElementById('pendingAssignmentsList');
    if (!list) {
        return;
    }

    const rawSubjects = localStorage.getItem('studentSubjects') || '';
    const subjects = rawSubjects.split(',').map(s => s.trim()).filter(Boolean);

    if (subjects.length === 0) {
        return;
    }

    const assignmentNames = [
        'Project 1',
        'Problem Set 2',
        'Quiz Review',
        'Lab Report 1',
        'Case Study',
        'Reflection Paper'
    ];
    const dueDates = ['Feb 15', 'Feb 16', 'Feb 18', 'Feb 20', 'Feb 22', 'Feb 24'];

    const items = subjects.map((subject, index) => {
        const subjectCode = subject
            .split(' ')
            .filter(Boolean)
            .map(w => w[0])
            .join('')
            .toUpperCase()
            .slice(0, 4) || `SUB${index + 1}`;

        const assignment = assignmentNames[index % assignmentNames.length];
        const due = dueDates[index % dueDates.length];

        return `
            <div class="grade-item">
                <div class="grade-course">
                    <div class="course-code">${subjectCode}</div>
                    <div class="assignment-name">${assignment} - ${subject}</div>
                </div>
                <div class="grade-badge">
                    <div class="grade-letter">Due:</div>
                    <div class="grade-score">${due}</div>
                </div>
            </div>
        `;
    }).join('');

    list.innerHTML = items;
}

/**
 * Initializes the profile page
 */
async function initializeProfile() {
    const studentName = localStorage.getItem('studentName');
    const studentEmail = localStorage.getItem('studentEmail');

    if (!studentName) {
        window.location.href = 'login.html';
        return;
    }

    await refreshProfileFromBackend(studentEmail);

    const profileUserName = document.getElementById('profileUserName');
    if (profileUserName) {
        profileUserName.textContent = localStorage.getItem('studentName') || studentName;
    }

    const welcomeText = document.getElementById('welcomeText');
    if (welcomeText) {
        welcomeText.textContent = `Welcome back, ${localStorage.getItem('studentName') || studentName}!`;
    }

    const studentId = document.getElementById('studentId');
    if (studentId) {
        studentId.textContent = localStorage.getItem('studentId') || '-';
    }

    const profileEmail = document.getElementById('studentEmail');
    if (profileEmail) {
        profileEmail.textContent = localStorage.getItem('studentEmail') || '-';
    }

    const studentProgram = document.getElementById('studentProgram');
    if (studentProgram) {
        studentProgram.textContent = localStorage.getItem('studentProgram') || '-';
    }

    const studentYear = document.getElementById('studentYear');
    if (studentYear) {
        studentYear.textContent = localStorage.getItem('studentYear') || '-';
    }

    renderActiveCoursesFromSubjects();
    renderPendingAssignmentsFromSubjects();
}

/**
 * Initializes the offices page
 */
function initializeOffices() {
    const studentName = localStorage.getItem('studentName');
    if (!studentName) {
        window.location.href = 'login.html';
        return;
    }

    const officesUserName = document.getElementById('officesUserName');
    if (officesUserName) {
        officesUserName.textContent = studentName;
    }
}

/**
 * Initializes the survey page
 */
function initializeSurvey() {
    const studentName = localStorage.getItem('studentName');
    if (!studentName) {
        window.location.href = 'login.html';
        return;
    }

    const surveyUserName = document.getElementById('surveyUserName');
    if (surveyUserName) {
        surveyUserName.textContent = studentName;
    }
}

/**
 * Initializes footer link event listeners when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    const footerLinks = document.querySelectorAll('.footer-links-list a');
    if (footerLinks.length > 0) {
        footerLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-footer-link');
                handleFooterLink(target);
            });
        });

        if (typeof initializeDashboard === 'function') {
            initializeDashboard();
        }
    }
});






