<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index() {
        $posts = Post::latest()->paginate(5);

        return new PostResource(true, 'List Data posts', $posts);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        // check if validation fails
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // create post
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return new PostResource(true, 'Post created successfully', $post);
    }

    public function show($id) {
        $post = Post::find($id);

        return new PostResource(true, 'Post found', $post);
    }

    public function update(Request $request,$id) {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        // check if validation fails
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $post = Post::find($id);

        if(!$post) {
            return new PostResource(false, 'Post not found', null);
        }

        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/'.basename($post->image));

            //update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content,
            ]);

        } else {

            //update post without image
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        //return response
        return new PostResource(true, 'Update Post successfully!', $post);
    }

    public function destroy($id) {
        $post = Post::find($id);

        if(!$post) {
            return new PostResource(false, 'Post not found', null);
        }

        //delete image
        Storage::delete('public/posts/'.basename($post->image));

        //delete post
        $post->delete();

        return new PostResource(true, 'Delete Post successfully!', null);
    }
}
