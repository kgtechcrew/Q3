
/* This function is used for creating the global URL in php where the function is used in JS **/
function setGlobalUrl(val)
{
    return "?r=" + val;
}

function loginRequest()
{
    var res = '';
    $.ajax({
        type: "POST",
        data: $('#login_form').serialize(),
        async: false,
        dataType: "json",
        url: setGlobalUrl('user/login'),
        success: function(result)
        {
            if (result['status'] === 'success')
            {
                window.location.href = setGlobalUrl('user/dashabord');
            }
            else
            {
                $('#login_error_message').html(result['message']);
                $('#exampleModalCenter').modal({
                    show: true,
                    backdrop: 'static',
                    keyboard: false
                });
            }
        }
    });
}