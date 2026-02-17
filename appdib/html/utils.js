const UTIL_API_BASE_URL = window.NOVA_API_BASE || (window.location.hostname === 'localhost' ? '/appdib/backend/api' : '/api');

/* ===== UTILITY FUNCTIONS & HELPERS ===== */

/**
 * Support data for footer links
 */
const supportData = {
    'css': {
        title: 'CSS Support',
        email: 'css.support@novasupport.edu',
        message: 'Customer Support Services for general student concerns.'
    },
    'kins': {
        title: 'KINS Support',
        email: 'kins@novasupport.edu',
        message: 'Health and wellness support, counseling, and clinic concerns.'
    },
    'finance': {
        title: 'Finance Office',
        email: 'finance@novasupport.edu',
        message: 'Tuition payments, billing concerns, and financial assistance.'
    },
    'registrar': {
        title: 'Registrar',
        email: 'registrar@novasupport.edu',
        message: 'Enrollment, subject registration, transcripts, and records requests.'
    },
    'supplies': {
        title: 'Supplies Office',
        email: 'supplies@novasupport.edu',
        message: 'School supplies, uniforms, and learning materials assistance.'
    },
    'clinic': {
        title: 'Clinic',
        email: 'clinic@novasupport.edu',
        message: 'Medical concerns, first aid, and student wellness support.'
    }
};

/**
 * Handles footer link clicks
 * @param {string} linkTarget - Target link identifier
 */
function handleFooterLink(linkTarget) {
    const data = supportData[linkTarget];

    if (data) {
        showSupportEmailPopup(data, linkTarget);
    }
}

/**
 * Opens a popup for sending an email to a support office
 * @param {{title: string, email: string, message: string}} data - Support office data
 * @param {string} officeKey - Support office key
 */
function showSupportEmailPopup(data, officeKey) {
    const existingModal = document.querySelector('.support-modal-overlay');
    if (existingModal) {
        existingModal.remove();
    }

    const modal = document.createElement('div');
    modal.className = 'support-modal-overlay';
    modal.innerHTML = `
        <div class="support-modal" role="dialog" aria-modal="true" aria-label="Send Email">
            <h3>Contact ${data.title}</h3>
            <p class="support-modal-subtitle">${data.message}</p>
            <form id="supportEmailForm" class="support-email-form">
                <label>
                    To
                    <input type="email" value="${data.email}" readonly>
                </label>
                <label>
                    Subject
                    <input type="text" id="supportSubject" value="Support Request - ${data.title}" required>
                </label>
                <label>
                    Message
                    <textarea id="supportBody" rows="5" placeholder="Type your message here..." required></textarea>
                </label>
                <div class="support-modal-actions">
                    <button type="button" class="support-cancel-btn">Cancel</button>
                    <button type="submit" class="support-send-btn">Send Email</button>
                </div>
            </form>
        </div>
    `;

    document.body.appendChild(modal);

    const onEscapeKey = (e) => {
        if (e.key === 'Escape') {
            closeModal();
        }
    };

    const closeModal = () => {
        document.removeEventListener('keydown', onEscapeKey);
        modal.remove();
    };

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    const cancelButton = modal.querySelector('.support-cancel-btn');
    cancelButton.addEventListener('click', closeModal);

    const form = modal.querySelector('#supportEmailForm');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const subject = modal.querySelector('#supportSubject').value.trim();
        const body = modal.querySelector('#supportBody').value.trim();
        const studentEmail = localStorage.getItem('studentEmail');

        if (!subject || !body || !studentEmail) {
            if (!studentEmail) {
                alert('Please log in before sending a support message.');
            }
            return;
        }

        try {
            const response = await fetch(UTIL_API_BASE_URL + '/support/send.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: studentEmail,
                    office_key: officeKey,
                    subject: subject,
                    message: body
                })
            });

            const result = await response.json();
            if (!response.ok) {
                alert(result.error || 'Failed to save support message.');
                return;
            }

            const mailtoUrl = `mailto:${encodeURIComponent(data.email)}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.location.href = mailtoUrl;
            closeModal();
        } catch (error) {
            console.error(error);
            alert('Server error while sending support message.');
        }
    });

    document.addEventListener('keydown', onEscapeKey);
}






