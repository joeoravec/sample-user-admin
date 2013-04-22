<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>ECM WebCM - Admin Reporting</title>
<link href="styles.css" rel="stylesheet" type="text/css" />
<link href="css/ecm-styles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript"  src="js/handlebars.js"></script>
</head>
<body>
<div id="main">
  <?php 
include('headerMain.inc');
?>
<?php 
include('tabsMain.inc');
?>
<div id="content">
  <div class="container">
    <div id="userResults">
    </div>
    <div id="linkbox"></div>
  </div>
<?php
//include('activityLog.inc');
?>
<script id="users-edit-template" type="text/x-handlebars-template">
    {{#if user_id}}
    <h2 class="pageHeader">Edit User: {{user_full}}</h2>   
    {{else}}
    <h2 class="pageHeader">New User</h2> 
    {{/if}}
    <form id="editUserForm" data-usercount="{{thisUserCount}}">
    
      <input type="hidden" value="{{user_id}}" id="user_id" name="user_id" />
      
      <div id="form-one-column">
        <fieldset>
          <ul>
            <li>
              <label>First Name:</label>
              <input id="user_first" name="user_first" type="text" class="fieldLarge" value="{{user_first}}" />
            </li>
            <li>
              <label>Middle Name:</label>
              <input id="user_middle" name="user_middle" type="text" class="fieldLarge" value="{{user_middle}}" />
            </li>
            <li>
              <label>Last Name:</label>
              <input id="user_last" name="user_last" type="text" class="fieldLarge" value="{{user_last}}" />
            </li>
            <li>
              <label>Login:</label>
              <input id="user_login" name="user_login" type="text" class="fieldLarge" value="{{user_login}}" />
            </li>
            <li>
              <label>Password:</label>
              <input id="user_password" name="user_password" type="text" class="fieldLarge" value="{{user_password}}" />
            </li>
            <li>
              <label>Email:</label>
              <input id="user_email" name="user_email" type="text" class="fieldLarge" value="{{user_email}}" />
            </li>
          </ul>
         </fieldset>
        </div>
        <div class="actionbuttonsSmall">
          <ul class="buttonLarge">
            <li><a href="#" class="button-user-edit"><span>Save</span></a></li>
            <li><a href="#" class="button-user-cancel"><span>Cancel</span></a></li>
          </ul>
        </div>
      </form>
</script>
<script id="users-table-template" type="text/x-handlebars-template">

  <div class="entry">
  <h2 class="pageHeader">Manage Users</h2>
  <table class="dataTable">
  <thead>
    <tr>
    <th><a href="#" data-sort-col="user_first" >First Name</a></th>
    <th><a href="#" data-sort-col="user_last" >Last Name</a></th>
    <th></th>
  </thead>
  <tbody>
{{#each source_list}}
  <tr data-usercount="{{@index}}" {{usertype_class}}>
    <td>{{user_first}}</td>
    <td>{{user_last}}</td>
    <td>
       <a href="#" class="edituserlink" data-usercount="{{@index}}">Edit</a>
       
      {{allow_delete @index}}
      
      </td>
    </tr>
{{/each}}
</table>
        <div class="actionbuttonsSmall">
          <ul class="buttonLarge">
            <li><a href="#" class="button-user-new"><span>New User</span></a></li>
          </ul>
        </div>
</div>

</script>

<script src="js/jquery.js"></script>
<script type="text/javascript">
(function(){
  var Views = {
    UserTable: {
      render: 
        function() {
          Users.allUserData.sort(sortByLast);
          Users.allUsers.sort(sortByLast);
          var newcontent = {source_list: Users.allUserData};
          var source     = $("#users-table-template").html();
          var template   = Handlebars.compile(source);
          var html       = template(newcontent);
          $('#userResults').html(html);
          $('#userResults tr.nonUserType').remove();
          $('#userResults tr:odd').addClass('odd');         
        },
      newLink: 
        $('#userResults').on('click', 'a.button-user-new', function(){
          Views.UserEdit.render({user_id: 0, user_first: '', user_last: ''});
        }),
      editLink: 
        $('#userResults').on('click', 'a.edituserlink', function(){
          thisUserCount = $(this).attr('data-usercount');
          Users.allUsers[thisUserCount].editUser(thisUserCount);
        }),
      deleteLink: 
        $('#userResults').on('click', 'a.deleteuserlink', function(){
          thisUserCount = $(this).attr('data-usercount');
          Users.allUsers[thisUserCount].deleteUser();
        })
    },
    UserEdit: {
      render: function(userObj, thisUserCount) {
        thisUserCount = thisUserCount || 0;
        console.log("form with info for " + userObj.user_id);
        userObj.thisUserCount = thisUserCount;
        console.log(userObj);
          var newcontent = userObj;
          newcontent.user_list = Users.allUsers;
          console.log(newcontent);
          var source     = $("#users-edit-template").html();
          var template   = Handlebars.compile(source);
          var html       = template(userObj);
          $('#userResults').html(html);          
      },
      saveEdit: 
        $('#userResults').on('click', 'a.button-user-edit', function(){
          var form_data = $('#editUserForm').serializeObject();
          var thisUserCount = $('#editUserForm').data('usercount');
          if (!thisUserCount){
            thisUserCount = Users.allUsers.length;
            Users.allUsers[thisUserCount] = new Users.User(form_data);
          }
          Users.allUsers[thisUserCount].saveUser(form_data);
        }),
      cancelEdit: 
        $('#userResults').on('click', 'a.button-user-cancel', function(){
          Views.UserTable.render();
        })
    }
  };

  var Users = {
    User: function (args) {
      //this.obj = args;
      for (var key in args) {
          if (args.hasOwnProperty(key)) { //make sure that the key you get is an actual property of an object, and doesn't come from the prototype:
              this[key] = args[key];
          }
      }
      // initialize
      this.makeFullName();
    },
    allUserData: [],
    allUsers: []
  };
   
  Users.User.prototype = {
    constructor: Users.User,
    makeFullName: function() {
      this.user_full = this.user_first + " " + this.user_last;
      return this.user_full;    
    },
    editUser: function(thisUserCount) {
      // make form with edit
      console.log('edit: ' + this.user_id); 
      Views.UserEdit.render(this, thisUserCount); 
    },
    deleteUser: function() {
      // send methodType=destroy with uid
      console.log('destroy: ' + this.user_id);
      this.user_type = 9999;
      fetchUsers('destroy',this.user_id);    
    },
    saveUser: function(formData) {
      // send methodType=create with userJson data
      // include value for user_id if edit  
      for (var key in formData) {
          if (formData.hasOwnProperty(key)) { //make sure that the key you get is an actual property of an object, and doesn't come from the prototype:
              this[key] = formData[key];
          }
      }
      this.makeFullName();
      fetchUsers('create','0',JSON.stringify(formData));
    }
  }

  var fetchUsers = function(methodType, uid, userJson) {
      var ajax_action = 'methodType='+methodType;
      if (uid) {ajax_action+='&uid='+uid;}
      if (methodType == "create") {
        ajax_action+='&userJson='+userJson;
      }
      fetchedresults = $.ajax({
            url: "controllers/cont_adminUser.php",
            type: "POST",
            dataType: "json",
            data: ajax_action,
            success: function(data){
              // retrieve callback
              if (methodType=="retrieve") {
                this.retrieveAll(data);
              } else if (methodType=="create") {
                this.saveUser(data);
              } else if (methodType=="destroy") {
                this.destroyUser(data);
              }
            },
            error: function(error){
                // console.log("Error:");
                // console.log(error);
            },
            retrieveAll: function(data){
              Users.allUserData = data.data;
              Users.allUserData.sort(sortByLast);
              for (i=0; i<Users.allUserData.length; i++) {
                Users.allUsers[i] = new Users.User(Users.allUserData[i]);
              }
              Views.UserTable.render();
              if (data.data.length === 1) {
                Users.allUserData = data.data[0];
              }
            },
            saveUser: function(data){
              console.log(data.data);
              var saved_id = data.data.user_id;
              var found = false;
              for (var i = 0; i < Users.allUserData.length; i++) {
                  if (Users.allUserData[i].user_id == saved_id)
                  {
                      Users.allUserData[i] = data.data;
                      found = true;
                      Views.UserTable.render();
                      break;
                  }
              }
              if (!found) {
                Users.allUserData[i] = data.data;
                for (var i = 0; i < Users.allUsers.length; i++) {
                    if (Users.allUsers[i].user_id == 0)
                    {
                        Users.allUsers[i].user_id = saved_id;
                        break;
                    }
                }
                Views.UserTable.render();
              }
            },
            destroyUser: function(data){
              removed_id = data.data.user_id;
              for (var i = 0; i < Users.allUserData.length; i++) 
              {
                  if (Users.allUserData[i].user_id == removed_id)
                  {
                      Users.allUserData[i].user_type = 9999;
                      Views.UserTable.render();
                      break;
                  }
              }
            }
      });
    
  }

  // Helper functions
  $.fn.serializeObject = function() {
      var o = {};
      var a = this.serializeArray();
      $.each(a, function() {
          if (o[this.name] !== undefined) {
              if (!o[this.name].push) {
                  o[this.name] = [o[this.name]];
              }
              o[this.name].push(this.value || '');
          } else {
              o[this.name] = this.value || '';
          }
      });
      return o;
  };

  var sortByLast = function(a,b) {
    if (a.user_last.toLowerCase() > b.user_last.toLowerCase()) {
      return 1;
    } else if (a.user_last.toLowerCase()
     < b.user_last.toLowerCase()) {
      return -1;
    } else {
      return 0;
    }
  }

// template helper functions

  Handlebars.registerHelper('usertype_class', function() {
    if (this.user_type == '9999') {
      return new Handlebars.SafeString(' class="nonUserType"');
    }
  });
  Handlebars.registerHelper('allow_delete', function(thisIndex) {
    if (this.open_cases === '0') {
      return new Handlebars.SafeString(' | <a href="#" class="deleteuserlink" data-usercount="'+thisIndex+'">Delete</a>');
    } else {
      return '';
    }
    return Math.ceil(daysBetween(this.date_assigned,this.date_archived));
  });

  // Initialize
  fetchUsers('retrieve');

//allUserData = [{"user_id":"1","user_login":"uiop","user_password":"ghkj","user_first":"AIM CM","user_middle":"","user_last":"Admin","user_email":"joeoravec@cvnbcvnbc.com","user_type":"2","user_sub":"52,43","canCreateCase":"1","canArchiveCase":"1","canDeleteCase":"1"},{"user_id":"5","user_login":"qwer","user_password":"asdf","user_first":"Kathy","user_middle":"","user_last":"Miller","user_email":"kmiller@asdf.com","user_type":"2","user_sub":null,"canCreateCase":"1","canArchiveCase":"1","canDeleteCase":"1"}];




})();
</script>



</body>
</html>
