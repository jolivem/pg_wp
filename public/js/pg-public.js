
(function ($) {

    $(function () {

        // Update the photo counter on the edit-photo page
        function updatePhotoCounter() {

            let elem = document.getElementById("images_id");
            if (elem) {

                // set the photo counter
                let images = elem.value.split(',');
                //console.log("edit-photo-option images", images);
                if (images.length > 1) {
                    const postid = document.getElementById("post_id").value;
                    //console.log("edit-photo-option postid", postid);
                    const isNumber = (element) => element == postid;
                    const index = images.findIndex(isNumber);
                    if (index != -1) {
                        let cpt = index+1;
                        document.getElementById('cpt-photo').innerHTML = cpt + "/" + images.length;
                        //console.log("edit-photo-option index", index);
                        $('.fa-angle-double-left').css('color', '');
                        $('.fa-angle-double-right').css('color', '');
                        if (index == 0){
                            $('.fa-angle-double-left').css('color', 'darkgray');
                        }
                        if (index == images.length-1){
                            $('.fa-angle-double-right').css('color', 'darkgray');
                        }
                    }
                }
            }
        }

        // call the function when ready
        updatePhotoCounter();

        
        $(document).find('#user-galleries-create').on('click', function(e){
            //console.log("user-galleries-create click", e);
            let edit_gallery_url = document.getElementById('pg_edit_gallery_url').value;
            edit_gallery_url += "?gid=-1";
            window.location = edit_gallery_url;
        });


        $(document).find('.admin-photo-option').on('click', function(e){
            //console.log("admin-photo-option click", e);
            e.preventDefault();
            const postid = e.target.dataset.postid;
            //console.log("admin-photo-option thumbs-up postid=", postid)
            let nonce = document.getElementById('pg_nonce')?.value;
            let admin_url = document.getElementById('pg_admin_ajax_url')?.value;

            const formData = new FormData();
            formData.append('nonce', nonce);
            formData.append('pid', postid);
            if (e.target.classList.contains("fa-thumbs-up")) {
    
                // get the address selected 
                let name = "address" + postid;
                let address = document.querySelector('input[name="'+name+'"]:checked');
                if (address) {
                    formData.append('address', address.value);
                }

                formData.append('action', 'admin_valid_photo');

                jQuery.ajax({
                    method: 'POST',
                    url: admin_url,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response){
                        //console.log("valid success");
                        // remove the photo from list with animation
                        let ancestor = e.target.parentNode.parentNode;
                        ancestor.style.animationDuration = '.35s';
                        ancestor.style.animationName = 'slideOutRight';
                    
                        setTimeout(() => {
                            ancestor.remove(); // Remove the corresponding list item after animation
                        }, 300); // Duration of the animation    
        
                        // display a toast
                        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                        toastElList.map(function(toastEl) {
                            return new bootstrap.Toast(toastEl)
                        })
                    }
                    // TODO handle error
                });                          
            }
            if (e.target.classList.contains("fa-thumbs-down")) {
        
                formData.append('action', 'admin_reject_photo');
    
                jQuery.ajax({
                    method: 'POST',
                    url: admin_url,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response){
                        //console.log("delete success");
                        // remove the photo from list with animation
                        let ancestor = e.target.parentNode.parentNode;
                        ancestor.style.animationDuration = '.35s';
                        ancestor.style.animationName = 'slideOutLeft';
                    
                        setTimeout(() => {
                            ancestor.remove(); // Remove the corresponding list item after animation
                        }, 300); // Duration of the animation    
        
                        // display a toast
                        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                        toastElList.map(function(toastEl) {
                            return new bootstrap.Toast(toastEl)
                        })
        
                    }
                    // TODO handle error
                });                         
            }
        });

        // click ti valid or not the user web site
        $(document).find('.admin-url-option').on('click', function(e){
            //console.log("admin-url-option click", e);
            e.preventDefault();
            const userid = e.target.dataset.userid;
            let nonce = document.getElementById('pg_nonce').value;
            let admin_url = document.getElementById('pg_admin_ajax_url').value;
    
            const formData = new FormData();
            formData.append('nonce', nonce);
            formData.append('uid', userid);

            if (e.target.classList.contains("fa-thumbs-up")) {
    
                formData.append('action', 'admin_valid_url');

                jQuery.ajax({
                    method: 'POST',
                    url: admin_url,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response){
                        //console.log("valid success");

                        // remove the photo from list with animation
                        let ancestor = e.target.parentNode.parentNode;
                        //console.log("valid success ancestor", ancestor);
                        ancestor.style.animationDuration = '.35s';
                        ancestor.style.animationName = 'slideOutRight';
                    
                        setTimeout(() => {
                            ancestor.remove(); // Remove the corresponding list item after animation
                        }, 300); // Duration of the animation    
        
                        // display a toast
                        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                        toastElList.map(function(toastEl) {
                            return new bootstrap.Toast(toastEl)
                        })
        
                    }
                    // TODO handle error
                });                          
            }
            if (e.target.classList.contains("fa-thumbs-down")) {

                formData.append('action', 'admin_reject_url');

                jQuery.ajax({
                    method: 'POST',
                    url: admin_url,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response){
                        //console.log("delete success");

                        // remove the photo from list with animation
                        let ancestor = e.target.parentNode.parentNode;
                        //console.log("delete success ancestor", ancestor);
                        ancestor.style.animationDuration = '.35s';
                        ancestor.style.animationName = 'slideOutLeft';
                    
                        setTimeout(() => {
                            ancestor.remove(); // Remove the corresponding list item after animation
                        }, 300); // Duration of the animation    
        
                        // display a toast
                        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                        toastElList.map(function(toastEl) {
                            return new bootstrap.Toast(toastEl)
                        })
        
                    }
                    // TODO handle error
                });                         
            }
        });

        // Process when user click on step-forward or step-backward
        // and when user click on angle-double-right or angle-double-left
        $(document).find('.edit-photo-option').on('click', function(e){
            //console.log("edit-photo-option click", e);
            e.preventDefault();
            let images_id = document.getElementById("images_id").value;
            let images = images_id.split(',');
            //console.log("edit-photo-option images", images);
            if (images.length > 1) {
                const postid = e.target.dataset.postid;
                //console.log("edit-photo-option postid", postid);
                const isNumber = (element) => element == postid;
                const index = images.findIndex(isNumber);
                if (index != -1) {
                    //console.log("edit-photo-option index", index);

                    if (e.target.classList.contains("fa-angle-double-right")) {
                        // find the following image
                        if (index < images.length -1) {
                            let next_id = images[index + 1];
                            //console.log("edit-photo-option next_id", next_id);
                            let ancestor = e.target.parentNode.parentNode.parentNode.parentNode;
                            let img_cont = ancestor.querySelector(".pg-edit-img-cont");
                            //console.log("edit-photo-option img_cont", img_cont);
                            if (img_cont) {
                                img_cont.style = '';
                                img_cont.style.animationDuration = '.45s';
                                img_cont.style.animationName = 'fadeOutLeft';
                                setTimeout(() => {
                                    let img = ancestor.querySelector(".pg-edit-img");
                                    img.style = 'display: none;';
                                    user_get_photo(next_id);
                                }, 200); // Duration of the animation    
                            }
    
                            
                        }
                    }
                    else if (e.target.classList.contains("fa-angle-double-left")) {
                        // find the previous image
                        if (index > 0) {
                            let previous_id = images[index - 1];

                            let ancestor = e.target.parentNode.parentNode.parentNode.parentNode;
                            let img_cont = ancestor.querySelector(".pg-edit-img-cont");
                            if (img_cont) {
                                img_cont.style = '';
                                img_cont.style.animationDuration = '.45s';
                                img_cont.style.animationName = 'fadeOutRight';
                                setTimeout(() => {
                                    let img = ancestor.querySelector(".pg-edit-img");
                                    img.style = 'display: none;';
                                    user_get_photo(previous_id);
                                }, 200); // Duration of the animation                                    
                            }

                        }
                    }
                    //let cpt = index+1;
                    //document.getElementById('cpt-photo').innerHTML = cpt + "/" + images.length;
                }
            }
            // console.log("edit-photo-option OUT");

        });  

        $(document).find('#btn-add-single-photo').on('click', function(e){
            // console.log("btn-add-single-photo click", e);
            e.preventDefault();
            const galid = e.target.dataset.galid;
            let download_single_url = document.getElementById('pg_download_single_url').value;
            download_single_url += "?gid=";
            download_single_url += galid;
            window.location = download_single_url;
        });  

        const user_get_photo = (postid) => {
            //console.log("user_get_photo IN");
            let nonce = document.getElementById('pg_nonce').value;
            let admin_url = document.getElementById('pg_admin_ajax_url').value;

            const formData = new FormData();
            formData.append('action', 'user_get_photo');
            formData.append('nonce', nonce);
            formData.append('pid', postid);

            //console.log("user_get_photo AJAX");
            jQuery.ajax({
                method: 'POST',
                url: admin_url,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response){
                    //console.log("user_get_photo success", response);
                    let img_cont = document.querySelector(".pg-edit-img-cont");
                    if (img_cont) {
                        // remove animation
                        img_cont.style = '';
                    }

                    // update the page
                    $("#photo-description").val(response.data.content);
                    $("#user_status").val(response.data.user_status);
                    if (response.data.user_status == 'public') {
                        $("#user_status").prop("checked", true);
                    }
                    else {
                        $("#user_status").prop("checked", false);
                    }
                    $("#user_status_label").html(response.data.user_status_label);
                    $("#post_id").val(response.data.pid);

                    $("[data-postid]").each( function() {
                        //console.log("user_get_photo data-postid found");
                        $(this).data('postid', response.data.pid);
                        $(this).attr('data-postid', response.data.pid);
                    });

                    $(".flex-container-photo").find("img").attr("src", response.data.img_src);
                    //console.log("edit-photo-option img_cont", img_cont);
                    let img = document.querySelector(".pg-edit-img");
                    img.style = '';
                    //console.log("edit-photo-option img display XXXXXXXXXXX", img);
                    if (img_cont) {
                        img_cont.style.animationDuration = '.35s';
                        img_cont.style.animationName = 'zoomIn';
                    }

                    //console.log("user_get_photo update done");
                    updatePhotoCounter();
                }
                // TODO handle error
            });          
        };


        $(document).find('.user-photo-option').on('click', function(e){
            // console.log("user-photo-option click", e);
            e.preventDefault();
            if (e.target.classList.contains("fa-edit")) {
                const postid = e.target.dataset.postid;
                //console.log("user-photo-option edit postid=", postid)
                let edit_photo_url = document.getElementById('pg_edit_photo_url').value;
                edit_photo_url += "?pid=";
                edit_photo_url += postid;
                window.location = edit_photo_url;

            }
            if (e.target.classList.contains("fa-trash")) {
                const postid = e.target.dataset.postid;
                //console.log("user-photo-option trash postid=", postid)

                e.preventDefault();

                //let post_id = document.getElementById('post_id').value;
                let nonce = document.getElementById('pg_nonce').value;
                let admin_url = document.getElementById('pg_admin_ajax_url').value;
        
                const formData = new FormData();
                formData.append('action', 'user_delete_photo');
                formData.append('nonce', nonce);
                formData.append('pid', postid);
    
                jQuery.ajax({
                    method: 'POST',
                    url: admin_url,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response){
                        //console.log("delete success");

                        // remove the photo from list with animation
                        let ancestor = e.target.parentNode.parentNode;
                        //console.log("delete success ancestor", ancestor);
                        ancestor.style.animationDuration = '.35s';
                        ancestor.style.animationName = 'slideOutLeft';
                    
                        setTimeout(() => {
                            ancestor.remove(); // Remove the corresponding list item after animation
                        }, 300); // Duration of the animation    
        
                        // display a toast
                        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                        toastElList.map(function(toastEl) {
                            return new bootstrap.Toast(toastEl)
                        })
                        
                        var myToastEl = document.getElementById('delete-photo-success')
                        var myToast = bootstrap.Toast.getInstance(myToastEl);
                        myToast.show();
        
                    }
                    // TODO handle error
                });                         
            }
        });

        //var deleteConfirModal;
        $(document).find('.user-gallery-option').on('click', function(e){
            // console.log("user-gallery-option click", e);
            if (e.target.classList.contains("fa-edit")) {
                const galid = e.target.dataset.galid;
                //console.log("user-gallery-option edit galid=", galid);
                let edit_gallery_url = document.getElementById('pg_edit_gallery_url').value;
                edit_gallery_url += "?gid=";
                edit_gallery_url += galid;
                window.location = edit_gallery_url;
            }
            else if (e.target.classList.contains("fa-eye")) {
                const galuuid = e.target.dataset.galuuid;
                //console.log("user-gallery-option view galuuid=", galuuid);
                let edit_gallery_url = document.getElementById('pg_show_gallery_url').value;
                edit_gallery_url += "?guuid=";
                edit_gallery_url += galuuid;
                window.location = edit_gallery_url;
            }
            else if (e.target.classList.contains("fa-share-alt")) {
                const galuuid = e.target.dataset.galuuid;
                // console.log("user-gallery-option view galuuid=", galuuid);
                let edit_gallery_url = document.getElementById('pg_show_gallery_url').value;
                edit_gallery_url += "?guuid=";
                edit_gallery_url += galuuid;

                navigator.clipboard.writeText(edit_gallery_url).then(() => {
                    // display a toast
                    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
                    // console.log("user-gallery-option toastElList=", toastElList);
                    toastElList.map(function(toastEl) {
                        // console.log("user-gallery-option toastEl=", toastEl);
                        return new bootstrap.Toast(toastEl);
                    });

                    var myToastEl = document.getElementById('copy-to-clipboard');
                    var myToast = bootstrap.Toast.getInstance(myToastEl);
                    myToast.show();                
                });

            }
            e.preventDefault();
        });

        // edit gallery when click on gallery text container and miniatures
        $(document).on('click', '.miniature1, .miniature2, .miniature3, .pdb-descr-container', function(e){
            //console.log("miniature1 click", e.target);
            const closest = e.target.closest(`[data-galid]`);
            //console.log("miniature1 closest", closest);
            
            if (closest) {
                const galid = closest.dataset.galid;
                //console.log("user-gallery-option edit galid=", galid);
                let edit_gallery_url = document.getElementById('pg_edit_gallery_url').value;
                edit_gallery_url += "?gid=";
                edit_gallery_url += galid;
                window.location = edit_gallery_url;
            }
            e.preventDefault();
        });

        $(document).find('#modal-delete-gallery').on('click', function(e){
            let galid = document.getElementById('gallery-id').value;
            //console.log("modal-delete-gallery galid=", galid);
            //deleteConfirModal.toggle();
            e.preventDefault();

            //let post_id = document.getElementById('post_id').value;
            let nonce = document.getElementById('pg_nonce').value;
            let admin_url = document.getElementById('pg_admin_ajax_url').value;
    
            const formData = new FormData();
            formData.append('action', 'user_delete_gallery');
            formData.append('nonce', nonce);
            formData.append('gid', galid);

            jQuery.ajax({
                method: 'POST',
                url: admin_url,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response){
                    //console.log("delete success");
                    
                    let url = document.getElementById('pg_my_galleries_url').value;
                    window.location = url;
    
                    // remove the gallery with animation
                    // find the gallery elements
                }
                // TODO handle error
            });            
        });
        
        // edit gallery when click on gallery text container and miniatures
        $(document).on('click', '.miniature', function(e){
            //console.log("miniature click", e.target);
            const closest = e.target.closest(`[data-id]`);
            //console.log("miniature closest", closest);

            if (closest) {
                const postid = closest.dataset.id;
                //console.log("gallery-photo-option postid=", postid);
                let edit_photo_url = document.getElementById('pg_edit_photo_url').value;
                edit_photo_url += "?pid=";
                edit_photo_url += postid;
                const gallery_id = document.getElementById('gallery-id')?.value;
                if (gallery_id) {
                    edit_photo_url += "&gid=";
                    edit_photo_url += gallery_id;
                }
                window.location = edit_photo_url;                
                // const galid = closest.dataset.galid;
                // //console.log("user-gallery-option edit galid=", galid);
                // let edit_gallery_url = document.getElementById('pg_edit_gallery_url').value;
                // edit_gallery_url += "?gid=";
                // edit_gallery_url += galid;
                // window.location = edit_gallery_url;
            }
            e.preventDefault();
        });

        $(document).find('.gallery-photo-option').on('click', function(e){
            //console.log("gallery-photo-option click", e);
            e.preventDefault();
            if (e.target.classList.contains("fa-edit")) {
                const postid = e.target.parentElement.dataset.id;
                //console.log("gallery-photo-option postid=", postid);
                let edit_photo_url = document.getElementById('pg_edit_photo_url').value;
                edit_photo_url += "?pid=";
                edit_photo_url += postid;
                const gallery_id = document.getElementById('gallery-id').value;
                edit_photo_url += "&gid=";
                edit_photo_url += gallery_id;
                window.location = edit_photo_url;
            }
            if (e.target.classList.contains("fa-trash")) {
                                
                let ancestor = e.target.parentNode.parentNode.parentNode;
                ancestor.style.animationDuration = '.35s';
                ancestor.style.animationName = 'slideOutLeft';
            
                setTimeout(() => {
                    ancestor.remove(); // Remove the corresponding list item after animation
                }, 300); // Duration of the animation    
            }

        });

        const validateEmail = (email) => {
            return String(email)
              .toLowerCase()
              .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
              );
        };
        
        // When clicked on submit button of contact page
        $(document).find('#contact-mail').on('click', function(event){
            // console.log("contact-mail IN");
            event.preventDefault();
            let valid=true;
            $('.invalid-input').css('display', 'none');
            //find('.invalid-input').css('display', 'block');

    
            const email = document.getElementById('email');
            if (email.value == '' || !validateEmail(email.value)) {
                // with jquery
                $("#email").parent().find('.invalid-input').css('display', 'block');

                valid = false;
            }

            const message = document.getElementById('contact-message');
            if (message.value == '') {
                $("#contact-message").parent().find('.invalid-input').css('display', 'block');
                valid = false;
            }
    
            if (valid == true) {
                let nonce = document.getElementById('pg_nonce').value;
                let admin_url = document.getElementById('pg_admin_ajax_url').value;

                const formData = new FormData();
                formData.append('action', 'contact_mail');
                formData.append('nonce', nonce);
                formData.append('email', email.value);
                formData.append('msg', message.value);
                jQuery.ajax({
                    method: 'POST',
                    url: admin_url,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(){
                        // console.log("contact-mail success");
                        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                        toastElList.map(function(toastEl) {
                            return new bootstrap.Toast(toastEl)
                        })
                        
                        var myToastEl = document.getElementById('contact-success')
                        var myToast = bootstrap.Toast.getInstance(myToastEl);
                        myToast.show();
                    }
                    // TODO handle error
                });
            }

        });

        // When user changes photo status
        $('#user_status').on('change', function(e) {
            this.value = this.checked ? "public" : "private";
            //console.log("on user_status change", e.target);
            
            if (this.checked) {
                document.getElementById('user_status_label').textContent = ays_vars.public_photo;
            }
            else {
                document.getElementById('user_status_label').textContent = ays_vars.private_photo;
            }
            e.stopPropagation();
        });

        // When clicked on submit button
        $(document).find('#btn-save-photo').on('click', function(event){
            // console.log("edit-photo IN");
            event.preventDefault();
            let error = false;
            let post_id = document.getElementById('post_id').value;
            let nonce = document.getElementById('pg_nonce').value;
            let admin_url = document.getElementById('pg_admin_ajax_url').value;

            const description = document.getElementById('photo-description').value;
            const user_status = document.getElementById('user_status').value;
            //const vignette = document.getElementById("select-country").value;

            const formData = new FormData();
            formData.append('action', 'user_save_photo');
            formData.append('nonce', nonce);
            formData.append('post_id', post_id);
            //formData.append('title', title);
            formData.append('desc', description);
            //formData.append('vignette', vignette);
            formData.append('user_status', user_status);
            jQuery.ajax({
                method: 'POST',
                url: admin_url,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response){
                    // come back to the previous page
                    // console.log("upload done");
                    var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                    toastElList.map(function(toastEl) {
                        return new bootstrap.Toast(toastEl)
                    })
                    
                    var myToastEl = document.getElementById('save-photo-success')
                    var myToast = bootstrap.Toast.getInstance(myToastEl);
                    myToast.show();                
                    
                }
                // TODO handle error
            });
        });

        function save_current_gallery() {

            let gallery_id = document.getElementById('gallery-id').value;
            let nonce = document.getElementById('pg_nonce').value;
            let admin_url = document.getElementById('pg_admin_ajax_url').value;

            const title = document.getElementById('gallery-title').value;
            const description = document.getElementById('gallery-description').value;

            // get the image list in the order of display
            let images_id = [];

            if (document.getElementById('item-list')){
            const list = document.getElementById('item-list').children;
            //console.log( "list", list);
                for(let i = 0 ; i < list.length; i++){
                    // Append them to a string
                    //console.log( "list i", list[i]);
                    images_id.push(list[i].dataset.id);
                }
            }

            const formData = new FormData();
            formData.append('action', 'user_edit_gallery');
            formData.append('nonce', nonce);
            formData.append('gallery_id', gallery_id);
            formData.append('title', title);
            formData.append('desc', description);
            formData.append('images_id', images_id);
            jQuery.ajax({
                method: 'POST',
                url: admin_url,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response){
                    // console.log("upload done");
                    var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                    toastElList.map(function(toastEl) {
                        return new bootstrap.Toast(toastEl)
                    })
                    
                    var myToastEl = document.getElementById('save-gallery-success')
                    var myToast = bootstrap.Toast.getInstance(myToastEl);
                    myToast.show();
                }
                // TODO handle error
            });

        }
        // When clicked on submit button of edit gallery page
        $(document).find('#edit-gallery-save').on('click', function(event){
            // console.log("edit-gallery-save IN");
            event.preventDefault();
            save_current_gallery();
        });

        $(document).find('#edit-gallery-save-2').on('click', function(event){
            // console.log("edit-gallery-save IN");
            event.preventDefault();
            save_current_gallery();
        });

        // from the Edit Gallery page
        $(document).find('#gallery_help').on('change', function(event) {
            let hide=false;
            if (event.target.checked) {
                //console.log('Checkbox is checked');
                hide = true;
            }

            let admin_url = document.getElementById('pg_admin_ajax_url').value;
            let nonce = document.getElementById('pg_nonce').value;

            const formData = new FormData();
            formData.append('action', 'hide_gallery_help');
            formData.append('nonce', nonce);
            formData.append('hide', hide);

            jQuery.ajax({
                method: 'POST',
                url: admin_url,
                data: formData,
                contentType: false,
                processData: false,
                success: function(){
                    console.log("hide_gallery_help success");
                }
            });       
        });
        
        // from the My Galleries page
        $(document).find('#galleries_help').on('change', function(event) {
            console.log('galleries_help IN');
            let hide=false;
            if (event.target.checked) {
                //console.log('Checkbox is checked');
                hide = true;
            }

            let admin_url = document.getElementById('pg_admin_ajax_url').value;
            let nonce = document.getElementById('pg_nonce').value;

            const formData = new FormData();
            formData.append('action', 'hide_galleries_help');
            formData.append('nonce', nonce);
            formData.append('hide', hide);

            jQuery.ajax({
                method: 'POST',
                url: admin_url,
                data: formData,
                contentType: false,
                processData: false,
                success: function(){
                    console.log("hide_galleries_help success");
                }
            });       
        });        

    })
})(jQuery)


