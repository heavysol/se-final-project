/* This script handles behaviour of login page */

// DOM Elements
const loginForm = document.getElementById('loginForm');
const togglePasswordBtns = document.querySelectorAll('.toggle-password');
const oauthButtons = {
    google: document.getElementById('googleBtn'),
    sso: document.getElementById('ssoBtn'),
    github: document.getElementById('githubBtn')
};

// Password Toggle Functionality
togglePasswordBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const passwordField = document.getElementById(targetId);
        
        // Toggle password visibility
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
    });
});

// OAuth Sign-in Handlers
oauthButtons.google.addEventListener('click', function() {
    console.log("Google sign-in initiated");
    // In production: window.location.href = '/auth/google';
    alert("Redirecting to Google authentication...");
});

oauthButtons.sso.addEventListener('click', function() {
    console.log("SSO sign-in initiated");
    // In production: window.location.href = '/auth/sso';
    alert("Redirecting to SSO authentication...");
});

oauthButtons.github.addEventListener('click', function() {
    console.log("GitHub sign-in initiated");
    // In production: window.location.href = '/auth/github';
    alert("Redirecting to GitHub authentication...");
});

// Form Submission
loginForm.addEventListener('submit', function(event) {
    event.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    const loginData = {};
    
    for (const [key, value] of formData.entries()) {
        loginData[key] = value;
    }
    
    console.log("Login attempt:", loginData);
    alert("Login form submitted!");
    
    // In production: Submit the form data to server
    // fetch('/api/login', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify(loginData)
    // }).then(response => response.json())
    //   .then(data => {
    //     if (data.success) {
    //       window.location.href = '/dashboard';
    //     }
    //   });
});