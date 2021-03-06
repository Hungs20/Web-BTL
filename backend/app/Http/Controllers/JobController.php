<?php

namespace App\Http\Controllers;

use App\Company;
use App\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Job;
use App\Job_Availabilty;
use App\Job_Experience;
use App\Job_Type;
use App\Program_Language_Job;
use App\School;
use App\Program_Language;
use App\User;

class JobController extends Controller
{
    // add and update
    function addOrUpdate(Request $request){
        $id = $request->User()->id;
        $type = $request->User()->type;
        $name = $request->name;
        if($type == "Student"){
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền ở đây'
            ]);
        }
        if($request->job_id) $job_id = $request->job_id;
        else $job_id = null;
        $job = null;
        if($job_id){
            $job = Job::find($job_id);
        } else {
            $job = Job::where('user_id', $id)->where('name', $name)->first();
            if($job) return response()->json(['success' => false, 'message' => 'Bạn đã tạo job này rồi']);
        }
        DB::beginTransaction();
        try{
            if(!$job) $job = new Job;
            $job->user_id = $id;
            $job->name = $name;
            $job->description = $request->description;
            $job->pay_rate = $request->pay_rate;
            $job->type_id = $request->type_id;
            $job->experience_id = $request->experience_id;
            $job->country_id = $request->country_id;
            $job->availabilty_id = $request->availabilty_id;
            $job->save();

            //add language
            $program_language_id = $request->program_language;
            Program_Language_Job::where('job_id', $job->id)->delete();
            foreach($program_language_id as $programlanguage_id){
                $pgl_j = null;
                $pgl_j = new Program_Language_Job;
                $pgl_j->job_id = $job->id;
                $pgl_j->program_language_id = $programlanguage_id;
                $pgl_j->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Bạn đã cập nhật job thành công']);
        } catch(\Exception $e){
            DB::rollback();
            return response()->json(['success'=>false, 'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.']);
        }
    }

    function remove(Request $request){
        $id = $request->id;
        if($id){
            $job = Job::find($id);
            DB::beginTransaction();
            try {
                $job->delete();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Xoá job thành công'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi hệ thống. Vui lòng thử lại sau !'
                ]);
            }
        }
    }

    function getJob(Request $request){
        $id = $request->id;
        $job = Job::find($id);
        if(!$job){
            return response()->json(['success' => false, 'message' => 'Không tìm thấy job nào']);
        }
        else {
            $user = Company::where('user_id', $job->user_id)->first();
            if(!$user) $user = School::where('user_id', $job->user_id)->first();
            if(!$user) return response()->json(['success' => false, 'message' => 'Người tạo job không tồn tại']);
            $job->user_name = $user->name;
            $job->type_name = Job_Type::find($job->type_id)->name;
            $job->experience_name = Job_Experience::find($job->experience_id)->name;
            $job->country_name = Country::find($job->country_id)->name;
            $job->availabilty_name = Job_Availabilty::find($job->availabilty_id)->name;
            $language = array();
            $language = Program_Language_Job::where('job_id', $id)->get();
            foreach($language as $lg){
                $id_lg = $lg->program_language_id;
                $lg_data = Program_Language::find($id_lg);
                $lg->program_language_name = $lg_data->name;
            }
            $job->program_language = $language;
            return response()->json(['success' => true, 'data' => $job]);
        }
    }

    function list(Request $request){
        $random = $request->random ?? 0;
        $offset = $request->offset ?? 0;
        $limit = $request->limit ?? 10;
        $list = array();
        if(!$random) $list = Job::limit($limit)->offset($offset)->orderBy('created_at', 'desc')->get();
        else {
            $list = Job::all();
            if($list->count() < $limit) $limit = $list->count();
            $list->random($limit);
        }

        $arrRes = [];
        foreach($list as $ls) {
            $job = Job::find($ls->id);
            $user = Company::where('user_id', $job->user_id)->first();
            if(!$user) $user = School::where('user_id', $job->user_id)->first();
            $job->user_name = $user->name;
            $job->type_name = Job_Type::find($job->type_id)->name;
            $job->experience_name = Job_Experience::find($job->experience_id)->name;
            $job->country_name = Country::find($job->country_id)->name;
            $job->availabilty_name = Job_Availabilty::find($job->availabilty_id)->name;
            $language = array();
            $language = Program_Language_Job::where('job_id', $ls->id)->get();
            foreach($language as $lg){
                $id_lg = $lg->program_language_id;
                $lg_data = Program_Language::find($id_lg);
                $lg->program_language_name = $lg_data->name;
            }
            $job->program_language = $language;

            $user = User::find($ls->user_id);
            $job->avatar = $user->avatar;
            $arrRes[] = $job;
        }

        return response()->json(['jobs' => $arrRes]);
    }

    function getType(){
        $list = Job_Type::all();
        return response()->json([
            'types' => $list
        ]);
    }
    function removeType(Request $request){
        $id = $request->id;
        DB::beginTransaction();
        try {
            $type = Job_Type::find($id);
            $type->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Xoá thành công']);
        } catch (\Exception $e){
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Xoá thất bại']);
        }
    }
    function addOrUpdateType(Request $request){
        $id = $request->id;
        $name = $request->name;
        $type = null;

        DB::beginTransaction();
        try {
            if($id) $type = Job_Type::find($id);
            if($type) $type->name = $name;
            else {
                $type = new Job_Type;
                $type->name = $name;
            }
            $type->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
        } catch (\Exception $e){
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Cập nhật thất bại']);
        }
    }

    function getAvailabilty(){
        $list = Job_Availabilty::all();
        return response()->json([
            'availabilties' => $list
        ]);
    }

    function removeAvailabilty(Request $request){
        $id = $request->id;
        DB::beginTransaction();
        try {
            $type = Job_Availabilty::find($id);
            $type->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Xoá thành công']);
        } catch (\Exception $e){
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Xoá thất bại']);
        }
    }
    function addOrUpdateAvailabilty(Request $request){
        $id = $request->id;
        $name = $request->name;
        $type = null;

        DB::beginTransaction();
        try {
            if($id) $type = Job_Availabilty::find($id);
            if($type) $type->name = $name;
            else {
                $type = new Job_Availabilty;
                $type->name = $name;
            }
            $type->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
        } catch (\Exception $e){
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Cập nhật thất bại']);
        }
    }


    function getExperience(){
        $list = Job_Experience::all();
        return response()->json([
            'experiences' => $list
        ]);
    }

    function removeExperience(Request $request){
        $id = $request->id;
        DB::beginTransaction();
        try {
            $type = Job_Experience::find($id);
            $type->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Xoá thành công']);
        } catch (\Exception $e){
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Xoá thất bại']);
        }
    }
    function addOrUpdateExperience(Request $request){
        $id = $request->id;
        $name = $request->name;
        $type = null;

        DB::beginTransaction();
        try {
            if($id) $type = Job_Experience::find($id);
            if($type) $type->name = $name;
            else {
                $type = new Job_Experience;
                $type->name = $name;
            }
            $type->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
        } catch (\Exception $e){
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Cập nhật thất bại']);
        }
    }


}
