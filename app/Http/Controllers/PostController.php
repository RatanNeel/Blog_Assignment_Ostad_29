<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::query()->with('user', 'tags')->latest();

        //-- Search Login -- 
        if ( $search = $request->input('search') ) {
            $query->where( function ($q) use ($search) {
                $q->where('title','like', "%{$search}%")
                    ->orWhere('content','like', "%{$search}%")
                    //optionally search by username
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
                
            });
        }


        //-- Visibility Filter --- 
        if ( !Auth::check() ) {
            //Guests only see published posts
            $query->where('visibility', 'published');
        } else {
            //Logged-in users see published posts and their own non-published posts
            $query->where(function ($q) {
                $q->where('visibility', 'published')
                    ->orWhere('user_id', Auth::id());
            });
        }

        $posts = $query->paginate(10)->withQueryString(); //Keep the search query in pagination

        //pass data to the Inertia Component
        return inertia('Posts/Index', [// Adjust component path as needed
            'posts' => $posts,
            'filters' => $request->only(['search']), // Pass filters back to the view
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //Optionally pass tags or other data needed for the form
        return  Inertia::render('Posts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
