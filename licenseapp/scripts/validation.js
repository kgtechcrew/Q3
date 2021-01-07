/**
 * Login basic validation
 * @returns {undefined}
 */
function loginValdation()
{
    clearErrorMessage();
    var username = $('#username').val();
    var pass = $('#password').val();
    var error = errorMessage();
    if (username === "" && pass === "")
    {
        $('#username_error').html(error.username);
        $('#password_error').html(error.password);
    }
    else if (username === "")
    {
        $('#username_error').html(error.username);
    }
    else if (pass === "")
    {
        $('#password_error').html(error.password);
    }
    else
    {
        loginRequest();
    }
}

/**
 * Login Error message
 * @returns {errorMessage.msg}
 */
function errorMessage()
{
    var msg = {
        username: 'Please enter the username',
        password: 'Please enter the password'
    };
    return msg;
}

/**
 * Clear Error Message
 * @returns {undefined}
 */
function clearErrorMessage()
{
    $('#username_error').html('');
    $('#password_error').html('');
}

/** Back button restructions **/
history.pushState(null, null, null);
window.addEventListener('popstate', function() {
    history.pushState(null, null, null);
});