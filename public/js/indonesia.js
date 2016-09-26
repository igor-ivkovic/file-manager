$(document).ready(function () {


    // category selection
    $(document).on('click', 'span', function () {

        var parent_id = $(this).attr('id');
        $('#add_cat_parent').val( parent_id );
        $('#file_parent_cat').val( parent_id );
        $('#edit_cat_id').val(parent_id);
        $('#delete_cat_id').val(parent_id);
        $('#set_rights_id').val(parent_id);
        var category_name = $(this).text();
        var rights = $(this).attr('class');
        var download = null;
        var upload = null;

        // choosing colors to show category rights
        switch (rights) {
            case 'download':
                download = 'green';
                upload = 'red';
                break;
            case 'upload':
                download = 'red';
                upload = 'green';
                break;
            case 'full':
                download = 'green';
                upload = 'green';
                break;
            case 'none':
                download = 'red';
                upload = 'red';
                break;
            default:
                download = 'red';
                upload = 'red';
                break;
        } // end choosing colors to show category rights


        //changing background for category and showing rights
        $('ul#0 li').css("background-color", "white");
        $('span#0').parent().css("background-color", "white");
        $(this).parent().css("background-color", "lightblue");

        if (parent_id == 0) {
            $('#current_cat_top').html('<i class="fa fa-cloud-download" id="green" aria-hidden="true"></i> <i class="fa fa-cloud-upload" id="green" aria-hidden="true"></i> CATEGORIES');
            $('#current_cat_bottom').html('Files in the category:');
        }
        else {
            $('#current_cat_top').html('<i class="fa fa-cloud-download" id="'+ download +'" aria-hidden="true"></i> <i class="fa fa-cloud-upload" id="'+ upload +'" aria-hidden="true"></i> '+ category_name +'');
            $('#current_cat_bottom').html('Files in the "'+ category_name +'" category:');
        } // end changing background for category and showing rights

        var url = $('a.navbar-brand').attr('href');

        // checks files for category
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "post",
            url: url + '/check_files',
            data: {parent_id: parent_id},
            success: function(data) {
                var files = data['files'];
                var can_download = data['can_download'];
                var i;
                var url = $('a.navbar-brand').attr('href');

                $('#file_list').empty();

                if (can_download == 'yes') {
                    for (i = 0; i < files.length; ++i) {
                        $('#file_list').prepend('<tr><td class="download" id="'+ files[i]['id'] +'"><a href="'+ url +'/download/'+ files[i]['id'] +'"> &nbsp'+ files[i]['filename'] +'</a></td><td>&nbsp&nbsp Version:&nbsp'+ files[i]['version'] +'.0</td></tr>');

                    }
                }
                else {
                    for (i = 0; i < files.length; ++i) {
                        $('#file_list').prepend('<tr><td class="download" id="'+ files[i]['id'] +'"><strong> &nbsp'+ files[i]['filename'] +'</strong></td><td>&nbsp&nbsp Version:&nbsp'+ files[i]['version'] +'.0</td></tr>');

                    }
                }



            }
        }); // end checks files for category

    }); // end category selection


    // getting child categories
    $(document).on('click', 'strong.plus', function () {

        var parent_id = $(this).attr('id');
        var url = $('a.navbar-brand').attr('href');

        $(this).attr('class', 'minus');
        $(this).children().attr('class', 'fa fa-minus-square-o');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "post",
            url: url + '/get_children',
            data: {parent_id: parent_id},
            success: function(data) {

                var categories = data['children'];
                var parent_id = data['parent_id'];
                var i;
                var all = '';

                for (i = 0; i < categories.length; ++i) {

                    var icon;

                    if(categories[i]['children'] > 0) {
                        icon = '<strong id="'+ categories[i]['id'] +'" class="plus"><i class="fa fa-plus-square-o" aria-hidden="true"></i></strong>';
                    }
                    else {
                        icon = '<strong id="'+ categories[i]['id'] +'" class="square"><i class="fa fa-square-o" aria-hidden="true"></i></strong>';
                    }

                    all += '<li class="'+ categories[i]['rights'] +'" id="'+ categories[i]['id'] +'">&nbsp'+ icon +'&nbsp<span class="'+ categories[i]['rights'] +'" id="'+ categories[i]['id'] +'">'+ categories[i]['category_name'] +'</span>&nbsp</li>';
                }

                $('li#'+parent_id).append(
                    '<ul class="padding" id="'+ parent_id +'">' +
                    all +
                    '</ul>');

            }
        });
    }); // end getting child categories


    // don't select text
    $(document).on('mousedown', 'li', function () {
        return false;
    });  // end don't select text

    // close tree structure
    $(document).on('click', 'strong.minus', function () {
        $(this).parent().children('ul').remove();
        $(this).attr('class', 'plus');
        $(this).children().attr('class', 'fa fa-plus-square-o');
    });  // end close tree structure


    // add category
    $(document).on('click', '#submit_add_cat', function (e) {
        e.preventDefault();
        var category_name = $('input[name="category_name"]').val();
        var url = $('form#add_cat_form').attr('action');
        var parent_id = $('input#add_cat_parent').val();


        $('#add_cat').modal('hide');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "post",
            url: url,
            data: {category_name: category_name, parent_id: parent_id},
            success: function(data) {

                console.log('siker');
                var duplicate = data['duplicate'];
                // check for duplicate category
                if (duplicate != 'yep') {

                    var category_name = data['added_cat']['category_name'];
                    var category_id = data['added_cat']['id'];
                    var parent_id = data['added_cat']['parent_id'];
                    var rights = data['added_cat']['rights'];
                    console.log(parent_id);

                    $('input[name="category_name"]').val('');
                    var test = $('li#'+parent_id).children('strong').attr('class');
                    console.log(test);

                    if (parent_id == 0) {

                        if (test == 'square') {
                            $('li#'+parent_id).children('strong').attr('class', 'plus');
                            $('li#'+parent_id).children('strong').children().attr('class', 'fa fa-plus-square-o');
                        }
                        else {
                            $('.cat_list ul').prepend('<li class="'+ rights +'" id="' + category_id + '">&nbsp&nbsp<strong id="'+ category_id +'" class="square"><i class="fa fa-square-o" aria-hidden="true"></i></strong>&nbsp<span class="'+ rights +'" id="'+ category_id +'">&nbsp' + category_name + '&nbsp</span></li>');
                        } // end if
                    }
                    else {
                        if (test == 'square') {
                            $('li#'+parent_id).children('strong').attr('class', 'plus');
                            $('li#'+parent_id).children('strong').children().attr('class', 'fa fa-plus-square-o');
                        }
                        else {
                            $('li#'+parent_id).children('ul').prepend('<li class="'+ rights +'" id="' + category_id + '">&nbsp<strong id="'+ category_id +'" class="square"><i class="fa fa-square-o" aria-hidden="true"></i></strong>&nbsp<span class="'+ rights +'" id="'+ category_id +'">' + category_name + '&nbsp</span></li>');
                            console.log('parent-not-0');
                        } //end if

                    } //end if
                } // end check for duplicate category

            } //end success
        }); // end ajax

    });  // end add category


    // file upload
    $(document).on('click', '#submit_add_file', function (e) {
        e.preventDefault();
        var file = new FormData(document.querySelector("#add_file_form"));
        var url = $('form#add_file_form').attr('action');


        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: "POST",
            data: file,
            processData: false,  // tell jQuery not to process the data
            contentType: false, // tell jQuery not to set contentType
            success: function(data) {
                var can_upload = data['can_upload'];
                // cheks upload rights
                if (can_upload == 'yes') {
                    var new_file = data['new_file'];
                    var can_download = data['can_download'];
                    var url = $('a.navbar-brand').attr('href');


                    // checks download rights
                    if (can_download == 'yes') {
                        $('#file_list').prepend('<tr><td class="download" id="'+ new_file['id'] +'"><a href="'+ url +'/download/'+ new_file['id'] +'"> &nbsp'+ new_file['filename'] +'</a></td><td>&nbsp&nbsp Version:&nbsp'+ new_file['version'] +'.0</td></tr>');
                    }
                    else {
                        $('#file_list').prepend('<tr><td class="download" id="'+ new_file['id'] +'"><strong> &nbsp'+ new_file['filename'] +'</strong></td><td>&nbsp&nbsp Version:&nbsp'+ new_file['version'] +'.0</td></tr>');
                    } // end checks download rights

                } // end cheks upload rights

                $('#add_file').modal('hide');

            } // end success
        }); // end ajax

    });  // end file upload

    // init popover
    $('[data-toggle="popover"]').popover({
        html: true,
        content: function() {
            return $('.popover-content');
        }
    });

    $(document).on('click', '#toggle_popover', function () {
        $('[data-toggle="popover"]').popover('hide');
    });

    // edit category name
    $(document).on('click', '#submit_edit_cat', function (e) {
        e.preventDefault();
        var url = $('form#edit_cat').attr('action');
        var category_id = $('input#edit_cat_id').val();
        var category_name = $('input#new_category_name').val();

        $('#edit').modal('hide'); // close modal window

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: "POST",
            data: {category_id: category_id, category_name: category_name},
            success: function(data) {
                var can_edit = data['can_edit'];
                // checks for edit rights
                if (can_edit == 'yep') {
                    var rights = data['rights'];
                    var category_name = data['category_name'];
                    var category_id = data['category_id'];

                    $('input#new_category_name').val('');

                    var download = null;
                    var upload = null;

                    // choosing colors to show category rights
                    switch (rights) {
                        case 'download':
                            download = 'green';
                            upload = 'red';
                            break;
                        case 'upload':
                            download = 'red';
                            upload = 'green';
                            break;
                        case 'full':
                            download = 'green';
                            upload = 'green';
                            break;
                        case 'none':
                            download = 'red';
                            upload = 'red';
                            break;
                        default:
                            download = 'red';
                            upload = 'red';
                            break;
                    } // end choosing colors to show category rights

                    $('#current_cat_top').html('<i class="fa fa-cloud-download" id="'+ download +'" aria-hidden="true"></i> <i class="fa fa-cloud-upload" id="'+ upload +'" aria-hidden="true"></i> '+ category_name +'');
                    $('#current_cat_bottom').html('Files in the "'+ category_name +'" category:');

                    $('li#'+category_id).children('span').html(category_name);





                } // end checks for edit rights


            } // end success
        }); // end ajax

    }); // end edit category name


    // delete category
    $(document).on('click', '#deleting_cat', function (e) {
        e.preventDefault();
        var url = $('form#delete_cat').attr('action');
        var category_id = $('input#delete_cat_id').val();

        $('[data-toggle="popover"]').popover('hide'); // hides popover


        $('#edit').modal('hide'); // close modal window

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: "POST",
            data: {category_id: category_id},
            success: function(data) {
                var can_delete = data['can_delete'];
                // check rights
                if (can_delete == 'yep') {
                    var category_id = data['category_id'];
                    $('li#'+category_id).remove();

                    var parent_id = 0;
                    $('#add_cat_parent').val( parent_id );
                    $('#file_parent_cat').val( parent_id );
                    $('#edit_cat_id').val( parent_id );
                    $('#delete_cat_id').val( parent_id );
                    $('#set_rights_id').val( parent_id );

                    $('#current_cat_top').html('<i class="fa fa-cloud-download" id="green" aria-hidden="true"></i> <i class="fa fa-cloud-upload" id="green" aria-hidden="true"></i> CATEGORIES');
                    $('#current_cat_bottom').html('Files in the category:');

                } // end check rights

            } // end success
        }); // end ajax

    }); // end delete category


    // set rights
    $(document).on('click', '#submit_set_rights', function (e) {
        e.preventDefault();
        var url = $('form#set_rights_form').attr('action');
        var category_id = $('input#set_rights_id').val();
        console.log(category_id);
        var new_right = $('#sel1').val();
        var sub_cat = '';

        if(document.getElementById('sub_cat').checked) {
            sub_cat = 'yep';
        } else {
            sub_cat = 'nope';
        }


        $('#set_rights').modal('hide'); // close modal window

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: "POST",
            data: {category_id: category_id, new_right: new_right, sub_cat: sub_cat},
            success: function(data) {
                var can_set_rights = data['can_set_rights'];
                // check rights
                if (can_set_rights == 'yep') {
                    var category_name = data['category_name'];
                    var category_id = data['category_id'];
                    var rights = data['rights'];
                    var children = data['children'];
                    var sub_cat = data['sub_cat'];

                    $('li#'+category_id).children('span').attr('class', rights);

                    var check = $('li#'+category_id).children('strong').attr('class');

                    if (check == 'minus') {
                        if (sub_cat == 'yep'){
                            $('li#'+category_id).children('ul').remove();
                            $('li#'+category_id).children('strong').attr('class', 'plus');
                            $('li#'+category_id).children().children().attr('class', 'fa fa-plus-square-o');
                        }
                    }

                    var download = null;
                    var upload = null;

                    // choosing colors to show category rights
                    switch (rights) {
                        case 'download':
                            download = 'green';
                            upload = 'red';
                            break;
                        case 'upload':
                            download = 'red';
                            upload = 'green';
                            break;
                        case 'full':
                            download = 'green';
                            upload = 'green';
                            break;
                        case 'none':
                            download = 'red';
                            upload = 'red';
                            break;
                        default:
                            download = 'red';
                            upload = 'red';
                            break;
                    } // end choosing colors to show category rights

                    $('#current_cat_top').html('<i class="fa fa-cloud-download" id="'+ download +'" aria-hidden="true"></i> <i class="fa fa-cloud-upload" id="'+ upload +'" aria-hidden="true"></i> '+ category_name +'');


                } // end check rights

            } // end success
        }); // end ajax

    }); // end set rights


    //navigation
    var page = $('#navigation').val();
    switch (page) {
        case 'home':
            $('li#home').toggleClass('active');
            break;
        case 'admin':
            $('li#admin').toggleClass('active');
            break;
        default:
            break;

    }


});
