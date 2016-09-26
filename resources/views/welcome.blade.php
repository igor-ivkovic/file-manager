@extends('layouts.app')
@section('content')

        <!-- modal windows -->

<!-- add category -->
<div id="add_cat" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">ADD CATEGORY</h4>
            </div>
            <div class="modal-body">

                <form id="add_cat_form" action="{{ url('add_cat') }}" method="post">
                    <input type="hidden" id="add_cat_token" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" id="add_cat_parent" name="parent_id" value="0">
                    <div class="form-group">
                        <label for="category_name">Category Name:</label>
                        <input type="text" name="category_name" class="form-control" id="category_name">
                    </div>
                    <button type="submit" id="submit_add_cat" class="btn btn-default">Submit</button>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div><!-- end add category -->

<!-- add file -->
<div id="add_file" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">ADD NEW FILE</h4>
            </div>

            <div class="modal-body">
                <form id="add_file_form" action="{{ url('add_file') }}" enctype="multipart/form-data" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" id="file_parent_cat" name="parent_id" value="0">
                    <div class="form-group">
                        <label for="file">Choose File:</label>
                        <input type="file" name="file" class="form-control" id="file">
                    </div>
                    <button type="submit" id="submit_add_file" class="btn btn-default">Submit</button>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div><!-- end add file -->

<!-- edit -->
<div id="edit" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">EDIT</h4>
            </div>
            <div class="modal-body">
                <form id="edit_cat" action="{{ url('edit_cat') }}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" id="edit_cat_id" name="cat_id" value="0">
                    <div class="form-group">
                        <label for="new_category_name">Rename Category:</label>
                        <input type="text" name="new_category_name" class="form-control" id="new_category_name" placeholder="Type A New Category Name">
                    </div>
                    <button type="submit" id="submit_edit_cat" class="btn btn-default">Submit</button>
                </form>
                <br>
                <form id="delete_cat" action="{{ url('delete_cat') }}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" id="delete_cat_id" name="delete_cat_id" value="0">
                    <button type="button" class="btn btn-lg btn-danger" data-toggle="popover" title="Are you sure?" data-content=" <a href='#yes' id='deleting_cat'>&nbsp Yes &nbsp</a> <strong>vs.</strong><a href='#no' id='toggle_popover'>&nbsp No &nbsp</a>">Delete Category</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div><!-- end edit -->



<!-- the end of modal windows -->


<!-- content -->
<div class="container">
    <div class="text-left">
        <input type="hidden" id="navigation" value="home">
        <!-- category panel -->
        <div class="cat-manager content">
            <div class="thirtytwo">
                <h3 id="current_cat_top"><i class="fa fa-cloud-download" id="green" aria-hidden="true"></i> <i class="fa fa-cloud-upload" id="green" aria-hidden="true"></i> CATEGORIES</h3>
            </div>

            <!-- icons -->
            <div class="text-right">
                <h3>
                    <a href="#modal" class="log" data-toggle="modal" data-target="#add_cat"><i class="fa fa-plus-square" aria-hidden="true"></i></a>
                    <a href="#modal" class="log" data-toggle="modal" data-target="#add_file"><i class="fa fa-file" aria-hidden="true"></i></a>
                    <a href="#modal" class="log" data-toggle="modal" data-target="#edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                </h3>
            </div><!-- end icons -->

            <hr>

            <!-- category list -->
            <div class="cat_list">

                <div><span id="0">&nbsp<i class="fa fa-home fa-2x" aria-hidden="true"></i></span></div>
                <ul id="0">
                    @foreach($categories as $category)

                        <li class="{{ $category->rights or '' }}" id="{{ $category->id or '' }}">
                            &nbsp @if($category->children > 0) <strong id="{{ $category->id or '' }}" class="plus"><i class="fa fa-plus-square-o" aria-hidden="true"></i></strong> @else <strong id="{{ $category->id or '' }}" class="square"><i class="fa fa-square-o" aria-hidden="true"></i></strong> @endif
                            &nbsp<span class="{{ $category->rights or '' }}" id="{{ $category->id or '' }}">{{ $category->category_name or "" }}</span>&nbsp
                        </li>
                    @endforeach
                </ul>

            </div><!-- end category list -->

        </div><!-- end category panel -->


        <!-- file panel -->
        <div class="cat-manager content">
            <div class="thirtytwo">
                <h3 id="current_cat_bottom">Files in the category:</h3>
            </div>
            <hr>
            <table id="file_list">

            </table>
        </div><!-- end file panel -->

    </div>
</div><!-- end content -->

@endsection

