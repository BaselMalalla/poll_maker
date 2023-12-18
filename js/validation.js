document.addEventListener("DOMContentLoaded", () => {
  const usernameInput = document.querySelector('input[name="name"]');
  const emailInput = document.querySelector('input[name="email"]');
  const passwordInput = document.querySelector('input[name="password"]');
  const usernameValidationMessage = document.getElementById("username-validation-message");
  const emailValidationMessage = document.getElementById("email-validation-message");
  const passwordValidationMessage = document.getElementById("password-validation-message");
  const submitButton = document.querySelector('input[name="register-btn"]');
  const usernameRegex = /^[a-zA-Z][a-zA-Z\s]*$/;
  const emailRegex = /^[a-z0-9]+@[a-z]+\.[a-z]{2,3}$/;
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@.=#$!%*_\-?&^])[A-Za-z\d@.=#$!%*_\-?&^]{8,}$/;

  let isUsernameValid = false;
  let isEmailValid = false;
  let isPasswordValid = false;

  usernameInput.addEventListener("input", () => {
      if (!usernameRegex.test(usernameInput.value)) {
          isUsernameValid = false;
          usernameValidationMessage.textContent = "Your username must only contain letters.";
      } else {
          usernameValidationMessage.textContent = "";
          isUsernameValid = true;
      }

      updateSubmitButton();
  });

  emailInput.addEventListener("input", () => {
      if (!emailRegex.test(emailInput.value)) {
          emailValidationMessage.textContent = "Please enter a valid email address.";
          isEmailValid = false;
      } else {
          emailValidationMessage.textContent = "";
          
          // Make AJAX request to check email availability
          const xhr = new XMLHttpRequest();
          xhr.open('POST', '../ajax/check_email_existence.php', true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          xhr.onreadystatechange = function () {
              if (xhr.readyState === 4 && xhr.status === 200) {
                  const response = JSON.parse(xhr.responseText);

                  if (response.taken) {
                    emailValidationMessage.textContent = 'This email is already taken.';
                  }else{
                    isEmailValid = true;
                  }
              }
          };

          // Send data to the server
          xhr.send('email=' + encodeURIComponent(emailInput.value));

          
      }

      updateSubmitButton();
  });

  passwordInput.addEventListener("input", () => {
      if (!passwordRegex.test(passwordInput.value)) {
          passwordValidationMessage.textContent = "Your password must be at least 8 characters including a lowercase and an uppercase letter, a number, and a special character";
          isPasswordValid = false;
      } else {
          passwordValidationMessage.textContent = "";
          isPasswordValid = true;
          console.log("valid pass");
      }

      updateSubmitButton();
  });

  function updateSubmitButton() {
      // Enable submit button only if all fields are valid
      submitButton.disabled = !(isUsernameValid && isEmailValid && isPasswordValid);
  }
});
