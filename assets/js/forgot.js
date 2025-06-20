/*===== Confirm Show =====*/
const forgotPassword = document.getElementById('forgot'),
    continueBtn = document.getElementById('continue'),
    confirm = document.getElementById('confirm-container');


continueBtn.addEventListener('click', ()=>{
    // Remove classes first if they exist
    forgotPassword.classList.remove('block');
    confirm.classList.remove('none');

    // Add classes
    confirm.classList.add('block');
    forgotPassword.classList.add('none');
})

