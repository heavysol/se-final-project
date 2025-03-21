/* This script handles (for now) chart management in the organiser dashboard */

// Bootstrap Icons
document.addEventListener('DOMContentLoaded', function() {
    const iconLink = document.createElement('link');
    iconLink.rel = 'stylesheet';
    iconLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css';
    document.head.appendChild(iconLink);
    
    // Event Analytics Chart
    const eventCtx = document.getElementById('eventAnalyticsChart').getContext('2d');
    const eventChart = new Chart(eventCtx, {
        type: 'line',
        data: {
            labels: ['1 Mar', '5 Mar', '10 Mar', '15 Mar', '20 Mar', '25 Mar', '30 Mar'],
            datasets: [{
                label: 'Registrations',
                data: [12, 19, 28, 35, 42, 58, 65],
                borderColor: '#0B5394',
                backgroundColor: 'rgba(11, 83, 148, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Categories Chart
    const catCtx = document.getElementById('categoriesChart').getContext('2d');
    const catChart = new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: ['Cultural', 'Academic', 'Professional', 'Entertainment', 'Community Service'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    '#0B5394', 
                    '#28a745', 
                    '#ffc107', 
                    '#dc3545',
                    '#6c757d'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});