function update_facebook_details() {
  document.getElementById('facebook-user-pic').innerHTML = 
      "<span>"
    + "<fb:profile-pic uid='loggedinuser' facebook-logo='true'></fb:profile-pic>"
    + "<fb:name uid='loggedinuser' useyou='false'></fb:name><br />"
    + "You are logged in with your Facebook account. <br />"
    + "<a href='/auth/facebook/facebook_disconnect.php'>(Click here to Disconnect from Facebook)</a>"
    + "</span>";
  FB.api('/me', function(response) {
    if (document.getElementById('id_email').value == '' && response.email) {
      document.getElementById('id_email').value = response.email;
      document.getElementById('id_email2').value = response.email;
    }
    if (document.getElementById('id_firstname').value == '' && response.first_name) {
      document.getElementById('id_firstname').value = response.first_name;
    }
    if (document.getElementById('id_lastname').value == '' && response.last_name) {
      document.getElementById('id_lastname').value = response.last_name;
    }  
    if (document.getElementById('id_city').value == '' && response.address && response.address.city) {
      document.getElementById('id_city').value = response.address.city;
    }  
    if (document.getElementById('id_country').value == '' && response.address && response.address.country) {
      SelectObject = document.getElementById('id_country');
      for(index = 0; index < SelectObject.length; index++) {
        if(SelectObject[index].text == response.address.country) {
          SelectObject.selectedIndex = index;
        }
      }
    }
    if (document.getElementById('id_timezone').value == 99 && response.timezone) {
      SelectObject = document.getElementById('id_timezone');
      for(index = 0; index < SelectObject.length; index++) {
        if(SelectObject[index].value == response.timezone) {
          SelectObject.selectedIndex = index;
        }
      }
    }    
    if (document.getElementById('id_description_editor').value == '' && response.bio) {
      document.getElementById('id_description_editor').value = response.bio;
    }
    else if (document.getElementById('id_description_editor').value == '' && response.quotes) {
      document.getElementById('id_description_editor').value = response.quotes;
    }
    
    //In theory Interests should come through from Facebook, but they don't seem to.

    if (document.getElementById('id_phone2').value == '' && response.mobile_phone) {
      document.getElementById('id_phone2').value = response.mobile_phone;
    }
    
    if (document.getElementById('id_url').value == '' && response.website) {
      document.getElementById('id_url').value = response.website;
    }
    else if (document.getElementById('id_url').value == '' && response.link) {
      document.getElementById('id_url').value = response.link;
    }
  });
  
  FB.XFBML.parse();
}

function update_facebook_login() {
  document.getElementById('facebook-user-login').innerHTML = 
      "<span>"
    + "<fb:profile-pic uid='loggedinuser' facebook-logo='true'></fb:profile-pic>"
    + "<fb:name uid='loggedinuser' useyou='false'></fb:name>"
    + "<form action='"+ fb_login_home +"' method='post'><input type='submit' value='Login with Facebook account'/>"
    + "<input type='hidden' name='fb_login' value='1' /></form>"
    + "</span>";
  FB.XFBML.parse();
}

function update_facebook_verification() {
  document.getElementById('facebook-user-login').innerHTML = 
      "<span>"
    + "<fb:profile-pic uid='loggedinuser' facebook-logo='true'></fb:profile-pic>"
    + "<fb:name uid='loggedinuser' useyou='false'></fb:name>"
    + "</span>";
  FB.XFBML.parse();
}