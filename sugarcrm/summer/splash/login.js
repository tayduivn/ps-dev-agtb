var login = {

    startup:function (status) {
        var images = [
            {url:'static/sunflower.jpg', text:'It\'s like a field of sunflowers.'},
            {url:'static/beach.jpg', text:'It\'s like a day at the beach.'},
            {url:'static/hotairballoon.jpg', text:'It\'s like a hot air balloon festival.'},
            {url:'static/flamingos.jpg', text:'It\'s like a flock of flamingos.'},
            {url:'static/houses.jpg', text:'It\'s like a flock of flamingos.'},
            {url:'static/houses2.jpg', text:'It\'s like a flock of flamingos.'}
        ];
        var selected = images[Math.floor(Math.random() * images.length)];
        $('body').css({
            background:'url(' + selected.url + ') no-repeat center center fixed ',
            'background-size':'cover'

        });
        $('#catchphrase').html(selected.text);
        login.setupLoginForm($('#login'));
        login.setupRegisterForm($('#register'));
        login.setupResetForm($('#reset'));
        $('#register_btn').click(login.showRegisterForm)
        $('#cancel_register_btn').click(login.showLoginForm)
        $('#cancel_reset_btn').click(login.showLoginForm)
        $('#reset_lnk').click(login.showReset)
        login.displayMessages(status);
        console.log(status);
        if(status && status.display){
            login.showForm(status.display);
        }
        if(status.login_response)login.response(status.login_response);

    },


    setupLoginForm:function (form) {
        form.submit(function (event) {
            event.preventDefault();
            var $form = $(this)
            var email = $form.find('input[name="email"]').val();
            var password = $form.find('input[name="password"]').val();
            $form.find('input[name="password"]').val('')

            $.post('rest/users/authenticate', {email:email, password:password}, login.response);
        });

    },

    setupRegisterForm:function (form) {
        form.submit(function (event) {
            event.preventDefault();
            $.post('rest/users', $(this).serialize(), login.registerResponse);
        });
    },

    setupResetForm:function (form) {
          form.submit(function (event) {
              event.preventDefault();
              var $form = $(this)
              var email = $form.find('input[name="email"]').val();
              $form.find('input[name="email"]').val('')
              if(email == '')return;
              $.post('rest/users/resetpassword', {email:email}, login.resetResponse);
          });

      },

    resendActivation:function (email) {
        $.post('rest/users/resendActivation', {email:email}, login.displayMessages);
    },

    resetResponse:function (data) {
        login.showLoginForm() ;
        login.displayMessages(data);

    },

    response:function (data) {
    	if(data.popup) {
    		document.location.href = data.popup;
    		return;
    	}
    	if(login.popup) {
    		login.popup.close();
    		login.popup = null;
    	}
    	login.refreshInstances(data)
    },

    refreshInstances:function (data) {
        if (!data.error) {
    		$('#instancelist').html("");
        	if(data.instances.length == 0) {
        		if(!data.info) {
        			data.info = []
        		}
            	data.info[data.info.lastIndexOf()+1] = "We were unable to detect any instances accessible to you. Please ask somebody to invite you to their instance or contact support.";
            }
            for (i in data.instances) {
                $('#instancelist').append('<li><a href="#" class="instance" data-id="' + data.instances[i].id + '">' + data.instances[i].name + ' by ' + data.instances[i].owner.name + '</a></li>')
            }
            $('.instance').click(login.selectInstance);
            $('#instances_refresh').click(
            		function () { $.get("rest/instances", null, login.refreshInstances); }
            	);
            login.showInstances();
            $('.username').html(data.user.first_name + ' ' + data.user.last_name);
        }
        login.displayMessages(data);

    },

    displayMessages:function (data) {
        if(!data)return;
        var notices = '';
        if (data.success) {
            if (!(data.success instanceof Array)) {
                data.success = [data.success];
            }
            for (i in data.success) {
               notices += "<div class='alert alert-block alert-success fade in '><strong>" + data.success[i] + "</strong></div>"

            }
        }
        if (data.error) {
            if (!(data.error instanceof Array)) {
                data.error = [data.error];
            }
            for (i in data.error) {
                notices += "<div class='alert alert-block alert-error fade in '><strong>" + data.error[i] + "</strong></div>"

            }
        }

        if (data.info) {
            if (!(data.info instanceof Array)) {
                data.info = [data.info];
            }
            for (i in data.info) {
                notices += "<div class='alert alert-block alert-info fade in '><strong>" + data.info[i] + "</strong></div>"

            }
        }
        console.log(notices);
        $('#notices').html(notices);


    },


    registerResponse:function (data) {

        if (!data.error) {
            $('#register').get(0).reset()
            login.showLoginForm();
        }
        login.displayMessages(data);
    },

    selectInstance:function (event) {
        var id = $(this).data('id');
        $.get('rest/instances/' + id, null, login.selectInstanceResponse)

    },

    selectInstanceResponse:function (data) {
        login.displayMessages(data);
        if (data.url) {
            window.location.href = data.url;
        }
    },

    showForm: function(id){
            if ($('.alert').alert)$('.alert').alert('close');
            $('form').toggle(false);
            $('#' + id).toggle(true);
    },
    showRegisterForm:function (event) {
        login.showForm('register');
    },

    showLoginForm:function (event) {
        login.showForm('login');

    },
    showInstances:function (event) {
        login.showForm('instances');

    },
    showReset:function (event) {
        login.showForm('reset');

    },


    showResetPass:function (event) {
        login.showForm('resetpass');

     }




};



