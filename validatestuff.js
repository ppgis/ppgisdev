function validate(form)
{
    //console.log('here')
    username = form.username.value;
    password = form.password.value;
    password2 = form.retype_password.value;
    failu = validateUsername(username)
    failp = validatePassword(password)

    if (password===password2) fail = ""
    else fail = "Passwords did not match"
    if (username==password) fail += "Password may not be the same as your Username!"
    fail += failu
    fail += failp
    //They should have be checked already by HTML for validity

    if (fail == "") return true
    else {alert(fail);
          fail = "Please try again<br>"+fail;
          document.getElementById('signuperror').innerHTML=fail;
          return false}
}
function validatelogin(form)
{
    //console.log('here')
    username = form.username.value;
    password = form.password.value;
    failu = validateUsername(username)
    failp = validatePassword(password)

    fail = ""
    fail += failu
    fail += failp
    //They should have be checked already by HTML for validity

    if (fail == "") return true
    else {alert(fail);
        fail = "Please try again<br>"+fail;
        document.getElementById('signuperror').innerHTML=fail;
        return false}
}
function validateUsername(field)
{
    if (field=="") return "No Username was entered.\n"
    else if (/[^a-zA-Z0-9_-]/.test(field))
        return "Invalid characters in username"
    return ""
}
function validatePassword(field)
{
    if (field=="") return "No Password was entered.\n"
    else if (field.length < 6) return "Password needs to be 6 or more characters.\n"
    else if (/[^a-zA-Z0-9_-]/.test(field))
        return "Invalid characters in password"
    return ""
}
function showhide(buttonID,pwNames)
{
    //obviously need an shbutton with the text (show) or (hide) to start with
    //uses name to find the elements
    thetext = document.getElementById(buttonID).innerHTML;
    if (thetext=="(show)") {
        for (i = 0; i < pwNames.length; i++) {
            console.log(pwNames[2])
            document.getElementsByName(pwNames[i])[0].type = "text"
        }
        document.getElementById(buttonID).innerHTML = "(hide)"
    }
    else {
        for (i = 0; i < pwNames.length; i++) {
            document.getElementsByName(pwNames[i])[0].type = "password"
        }
        document.getElementById(buttonID).innerHTML = "(show)"
    }
    return true
}
