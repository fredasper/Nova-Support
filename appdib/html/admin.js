const ADMIN_BASE_URL = window.NOVA_API_BASE || (window.location.hostname === 'localhost' ? '/appdib/backend/api' : '/api');
const ADMIN_API_BASE = `${ADMIN_BASE_URL}/admin`;

function formatDateTime(dateString) {
    if (!dateString) {
        return 'No date';
    }

    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) {
        return dateString;
    }

    return date.toLocaleString();
}

function escapeHtml(text) {
    return String(text || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

async function adminFetch(path, options = {}) {
    const response = await fetch(`${ADMIN_API_BASE}/${path}`, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            ...(options.headers || {})
        }
    });

    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.error || 'Request failed');
    }

    return data;
}

async function loadRecentQuestions() {
    const container = document.getElementById('recentQuestionsList');
    const topQuestion = document.getElementById('topQuestionText');

    try {
        const data = await adminFetch('recent-questions.php');
        const items = data.recent_questions || [];

        if (data.most_asked && data.most_asked.question) {
            topQuestion.textContent = `Top question: ${data.most_asked.question} (${data.most_asked.ask_count} asks)`;
        } else {
            topQuestion.textContent = 'Top question: none yet';
        }

        if (items.length === 0) {
            container.innerHTML = '<div class="list-item">No chatbot questions yet.</div>';
            return;
        }

        container.innerHTML = items.map((item) => `
            <div class="list-item">
                <div class="list-item-title">${escapeHtml(item.user_message)}</div>
                <div class="list-item-meta">${formatDateTime(item.created_at)}</div>
            </div>
        `).join('');
    } catch (error) {
        container.innerHTML = `<div class="list-item">Failed to load questions: ${escapeHtml(error.message)}</div>`;
        topQuestion.textContent = 'Top question: unavailable';
    }
}

async function exportQuestionsToFaq() {
    const button = document.getElementById('exportFaqBtn');
    const originalText = button.textContent;

    button.disabled = true;
    button.textContent = 'Exporting...';

    try {
        const data = await adminFetch('export-faq.php', { method: 'POST', body: '{}' });
        alert(`${data.message}. Exported: ${data.exported}`);
        await loadRecentQuestions();
    } catch (error) {
        alert(`Export failed: ${error.message}`);
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

async function loadSurveyAnalytics() {
    const summaryContainer = document.getElementById('surveySummary');
    const distContainer = document.getElementById('satisfactionDistribution');

    try {
        const data = await adminFetch('survey-analytics.php');
        const summary = data.summary || {};

        const stats = [
            { label: 'Total Responses', value: summary.total_responses ?? 0 },
            { label: 'Avg Satisfaction', value: summary.avg_satisfaction ?? 0 },
            { label: 'Avg Ease of Use', value: summary.avg_ease_of_use ?? 0 },
            { label: 'Avg Helpfulness', value: summary.avg_helpfulness ?? 0 },
            { label: 'Avg Response Time', value: summary.avg_response_time ?? 0 },
            { label: 'Avg Recommend', value: summary.avg_recommend ?? 0 }
        ];

        summaryContainer.innerHTML = stats.map((stat) => `
            <div class="stat-card">
                <div class="stat-label">${escapeHtml(stat.label)}</div>
                <div class="stat-value">${escapeHtml(stat.value)}</div>
            </div>
        `).join('');

        const dist = data.satisfaction_distribution || [];
        if (dist.length === 0) {
            distContainer.innerHTML = '<div class="list-item">No survey data yet.</div>';
            return;
        }

        distContainer.innerHTML = dist.map((row) => `
            <div class="list-item">
                <div class="list-item-title">Rating ${escapeHtml(row.satisfaction)}</div>
                <div class="list-item-meta">Responses: ${escapeHtml(row.count)}</div>
            </div>
        `).join('');
    } catch (error) {
        summaryContainer.innerHTML = `<div class="list-item">Failed to load analytics: ${escapeHtml(error.message)}</div>`;
        distContainer.innerHTML = '';
    }
}

async function loadRecentSurveys() {
    const container = document.getElementById('recentSurveyList');

    try {
        const data = await adminFetch('recent-surveys.php');
        const items = data.recent_surveys || [];

        if (items.length === 0) {
            container.innerHTML = '<div class="list-item">No recent survey submissions yet.</div>';
            return;
        }

        container.innerHTML = items.map((item) => `
            <div class="list-item">
                <div class="list-item-title">${escapeHtml(item.full_name)} (${escapeHtml(item.email)})</div>
                <div class="list-item-meta">Submitted: ${formatDateTime(item.created_at)}</div>
                <div class="list-item-meta">Satisfaction: ${escapeHtml(item.satisfaction)} | Ease: ${escapeHtml(item.ease_of_use)} | Helpful: ${escapeHtml(item.helpfulness)} | Response Time: ${escapeHtml(item.response_time)} | Recommend: ${escapeHtml(item.recommend_score)}</div>
                <div class="list-item-meta">Feedback: ${escapeHtml(item.feedback || '-')}</div>
            </div>
        `).join('');
    } catch (error) {
        container.innerHTML = `<div class="list-item">Failed to load recent surveys: ${escapeHtml(error.message)}</div>`;
    }
}

async function initializeAdminDashboardPage() {
    checkAdminAuthentication();
    loadAdminName();

    await Promise.all([
        loadRecentQuestions(),
        loadSurveyAnalytics(),
        loadRecentSurveys()
    ]);
}







