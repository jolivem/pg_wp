(function ($) {


    $(function () {
        
        var flag = true;
        var tapped = false;
        var touchStartCoords;


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

        $(document).on('touchstart', function (e){
            touchStartCoords = e.originalEvent.touches[0].clientY;
         });
        $(document).on("touchend" , ".lg-image",function(e){
            var touchEndCoords = e.originalEvent.changedTouches[0].clientY;
            if(touchStartCoords == touchEndCoords){
                if(!tapped){
                    tapped=setTimeout(function(){
                        if(flag){
                            $(document).find(".lg-icon,.lg-sub-html,.lg-toolbar").fadeOut("fast");
                            flag = false;
                        }
                        else{
                            $(document).find(".lg-icon,.lg-sub-html,.lg-toolbar").fadeOut().toggle("fast");
                            flag = true;
                        }
                        tapped = null;
                        
                    },200);  
                }
                else{
                    clearTimeout(tapped); 
                    tapped = null;
                }                    
                e.preventDefault();

            }
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

            // get the address selected 
            let name = "address" + postid;
            let address = document.querySelector('input[name="'+name+'"]:checked').value;
    
            const formData = new FormData();
            formData.append('nonce', nonce);
            formData.append('address', address);
            formData.append('pid', postid);
            if (e.target.classList.contains("fa-thumbs-up")) {
    
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
                // console.log("edit-photo-option postid", postid);
                const isNumber = (element) => element == postid;
                const index = images.findIndex(isNumber);
                if (index != -1) {
                    // console.log("edit-photo-option index", index);

                    if (e.target.classList.contains("fa-angle-double-right")) {
                        // find the following image
                        if (index < images.length -1) {
                            let next_id = images[index + 1];
                            // console.log("edit-photo-option next_id", next_id);
                            let edit_photo_url = document.getElementById('pg_edit_photo_url').value;
                            edit_photo_url += "?pid=";
                            edit_photo_url += next_id;
                            const gallery_id = document.getElementById('gallery-id').value;
                            edit_photo_url += "&gid=";
                            edit_photo_url += gallery_id;
                            // console.log("edit-photo-option edit_photo_url", edit_photo_url);
                            window.location = edit_photo_url;
                        }
                    }
                    else if (e.target.classList.contains("fa-angle-double-left")) {
                        // find the previous image
                        if (index > 0) {
                            let previous_id = images[index - 1];
                            // console.log("edit-photo-option previous_id", previous_id);
                            let edit_photo_url = document.getElementById('pg_edit_photo_url').value;
                            edit_photo_url += "?pid=";
                            edit_photo_url += previous_id;
                            const gallery_id = document.getElementById('gallery-id').value;
                            edit_photo_url += "&gid=";
                            edit_photo_url += gallery_id;
                            // console.log("edit-photo-option edit_photo_url", edit_photo_url);
                            window.location = edit_photo_url;
                        }
                    }
                    let cpt = index+1;
                    document.getElementById('cpt-photo').innerHTML = cpt + "/" + images.length;
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
        $(document).on('click', '.miniature1, .miniature2, .miniature3, .photo-text-container', function(e){
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
                    
                    let url = document.getElementById('pg_user_galleries_url').value;
                    window.location = url;
    
                    // remove the gallery with animation
                    // find the gallery elements
                }
                // TODO handle error
            });            
        });
        

        // edit gallery when click on gallery text container and miniatures
        $(document).on('click', '.miniature, .photo-text-container', function(e){
            //console.log("miniature1 click", e.target);
            const closest = e.target.closest(`[data-id]`);
            //console.log("miniature1 closest", closest);
            
            if (closest) {
                const postid = closest.dataset.id;
                //console.log("gallery-photo-option postid=", postid);
                let edit_photo_url = document.getElementById('pg_edit_photo_url').value;
                edit_photo_url += "?pid=";
                edit_photo_url += postid;
                const gallery_id = document.getElementById('gallery-id').value;
                edit_photo_url += "&gid=";
                edit_photo_url += gallery_id;
                window.location = edit_photo_url;                
                const galid = closest.dataset.galid;
                //console.log("user-gallery-option edit galid=", galid);
                let edit_gallery_url = document.getElementById('pg_edit_gallery_url').value;
                edit_gallery_url += "?gid=";
                edit_gallery_url += galid;
                window.location = edit_gallery_url;
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

        $(document).on('click',".lg-image",function(e){
            if(!tapped){
                tapped=setTimeout(function(){
                    if(flag){
                        $(document).find(".lg-icon,.lg-sub-html,.lg-toolbar").fadeOut("fast");
                        flag = false;
                    }
                    else{
                        $(document).find(".lg-icon,.lg-sub-html,.lg-toolbar").fadeOut().toggle("fast");
                        flag = true;
                    }
                    tapped = null;
                    
                },200);  
            }
            else{
                clearTimeout(tapped); 
                tapped = null;
            }                    
            e.preventDefault();
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
                document.getElementById('user_status_label').textContent = 'Affichage autorisé sur la galerie publique';
            }
            else {
                document.getElementById('user_status_label').textContent = 'Photo privée';
            }
            e.stopPropagation();
        });

        // When clicked on submit button
        $(document).find('#save-photo').on('click', function(event){
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
            formData.append('action', 'user_edit_photo');
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

    })
})(jQuery)

function ays_closestEdge(x,y,w,h) {
    let ays_topEdgeDist = ays_distMetric(x,y,w/2,0);
    let ays_bottomEdgeDist = ays_distMetric(x,y,w/2,h);
    let ays_leftEdgeDist = ays_distMetric(x,y,0,h/2);
    let ays_rightEdgeDist = ays_distMetric(x,y,w,h/2);
    let ays_min = Math.min(ays_topEdgeDist,ays_bottomEdgeDist,ays_leftEdgeDist,ays_rightEdgeDist);

    switch (ays_min) {
        case ays_leftEdgeDist:
            return 'left';
        case ays_rightEdgeDist:
            return 'right';
        case ays_topEdgeDist:
            return 'top';
        case ays_bottomEdgeDist:
            return 'bottom';
    }
}

function lazyload_single () {
    // console.log('lazyload_single in');
    
    var scrollTop = window.pageYOffset;
    // console.log('lazyload_single in1');
    var lazyloadImages = document.querySelectorAll('img.lazy');
    if(lazyloadImages.length != 0) { 
        let img = lazyloadImages[0];
        if (img.src == '') {
            let parent = img.parentNode;
            // console.log('lazyload_single offsetTop', parent.offsetTop);
            // console.log('lazyload_single innerHeight', window.innerHeight);
            // console.log('lazyload_single scrollTop', scrollTop);
            // console.log('lazyload_single sum', window.innerHeight + scrollTop);
            if(parent.offsetTop < (window.innerHeight + scrollTop)) {
                //img.classList.remove('lazy');
                img.classList.add('lazyloaded');
                img.src = img.dataset.src;
                // console.log('lazyload_single img', img);
            }
        }
    };
}

// for grid 
function lazyload_max () {
    // console.log('lazyload_max in');
    var lazyloadImages = document.querySelectorAll('img.lazy');    
    // console.log('lazyload_max lenght', lazyloadImages.length);
    
    var scrollTop = window.pageYOffset;
    // console.log('lazyload_max in1');
    lazyloadImages.forEach(function(img) {
        if (img.src == '') {
            let parent = img.parentNode;
            if(parent.offsetTop < (window.innerHeight + scrollTop)) {
                img.classList.add('lazyloaded');
                img.src = img.dataset.src;
                //
                // console.log('lazyload_max img', img);
            }
        }
    });
}


var geojson={};
//TODO do not load geojson if already loaded

async function ays_add_vignette_to_image( lmapId,jfile,lat,lon,zoom) {

    // console.log("ays_add_vignette_to_image IN lmapId=", lmapId);
    // get country
    //console.log("country", country);
    //let zoom = country.zoom;
    let file = ays_vars.base_url + "assets/geojson/" + jfile;

    //let select = document.getElementsByClassName("compat-field-vignette")[0];
    //console.log("select", select);
    //console.log("BABAauRHUM", parent);
    // select.appendChild(p);
    // var elemDiv = document.createElement('td');
    // elemDiv.id = lmapId;
    var elemDiv = document.getElementById(lmapId);
    //console.log("ays_add_vignette_to_image", elemDiv);
    
    var props = {
        attributionControl: false,
        zoomControl: false,
        doubleClickZoom: false, 
        closePopupOnClick: false, 
        dragging: false, 
        zoomSnap: false, 
        zoomDelta: false, 
        trackResize: false,
        touchZoom: false,
        scrollWheelZoom: false
    };

    var geostyle = {
        fillColor: 'lightgoldenrodyellow',
        //color: 'yellow',
        fillOpacity: 2,
        weight: 1
    }
    let myIconClass = L.Icon.extend({
        options: {
            iconSize:     [4, 4],
            iconAnchor:   [2, 2]
        }
    });
    
    // console.log("css:", css);
    //elemDiv.style.height = country.height;
    //elemDiv.style.width = country.width;
    //elemDiv.style.backgroundColor = 'white';
    elemDiv.style.background = 'transparent';
    // elemDiv.style.borderStyle = 'solid';
    // elemDiv.style.borderWidth = 'thin';
    // elemDiv.style.borderColor = 'lightgray';
    //select.appendChild(elemDiv);
    
    let mark = new myIconClass ({iconUrl: ays_vars.base_url + 'assets/markpoint.png'});
    
    let lmap = L.map(lmapId, props);
    //console.log("ays_add_vignette_to_image AA created lmap", {lmapId, lmap});
    //console.log("css:", css);
    // Charger le fichier GeoJSON et l'ajouter à la carte
    const response = await fetch(file);  // Use the 'await' keyword with 'fetch'

    //console.log("ays_add_vignette_to_image BB lmapId=", lmapId);
    const data = await response.json();

    L.geoJSON(data, {
        clickable: false,
        style: geostyle
    }).addTo(lmap);
    let clon = data.features[0].properties.geo_point_2d.lon;
    let clat = data.features[0].properties.geo_point_2d.lat;
    let center = [clat, clon];
    //console.log("coord:", coord);
    //console.log("lmap:", lmap);
    //console.log("ays_add_vignette_to_image DD setView", {lmapId, lmap});
    lmap.setView(center, zoom);
    let marker = [lat, lon];
    L.marker(marker, {icon: mark}).addTo(lmap);
 }


//Distance Formula
function ays_distMetric(x,y,x2,y2) {
    let ays_xDiff = x - x2;
    let ays_yDiff = y - y2;
    return (Math.abs(ays_xDiff) * Math.abs(ays_yDiff))/2;
}

function ays_getDirectionKey(ev, obj) {
    let ays_w = obj.offsetWidth,
        ays_h = obj.offsetHeight,
        ays_x = (ev.pageX - obj.offsetLeft - (ays_w / 2) * (ays_w > ays_h ? (ays_h / ays_w) : 1)),
        ays_y = (ev.pageY - obj.offsetTop - (ays_h / 2) * (ays_h > ays_w ? (ays_w / ays_h) : 1)),
        ays_d = Math.round( Math.atan2(ays_y, ays_x) / 1.57079633 + 5 ) % 4;
    return ays_d;
}


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


