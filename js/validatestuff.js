function validate(form,formtype)
{
    //formtype  is a string including any of the letters upre
    var failu,failp,failp2,failup,faile;
    failu = failp = failp2 = failup = faile = "";
    if (/u/.test(formtype)) {
        var username = form.username.value;
        failu = validateUsername(username);
    }
    if (/p/.test(formtype)) {
        var password = form.password.value;
        failp = validatePassword(password);
        if ((/u/.test)&& (username==password)) failup = "Password may not be the same as your Username! ";
    }
    if (/r/.test(formtype)) {
        var password2 = form.retype_password.value;
        if (password !== password2) failp2 = "Passwords did not match. ";
    }
    //only rudimentary email testing
    if (/e/.test(formtype)){
        var email = form.email.value;
        if (!/.+@.+..+/.test(email)) faile = "Non-conformal Email address.";
    }
    var fail = failu+failp+failp2+failup+faile;
    fail = fail.replace(/\. */g,".\n");
    //They should have be checked already by HTML for validity
    if (fail == "") return true
    else {alert(fail);
          fail = "Please try again<br>"+fail.replace(/\n/g, "<br />");
          document.getElementById('signuperror').innerHTML=fail;
          return false}
}
/*function validatelogin(form)
{
    //console.log('here')
    username = form.username.value;
    password = form.password.value;
    failu = validateUsername(username);
    failp = validatePassword(password);

    fail = "";
    fail += failu;
    fail += failp;
    //They should have be checked already by HTML for validity

    if (fail == "") return true;
    else {alert(fail);
        fail = "Please try again<br>"+fail;
        document.getElementById('signuperror').innerHTML=fail;
        return false}
}*/
function validateUsername(field)
{
    failtype = "";
    if (field=="") failtype = "No Username was entered. ";
    else if (/[^a-zA-Z0-9_-]/.test(field))
        failtype = "Invalid characters in username. ";
    else if (/.*guest.*/.test(field)) failtype = "Lowercase guest cannot be included in username. ";
    return failtype;
}
function validatePassword(field)
{
    failtype = "";
    if (field=="") failtype = "No Password was entered.";
    else if (field.length < 6) failtype = "Password needs to be 6 or more characters.";
    else if (/[^a-zA-Z0-9_-]/.test(field)) failtype = "Invalid characters in password. ";
    return failtype;
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
