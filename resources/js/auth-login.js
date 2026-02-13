// Random background selector for login page
document.addEventListener('DOMContentLoaded', function() {
    const loginBackgrounds = [
        '/template/assets/images/auth/LoginBackground1.png',
        '/template/assets/images/auth/LoginBackground2.png'
    ];
    
    // Select random background on page load
    const randomBg = loginBackgrounds[Math.floor(Math.random() * loginBackgrounds.length)];
    const bgElement = document.getElementById('authBackground');
    
    if (bgElement) {
        bgElement.style.backgroundImage = `url('${randomBg}')`;
    }
});
