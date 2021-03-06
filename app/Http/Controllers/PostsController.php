<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Post;
use DB;

class PostsController extends Controller
{
       /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
        $this->middleware('auth',['except'=>['index','show']]);
    }
 /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//      return Post::all();
//      $posts = Post::all();
//      $posts = Post::orderBy('title','desc')->get();
//      $posts = Post::orderBy('title','desc')->take(1)->get();
//      $posts = Post::where('title','Post One')->get();
//      $post = DB::select('SELECT * from posts');        problem
        $posts = Post::orderBy('created_at','asc')->paginate(20);
        return view('posts.index')->with('posts',$posts);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this -> validate($request, [
            'title' => 'required',
            'description' => 'required',
            'cover_image' => 'image|nullable|max:1999'
    ]);
    //Handle File Uplaod
    if($request->hasFile('cover_image'))
    {
        //Get filename with extension
        $filenameWithExt = $request->file('cover_image') -> getClientOriginalName();
        //Get just file name
        $filename = pathinfo($filenameWithExt,PATHINFO_FILENAME);
        //Get just file Ext
        $extension = $request -> file('cover_image')->getClientOriginalExtension();
        //File Name to store
        $filenameToStore = $filename.'_'.time().'.'.$extension;
        //Upload Image
        $path = $request->file('cover_image')->storeAs('public/cover_images',$filenameToStore);
    }
    else
    {
        $filenameToStore = 'noimage.jpg';
    }
            //Create Post
        $post = new Post;
        $post -> title = $request -> input('title');
        $post -> description = $request -> input('description');
        $post -> user_id = auth() -> user()->id;
        $post -> cover_image = $filenameToStore;
        $post -> save();

        return redirect('/posts')->with('success','Post Created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
//        return Post::find($id);
        $post = Post::find($id);
        return view('posts.show')->with('posts',$post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);
        //check for correct user
        if(auth()->user()->id !== $post->user_id)
        {
            return redirect('/posts') -> with('error','Unauthorized Page');
        }
        return view('posts.edit')->with('post',$post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this -> validate($request, [
            'title' => 'required',
            'description' => 'required'
    ]);
            //Create Post
        $post = Post::find($id);
        $post -> title = $request -> input('title');
        $post -> description = $request -> input('description');
        $post -> save();

        return redirect('/posts')->with('success','Post Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        //check for correct user
        if(auth()->user()->id !== $post->user_id)
        {
            return redirect('/posts') -> with('error','Unauthorized Page');
        }
        if($post -> cover_images != 'noimage.jpg')
        {
            //Delete image
            Storage::delete('public/cover_images/'.$post->cover_image);

        }
        $post -> delete();
        return redirect('/posts')->with('success','Post Removed');
    }
}