/** dragable and sortable list
 * see https://www.codingayush.com/2023/05/sortable-list-using-html-css-javascript.html **/


const sortableList = document.querySelector(".sortable-list");
if (sortableList) {
    const items = sortableList.querySelectorAll(".item");
    // console.log("sortableList found");

    items.forEach(item => {
        item.addEventListener("dragstart", () => {
            //console.log("dragstart");
            // Adding dragging class to item after a delay
            setTimeout(() => item.classList.add("dragging"), 0);
        });
        // Removing dragging class from item on dragend event
        item.addEventListener("dragend", () => item.classList.remove("dragging"));
        //console.log("set dragging to ", item);
    });

    const initSortableList = (e) => {
        e.preventDefault();
        //console.log("initSortableList e", e);
        const draggingItem = document.querySelector(".dragging");
        //console.log("dragging is ", draggingItem);
        // Getting all items except currently dragging and making array of them
        let siblings = [...sortableList.querySelectorAll(".item:not(.dragging)")];
        //const list = document.getElementById('item-list')

        // Finding the sibling after which the dragging item should be placed
        let nextSibling = siblings.find(sibling => {
            /*console.log("initSortableList sibling", sibling);
            console.log("initSortableList found", {
                y: e.clientY,
                top: sibling.offsetTop,
                top2: list.offsetTop,
                
                height: sibling.offsetHeight,
                sum: sibling.offsetTop + sibling.offsetHeight / 2
            });*/
            return e.pageY <= sibling.offsetTop + sibling.offsetHeight / 2;
        });
        //console.log("initSortableList insert Before", nextSibling);
        // Inserting the dragging item before the found sibling
        sortableList.insertBefore(draggingItem, nextSibling);
    }

    sortableList.addEventListener("dragover", initSortableList);
    sortableList.addEventListener("dragenter", e => e.preventDefault());
}


