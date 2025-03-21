/* This script handles behaviour of signup page */

function togglePassword(id) {
    let input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

// Google Sign In function
function signInWithGoogle() {
    console.log("Sign in with Google initiated");
    // In a real implementation, you would redirect to Google OAuth
    // window.location.href = '/auth/google';
    alert("Redirecting to Google authentication...");
}

// SSO Sign In function
function signInWithSSO() {
    console.log("Sign in with SSO initiated");
    // In a real implementation, you would redirect to SSO provider
    // window.location.href = '/auth/sso';
    alert("Redirecting to SSO authentication...");
}

// GitHub Sign In function
function signInWithGitHub() {
    console.log("Sign in with GitHub initiated");
    // In a real implementation, you would redirect to GitHub OAuth
    // window.location.href = '/auth/github';
    alert("Redirecting to GitHub authentication...");
}

// Form submission
document.getElementById('signupForm').addEventListener('submit', function(event) {
    event.preventDefault();
    console.log("Form submitted");
    alert("Form submitted successfully!");
    // In a real implementation, you would handle form submission here
});