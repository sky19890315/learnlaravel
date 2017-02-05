<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *显示文章列表
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Cache::get('posts' ,[]);
        if (!$posts)
            exit('404!Noting');
        $html = '<ul>';

        foreach ($posts as $key=>$post){
        $html .= '<li><a href='.route('post.show' ,['post'=>$key]).'>'.$post['title'].'</a></li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Show the form for creating a new resource.
     *创建新文章表单页面
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $postUrl = route('post.store');
        $csrf_field = csrf_field();
        $html =<<<CREATE
        <form action="$postUrl" method="post">
        $csrf_field
        <input type="text" name="title"/><br/><br/>
        <textarea name="content" cols="50" rows="5" placehoder="来说点新鲜事儿吧..."></textarea><br/><br/>
        <input type="submit" value="提交"/>
        </form>
CREATE;
        return $html;
    }

    /**
     * Store a newly created resource in storage.
     *将新创建的文章储存到存储器
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $title = $request->input('title');
        $content = $request->input('content');
        $post = ['title'=>trim($title) , 'content'=>trim($content)];
        $posts = Cache::get('posts' , []);

        if (!Cache::get('post_id')){
            Cache::add('post_id' ,1, 60);
        }else{
            Cache::increment('post_id' ,1);
        }
        $posts[Cache::get('post_id')] = $post;
        Cache::put('posts' ,$posts ,60);
        return redirect()->route('post.show' , ['post'=>Cache::get('post_id')]);
    }

    /**
     * Display the specified resource.
     *显示指定的文章
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $posts = Cache::get('posts' , []);
        if (!$posts || !$posts[$id])
            exit('404! Nothing Found! ');
        $post = $posts[$id];

        $editUrl = route('post.edit' ,['post' => $id]);
        $html = <<<DETAIL
        <h3>{$post['title']}</h3>
        <p>{$post['content']}</p>
        <p>
            <a href="{$editUrl}">编辑</a>
        </p>
DETAIL;

    return $html;
    }

    /**
     * Show the form for editing the specified resource.
     *编辑文章
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $posts = Cache::get('posts' ,[]);
        if (!$posts || !$posts[$id])
            exit('404! Noting Found! ');
        $post = $posts[$id];

        $postUrl = route('post.update' , ['post' => $id]);
        $csrf_field = csrf_field();

        $html = <<<UPDATE
        <form action="$postUrl" method="post">
        $csrf_field
        <input type="hidden" name="_method" value="PUT"/>
        <input type="text" name="title" value="{$post['title']}"/><br/><br/>
        <textarea name="content" cols="50" row="5">{$post['content']}
        </textarea><br/><br/>
        <input type="submit" value="提交"/>
        </form>

UPDATE;
        return $html;
    }

    /**
     * Update the specified resource in storage.
     *在存储器中更新文章
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $posts = Cache::get('posts' ,[]);
        if (!$posts || !$posts[$id])
            exit('404! Noting Found! ');


        $title = $request->input('title');
        $content = $request->input('content');

        $posts[$id]['title'] = trim($title);
        $posts[$id]['content'] = trim($content);

        Cache::put('posts' , $posts ,60);
        return redirect()->route('post.show' ,['post' => Cache::get('post_id')]);
    }

    /**
     * Remove the specified resource from storage.
     *在存储器中移除文章
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $posts = Cache::get('posts' ,[]);
        if (!$posts || !$posts[$id])
            exit('404! Noting Found! ');


        unset($posts[$id]);
        Cache::decrement('post_id' , 1);

        return redirect()->route('post.index');

    }
}
