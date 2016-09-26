<?php

namespace App\Http\Controllers;

use App\Category;
use App\File as FileModel;
use Auth;
use Illuminate\Http\Request;
use File;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::where('position', 1)->orderBy('category_name', 'ASC')->get();
        return view('admin', ['categories' => $categories]);
    }

    public function home () {
        if (Auth::guest()) {
            return redirect('login');
        }
        else {
            $categories = Category::where('position', 1)->orderBy('category_name', 'ASC')->get();
            return view('welcome', ['categories' => $categories]);
        }
    }

    // adding new category
    public function add_cat(Request $request)
    {
        $parent_id = $request->parent_id;
        $category_name = $request->category_name;
        $position = null;
        $url = null;
        $rights = null;
        $user = Auth::user()->name;

        // check for duplicate
        if (Category::where('parent_id', $parent_id)->where('category_name', $category_name)->first()) {
            return response()->json(["duplicate" => 'yep']);
        }
        else {

            if ($parent_id === '0') {
                $position = 1;
                $url = $category_name . '/';
                $rights = 'full';
            }
            else {
                $par_cat = Category::where('id', $parent_id)->first();
                $position = $par_cat->position + 1;
                $url = $par_cat->url . $category_name .'/';
                $rights = $par_cat->rights;
                if ($user == 'admin' || $rights == 'full' || $rights == 'upload') {
                // children count
                $par_cat->children = $par_cat->children + 1;
                $par_cat->save();
                } //end if
            } // end if

            $path = 'Categories/'. $url;
            $path = storage_path($path);

            if ($user == 'admin' || $rights == 'full' || $rights == 'upload') {
                File::makeDirectory($path, 0777, true, true);

                $added_cat = Category::create(['category_name' => $category_name, 'parent_id' => $parent_id, 'position' => $position, 'url' => $url, 'rights' => $rights]);
                return response()->json(["added_cat" => $added_cat, 'duplicate' => 'nope']);
            }
            else {
                return response()->json(["duplicate" => 'yep']);
            } // end if

        }   // end check for duplicate

    } // end adding new category


    // get child categories
    public function get_children (Request $request)
    {
        $parent_id = $request->parent_id;

        $children = Category::where('parent_id', $parent_id)->orderBy('category_name', 'ASC')->get();

        return response()->json(['children' => $children, 'parent_id' => $parent_id]);
    } // end get child categories

    // adding new file
    public function add_file (Request $request)
    {
        $parent_id = $request->parent_id;
        $version = 1;
        $user = Auth::user()->name;
        $parent = Category::where('id', $parent_id)->first();
        $category_id = $parent_id;
        $rights = $parent->rights;

        // checks for upload rights
        if ($user == 'admin' || $rights == 'full' || $rights == 'upload') {

            // making sure that file don't gets to root category
            if ($parent_id != 0) {
                // check if file was attached
                if ($request->hasFile('file')) {

                    $file = $request->file('file');
                    $file_name = $file->getClientOriginalName();
                    $display_name = $file->getClientOriginalName();

                    $version_check = FileModel::where('filename', $file_name)->orderBy('version', 'DESC')->first();

                    if ($version_check) {
                        $version = $version_check->version + 1;
                        $display_name =  'v(' . $version . ')_' .$display_name;
                    }


                    $can_download = '';

                    // checks download rights
                    if ($user == 'admin' || $rights == 'download' || $rights == 'full') {
                        $can_download = 'yes';
                    }
                    else {
                        $can_download = 'no';
                    } // end checks download rights

                    // adds file
                    $parent_url = 'Categories/' . $parent->url;
                    $parent_url = storage_path($parent_url);
                    $file->move($parent_url, $display_name);
                    $file_url = $parent_url . $display_name;
                    $new_file = FileModel::create(['filename' => $file_name, 'version' => $version, 'user' => $user, 'url' => $file_url, 'category_id' => $category_id, 'display_name' => $display_name]);
                    // end adds file


                    return response()->json(['new_file' => $new_file, 'can_download' => $can_download, 'can_upload' => 'yes']);

                } // end check if file was attached
            } // end making sure that file don't gets to root category

        }
        else {
            return response()->json(['can_upload' => 'no']);
        } // end checks for upload rights

    } // end adding new file

    // cheks files
    public function check_files (Request $request)
    {
        $user = Auth::user()->name;
        $parent_id = $request->parent_id;

        $rights = Category::where('id', $parent_id)->value('rights');
        $can_download = '';

        // checks download rights
        if ($user == 'admin' || $rights == 'download' || $rights == 'full') {
            $can_download = 'yes';
        }
        else {
            $can_download = 'no';
        } // end checks download rights


        $files = FileModel::where('category_id', $parent_id)->orderBy('filename', 'ASC')->get();

        return response()->json(['files' => $files, 'can_download' => $can_download]);


    } // end cheks files

    // download
    public function download ($file_id)
    {
        // $file_id = $request->file_id;
        $user = Auth::user()->name;
        $file = FileModel::where('id', $file_id)->first();

        if ($user == 'admin') {
            return response()->download($file->url);
        }
        else {
            $cat = Category::where('id', $file->category_id)->first();
            if ($cat->rights == 'none' || $cat->rights == 'upload') {
                return back();
            }
            else {

                $file_url = $file->url;
                $file_url = str_replace('\\', '/', $file_url);

                return response()->download($file_url);



            }
        }

    } // end download

    // edit category
    public function edit_cat (Request $request)
    {
        $category_id = $request->category_id;
        $category_name = $request->category_name;
        $user = Auth::user()->name;

        $rename = Category::where('id', $category_id)->first();
        $rights = $rename->rights;

        // checks if someone is trying to change the root folder
        if ($category_id != 0) {
            // checks rights
            if ($user == 'admin' || $rights == 'upload' || $rights == 'full') {

                $rename->category_name = $category_name;
                $rename->save();

                return response()->json(['category_name' => $category_name, 'category_id' => $category_id, 'can_edit' => 'yep', 'rights' => $rights]);
            }
            else {
                return response()->json(['can_edit' => 'nope']);
            } // end checks rights
        }
        else {
            return response()->json(['can_edit' => 'nope']);
        } // end checks if someone is trying to change the root folder


    }

    public function delete_cat (Request $request)
    {
        $category_id = $request->category_id;
        $user = Auth::user()->name;
        $category = Category::where('id', $category_id)->first();
        $parent_id = $category->parent_id;
        $rights = $category->rights;
        $url = $category->url;
        // checks if someone is trying to change the root folder
        if ($category_id != 0) {
            // checks rights
            if ($user == 'admin' || $rights == 'upload' || $rights == 'full') {

                $path = 'Categories/'. $url;
                $path = storage_path($path);
                Category::where('url', 'like', $url.'%')->delete();
                FileModel::where('url', 'like', '%Categories/'. $url.'%')->delete();
                File::deleteDirectory($path);

                $children_count = Category::where('id', $parent_id)->first();
                $children_count->children = $children_count->children - 1;
                $children_count->save();

                return response()->json(['category_id' => $category_id, 'can_delete' => 'yep', 'rights' => $rights]);
            }
            else {
                return response()->json(['can_delete' => 'nope']);
            } // end checks rights
        }
        else {
            return response()->json(['can_delete' => 'nope']);
        } // end checks if someone is trying to change the root folder
    }

    public function set_rights (Request $request)
    {
        $category_id = $request->category_id;
        $new_right = $request->new_right;
        $sub_cat = $request->sub_cat;
        $user = Auth::user()->name;
        $category = Category::where('id', $category_id)->first();
        $url = $category->url;
        $children = $category->children;
        $category_name = $category->category_name;

        // checks if someone is trying to change the root folder
        if ($category_id != 0) {
            // checks rights
            if ($user == 'admin') {

                $category->rights = $new_right;
                $category->save();

                if ($sub_cat == 'yep'){
                    Category::where('url', 'like', $url.'%')->update(['rights' => $new_right]);
                }

                return response()->json(['category_id' => $category_id, 'can_set_rights' => 'yep', 'rights' => $new_right, 'category_name' => $category_name, 'children' => $children, 'sub_cat' => $sub_cat]);
            }
            else {
                return response()->json(['can_set_rights' => 'nope']);
            } // end checks rights
        }
        else {
            return response()->json(['can_set_rights' => 'nope']);
        } // end checks if someone is trying to change the root folder

    }

}
