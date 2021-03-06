<?php

namespace App\Http\Controllers;

use App\Comment_Post;
use App\Company;
use App\Job_Post;
use App\Normal_Post;
use App\Post;
use App\School;
use App\Student;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CommentController extends Controller
{
    function addComment(Request $request){
        $user_id = $request->User()->id;
        $post_id = $request->post_id;
        $content = $request->content;

        DB::beginTransaction();
        try {
            $comment = new Comment_Post;
            $comment->user_id = $user_id;
            $comment->post_id = $post_id;
            $comment->content = $content;
            $comment->save();
            
            $post = Post::find($post_id);
            
            $posts = null;
            if($post->type == "Normal") $posts = Normal_Post::where('post_id', $post_id)->first();
            else if($post->type == "Job") $posts = Job_Post::where('post_id', $post_id)->first();
            $user_post = $posts->user_id;
            $notice = new NoticeController();
            $notice->addNotice($user_post, $user_id, ' đã bình luận trong bài viết của bạn');
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Viết bình luận thành công'
            ]);
        } catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống. Vui lòng thử lại.'
            ]);
        }
    }
    function getComment(Request $request){
        $post_id = $request->post_id;
        $limit = $request->limit ?? 5;
        $offset = $request->offset ?? 0;
        $comment = Comment_Post::where('post_id', $post_id)->offset($offset)->limit($limit)->get();
        $cnt = 0;
        foreach($comment as $cmt){
            $id = $cmt->user_id;
            $user = User::find($id);
            $cmt->avatar = $user->avatar;
            $users = null;
            if($user->type == "Student") {
                $users = Student::where('user_id', $id)->first();
            }
            else if($user->type == "School") {
                $users = School::where('user_id', $id)->first();
            }
            else if($user->type == "Company") {
                $users = Company::where('user_id', $id)->first();
            }
            else {
                return response()->json(['success'=>false, 'message'=>'Lỗi hệ thống.']);
            }
            $cmt->name = $users->name;
            $cnt++;
        }
        return response()->json([
            'success' => true,
            'count' => $cnt,
            'comments' => $comment
        ]);
    }
}
