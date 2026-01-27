// Random background selector for register page
document.addEventListener('DOMContentLoaded', function() {
    const registerBackgrounds = [
        '/template/assets/images/auth/RegisterBackground1.png',
        '/template/assets/images/auth/RegisterBackground2.png'
    ];
    
    // Select random background on page load
    const randomBg = registerBackgrounds[Math.floor(Math.random() * registerBackgrounds.length)];
    const bgElement = document.getElementById('authBackground');
    
    if (bgElement) {
        bgElement.style.backgroundImage = `url('${randomBg}')`;
    }
});
