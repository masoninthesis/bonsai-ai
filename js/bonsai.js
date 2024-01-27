document.addEventListener("DOMContentLoaded", function() {
  var form = document.querySelector('#gform_0');

  if (form) {
    var input1 = form.querySelector('#input_1');
    if (input1) input1.placeholder = 'Email or username';

    var input2 = form.querySelector('#input_2');
    if (input2) input2.placeholder = 'Password';

    var loginButton = form.querySelector('#gform_submit_button_0');
    if (loginButton) loginButton.value = 'Sign In';
  }
});
