//Show/Hide Password
const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');

const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
const confirmPassword = document.getElementById('confirmPassword');

const showPasswordIcon = '/project-sea/images/eyes.png';
const hidePasswordIcon = '/project-sea/images/eyes-closed.png';

togglePassword.addEventListener('click', function () {                                          //Untuk toggle ikon password
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);

    this.src = type === 'password' ? hidePasswordIcon : showPasswordIcon;
});

toggleConfirmPassword.addEventListener('click', function () {                                   //Untuk toggle ikon confirm password
    const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';     
    confirmPassword.setAttribute('type', type);
    
    this.src = type === 'password' ? hidePasswordIcon : showPasswordIcon;
});

//Fungsi form SIGN UP
function validateForm() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    const usernameMinLength = 5     //MINIMAL USERNAME LENGTH
    const usernameMaxLength = 25    //MAKSIMAL USERNAME LENGTH

    //Ukur panjang username
    if (username.length < usernameMinLength || username.length > usernameMaxLength) {
        alert(`Panjang username harus ${usernameMinLength} sampai ${usernameMaxLength} karakter.`);
        return false;
    }

    //Mencocokkan password dan confirm password
    if (password != confirmPassword){
        alert('Password tidak cocok!');
        return false;
    } 

    return true;
}