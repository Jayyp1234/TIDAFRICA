// Show & Hide Password for Login
const togglePasswordBtn = document.getElementById('eyeicon');
const toggleBtn = document.getElementById('icon');
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm-password');


togglePasswordBtn.addEventListener('click', function() {
    if(passwordInput.type == 'password') {
        passwordInput.type = 'text';
        togglePasswordBtn.innerHTML = '<i class="ri-eye-line fs-5"></i>';
    } else {
        passwordInput.type = 'password';
        togglePasswordBtn.innerHTML = '<i class="ri-eye-off-fill fs-5"></i>';
    }
});

toggleBtn.addEventListener('click', function() {
    if(confirmInput.type == 'password') {
        confirmInput.type = 'text';
        toggleBtn.innerHTML =  '<i class="ri-eye-line fs-5"></i>';   
    } else {
        confirmInput.type = 'password';
        toggleBtn.innerHTML =  '<i class="ri-eye-off-fill fs-5"></i>';   
    }
});
