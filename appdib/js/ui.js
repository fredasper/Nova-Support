/* ===== UI INTERACTIONS ===== */

/**
 * Scrolls to section with smooth animation and updates nav state
 * @param {string} sectionId - ID of section to scroll to
 * @param {Event} event - Click event
 */
function scrollToSection(sectionId, event) {
    event.preventDefault();
    
    // Remove active class from all nav icons
    document.querySelectorAll('.nav-icon').forEach(icon => {
        icon.classList.remove('active');
    });
    
    // Add active class to clicked icon
    event.target.closest('.nav-icon').classList.add('active');
    
    // Scroll to section
    const section = document.getElementById(sectionId);
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Toggles FAQ item active state
 * @param {Element} element - FAQ question element
 */
function toggleFAQ(element) {
    const faqItem = element.parentElement;
    faqItem.classList.toggle('active');
}

/**
 * Handles announcement item click
 * @param {number} announcementId - ID of announcement
 * @param {Event} event - Click event
 */
function handleAnnouncementClick(announcementId, event) {
    event.stopPropagation();
    // Optional: Add specific action for announcement click
    // For now, it just highlights the clicked announcement
    const items = document.querySelectorAll('.announcement-item');
    items.forEach(item => item.style.opacity = '0.6');
    event.target.closest('.announcement-item').style.opacity = '1';
    
    // Reset after 2 seconds
    setTimeout(() => {
        items.forEach(item => item.style.opacity = '1');
    }, 2000);
}

/**
 * Helper function to scroll to chatbot section
 */
function scrollToChatbot() {
    const chatbotSection = document.getElementById('chatbot');
    if (chatbotSection) {
        // Update nav icon active state
        document.querySelectorAll('.nav-icon').forEach(icon => {
            icon.classList.remove('active');
        });
        document.querySelectorAll('.nav-icon')[1].classList.add('active');
        
        chatbotSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
