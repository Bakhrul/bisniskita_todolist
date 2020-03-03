<?php

namespace App\Http\Controllers;

use App\Todo;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Auth;
use App\User;
use App\todolistRole;
use File;
use App\Attachment;
use Illuminate\Support\Facades\Storage;
use Response;

class ToDoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRequestDownloadFile()
    {
        return response()->download(storage_path("app/public/{$filename}"));

    }

    public function getTodoAction($id)
    {
        $data = DB::table('d_todolist_action')->join('d_todolist','tla_todolist','tl_id')->where('tla_todolist',$id)->get();
        $datas = array();
        foreach ($data as $key => $value) {
            $arr = [
                'id' => $value->tla_number,
                'todo' => $value->tla_todolist,
                'title' => $value->tla_title,
                'created'=> $value->tl_created,
                'done' => $value->tla_executed,
                'valid' => $value->tla_validation

            ];
            array_push($datas, $arr);
        }
        return response()->json($datas);
    }

    public function getFiles($todo)
    {
        $data = Attachment::where('tla_todolist', $todo)->orderBy('tla_id', 'ASC')->get();
        
        $datas = array();

        foreach ($data as $key => $value) {
            $arr = [
                'id'    => $value->tla_id,
                'todo'  => $value->tla_todolist,
                'path'  => $value->tla_path
            ];
            array_push($datas, $arr);
        }
        return response()->json($datas);
    }

    public function getHistory()
    {
        $data = todolistRole::leftJoin('d_todolist', function ($q) {
            $q->on('tlr_todolist', 'tl_id');
            $q->leftJoin('d_project', 'tl_project', 'p_id');
        })
        ->where(function($q){
            $q->orWhere('tl_status','Finish');
            $q->orWhere('p_status','Finish');
        })
        // 
        ->where('tlr_users', Auth::user()->us_id)
        ->groupBy('p_id')
        ->get();

        $datas = array(
            
        );
        foreach ($data as $key => $value) {
            if ($value->p_id != '') {
                $sum = DB::table('d_todolist')->where('tl_project', $value->p_id)->sum('tl_progress');
                $count = DB::table('d_todolist')->where('tl_project', $value->p_id)->count('tl_progress');
                $percent = (($sum/$count)/100);
                $arr = [
                    'id' => $value->p_id,
                    'title' => $value->p_name,
                    'start' => $value->p_timestart,
                    'end'   => $value->p_timeend,
                    'status' =>$value->p_status,
                    'progress' =>$percent,
                    'isproject' =>true
                ];
                array_push($datas, $arr);
            } else {
                $arr = [
                    'id' => $value->tl_id,
                    'title' => $value->tl_title,
                    'start' => $value->tl_planstart,
                    'end'   => $value->tl_planend,
                    'status' =>$value->tl_status,
                    'progress' => floatval($value->tl_progress/100),
                    'isproject' =>false
                ];
                array_push($datas, $arr);
            }
        }
        return response()->json($datas);
    }

    public function getPeserta($todo, $access)
    {
        $roleUser=DB::table('d_todolist_roles')->where('tlr_todolist', $todo)->where('tlr_users', '=', Auth::user()->us_id)->value('tlr_role');
        $data = User::leftJoin('d_todolist_roles', 'tlr_users', 'us_id')
        ->leftJoin('m_roles', 'tlr_role', 'r_id')
        ->where('tlr_todolist', $todo)
        ->orderBy('tlr_role', 'ASC')
        ->groupBy('tlr_users');
        // dd($data->get());
        if ($access == '1') {
            $data = $data->get();
        } else {
            $data = $data->where('tlr_role', $access)->get();
        }
        $datas = array();
        foreach ($data as $key => $value) {
            $arr = [
                'id' => $value->us_id,
                'name' => $value->us_name,
                'email' => $value->us_email,
                'access' => $value->r_name,
                'todo' => $value->tlr_todolist,
            ];
            array_push($datas, $arr);
        }
        return response()->json([
            'users' => $datas,
            'roleUser' => $roleUser
        ]);
    }

    public function getPesertaFilter(Request $request)
    {
        $data = User::where('us_email', $request->email)->get();
        $datas = array();
        foreach ($data as $key => $value) {
            # code...
            $arr = [
                'id' => $value->us_id,
                'name' => $value->us_name,
                'email' => $value->us_email,
            ];
            array_push($datas, $arr);
        }     
        return response()->json($datas);
    }

    public function category()
    {
        $data = DB::table('m_category')->orderBy('c_name', 'ASC')->where('c_user', null)->get();
        $dataFromUser = DB::table('m_category')->orderBy('c_name', 'ASC')->where('c_user', Auth::user()->us_id)->get();
        $datax = $data->merge($dataFromUser);
        $sorted = $datax->sortBy('c_name');
        $sorted->values()->all();
        $datas = array();
        foreach ($sorted as $key => $value) {
            $arr = [
                'id' => $value->c_id,
                'name'=> $value->c_name
            ];
            array_push($datas, $arr);
        }
        return response()->json($datas);
    }

    public function index($index)
    {
        setlocale(LC_TIME, 'IND');

        $type = $index;
        $todos = array();
        $data = Todo::orderBy('tl_planstart', 'ASC')
                ->join('d_todolist_roles', 'tlr_todolist', 'tl_id')
                ->leftJoin('d_todolist_important', function ($join) {
                    $join->on('d_todolist.tl_id', '=', 'd_todolist_important.tli_todolist')
                        ->where('d_todolist_important.tli_users', Auth::user()->us_id);
                })
                ->where('tlr_users', Auth::user()->us_id)
                ->groupBy('tl_id');
                
        if ($type == "1") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '<', Carbon::today())->where('tl_status', '=', 'Open');
            })->get();
        } elseif ($type == "2") {
            // return response()->json(Carbon::tomorrow());
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '>', Carbon::today());
                $q->where("tl_planend", '<=', Carbon::today()->setTime(23, 59, 59));
            })->get();
        } elseif ($type == "3") {
            // return ;
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '>', Carbon::tomorrow())
            ->Where('tl_planend', '<=', Carbon::tomorrow()->setTime(23, 59, 59));
            })->get();
        } elseif ($type == "4") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '>', Carbon::tomorrow()->addDay())
                ->where('tl_planend', '<=', Carbon::tomorrow()->addDay()->setTime(23, 59, 59));
            })->get();
        } elseif ($type == "5") {
            $data = $data->where(function ($q) {
                $q->whereBetween("tl_planend", [Carbon::now()->startOfWeek(Carbon::SUNDAY),Carbon::now()->endOfWeek(Carbon::SATURDAY)]);
            })->get();
        } elseif ($type == "6") {
            $data = $data->where(function ($q) {
                $q->whereMonth("tl_planend", '=', Carbon::now()->month);
            })->get();
        } else {
            $data = $data->where(function ($q) {
                $q->where('tl_status', '=', 'Pending');
            })->get();
        }

        foreach ($data as $key => $value) {
            $arr = [
                'id'    => $value->tl_id,
                'title' => $value->tl_title,
                'start' => $value->tl_planstart,
                'end'   => $value->tl_planend,
                'status' => $value->tl_status,
                'category' => $value->tl_category,
                'statuspinned' => $value->tli_todolist,
            ];
            array_push($todos, $arr);
        }
        return response()->json($todos);
    }

    public function actionpinned_todo(Request $request)
    {
        DB::BeginTransaction();
        try {
            $cekTodo = DB::table('d_todolist_important')->where('tli_todolist', $request->todolist)->where('tli_users', Auth::user()->us_id)->first();
            $status = '';
            if ($cekTodo == null) {
                DB::table('d_todolist_important')->insert([
                    'tli_users' => Auth::user()->us_id,
                    'tli_todolist' => $request->todolist,
                    'tli_created' => Carbon::now(),
                ]);
                $status = 'tambah';
            } else {
                DB::table('d_todolist_important')->where('tli_todolist', $request->todolist)->where('tli_users', Auth::user()->us_id)->delete();
                $status = 'hapus';
            }

            DB::commit();
            return response()->json([
                'status' => $status,
            ]);
        } catch (Exception $e) {
            return $e;
        }
    }
    public function todolist_berbintang(Request $request)
    {
        $type = $request->filter;

        $data = DB::table('d_todolist_important')
                ->join('d_todolist_roles', function ($join) {
                    $join->on('d_todolist_important.tli_todolist', '=', 'd_todolist_roles.tlr_todolist')
                        ->where('d_todolist_roles.tlr_users', Auth::user()->us_id);
                })
                ->join('d_todolist', 'tli_todolist', 'tl_id')
                ->where('tl_title', 'LIKE', $request->search .'%')
                  ->groupBy('tl_id');
        
        if ($type == "1") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '<', Carbon::today())->where('tl_status', '=', 'Open');
            })->get();
        } elseif ($type == "2") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '>', Carbon::today());
                $q->where("tl_planend", '<=', Carbon::today()->setTime(23, 59, 59));
            })->get();
        } elseif ($type == "3") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '>', Carbon::tomorrow())
            ->Where('tl_planend', '<=', Carbon::tomorrow()->setTime(23, 59, 59));
            })->get();
        } elseif ($type == "4") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '>', Carbon::tomorrow()->addDay())
                ->where('tl_planend', '<=', Carbon::tomorrow()->addDay()->setTime(23, 59, 59));
            })->get();
        } elseif ($type == "5") {
            $data = $data->where(function ($q) {
                $q->whereBetween("tl_planend", [Carbon::now()->startOfWeek(Carbon::SUNDAY),Carbon::now()->endOfWeek(Carbon::SATURDAY)]);
            })->get();
        } elseif ($type == "6") {
            $data = $data->where(function ($q) {
                $q->whereMonth("tl_planend", '=', Carbon::now()->month);
            })->get();
        } else {
            $data = $data->where(function ($q) {
                $q->where('tl_status', '=', 'Pending');
            })->get();
        }

        return response()->json([
            'todo' => $data,
            'counttodo' => count($data),
        ]);
    }
    public function search_todo_project(Request $request)
    {
        $type = $request->filter;

        $data = DB::table('d_todolist')
                ->join('d_todolist_roles', function ($join) {
                    $join->on('d_todolist.tl_id', '=', 'd_todolist_roles.tlr_todolist')
                        ->where('d_todolist_roles.tlr_users', Auth::user()->us_id);
                })
                ->leftJoin('d_todolist_important', function ($join) {
                    $join->on('d_todolist.tl_id', '=', 'd_todolist_important.tli_todolist')
                        ->where('d_todolist_important.tli_users', Auth::user()->us_id);
                })
                ->where('tl_title', 'LIKE', $request->search .'%')
               ->groupBy('tl_id');
               
        if ($type == "1") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '<', Carbon::today())->where('tl_status', '=', 'Open');
            })->get();
        } elseif ($type == "2") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '>', Carbon::today());
                $q->where("tl_planend", '<=', Carbon::today()->setTime(23, 59, 59));
            })->get();
        } elseif ($type == "3") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '>', Carbon::tomorrow())
            ->Where('tl_planend', '<=', Carbon::tomorrow()->setTime(23, 59, 59));
            })->get();
        } elseif ($type == "4") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '>', Carbon::tomorrow()->addDay())
                ->where('tl_planend', '<=', Carbon::tomorrow()->addDay()->setTime(23, 59, 59));
            })->get();
        } elseif ($type == "5") {
            $data = $data->where(function ($q) {
                $q->whereBetween("tl_planend", [Carbon::now()->startOfWeek(Carbon::SUNDAY),Carbon::now()->endOfWeek(Carbon::SATURDAY)]);
            })->get();
        } elseif ($type == "6") {
            $data = $data->where(function ($q) {
                $q->whereMonth("tl_planend", '=', Carbon::now()->month);
            })->get();
        } else {
            $data = $data->where(function ($q) {
                $q->where('tl_status', '=', 'Pending');
            })->get();
        }

        
        $Project = DB::table('d_project_member')
                    ->join('d_project', 'mp_project', 'p_id')
                    ->where('p_name', 'LIKE', $request->search .'%')
                    ->where('mp_user', Auth::user()->us_id)
                    ->get();

        return response()->json([
            'todo' => $data,
            'counttodo' => count($data),
            'project' => $Project,
            'countproject' => count($Project),
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::BeginTransaction();
        try {
       
        // return response()->json(storage_path() ."/files/".$imageName);
            // \File::put($path . $imageName, base64_decode($image));
            $todo = new Todo;
            $project = null;
            if ($request->category == '1') {
                $project = $request->project;
            }

            $todo->tl_title         = $request->title;
            $todo->tl_category      = $request->category;
            $todo->tl_project       = $project;
            $todo->tl_desc          = $request->desc;
            $todo->tl_status        = 'Open';
            $todo->tl_progress      = 0;
            $todo->tl_planstart     = Carbon::parse($request->planstart)->format('Y-m-d H:i:s');
            $todo->tl_planend       = Carbon::parse($request->planend)->format('Y-m-d H:i:s');
            $todo->tl_exestart      = null;
            $todo->tl_exeend        = null;
            $todo->tl_created       = Carbon::now();
            $todo->tl_updated       = Carbon::now();
            $todo->tl_allday        = $request->allday;
            $todo->save();
            
            DB::table('d_todolist_roles')
                ->insert([
                    'tlr_users'     => Auth::user()->us_id,
                    'tlr_todolist'  => $todo->tl_id,
                    'tlr_role'      => 1
                ]);
            if ($request->category == '1') {
                $memberProject = DB::table('d_project_member')->where('mp_project', $request->project)->get();
                foreach ($memberProject as $key => $value) {
                    DB::table('d_todolist_roles')->insert([
                        'tlr_users' => $value->mp_user,
                        'tlr_todolist' => $todo->tl_id,
                        'tlr_role' => $value->mp_role,
                        'tlr_own' => 'P',
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $todo->tl_id,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    public function store_attachment(Request $request)
    {
        DB::BeginTransaction();
        try {
            $ext = pathinfo($request->pathname, PATHINFO_EXTENSION);
            $ext = str_replace("'", "", $ext);
            $image = $request->file64;
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $request->filename);

            $imageName = date("ymdhis").'_'.$withoutExt.'.'.$ext;
            $path = storage_path(). '/files/' ;

            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            \File::put($path . $imageName, base64_decode($image));
            // Storage::disk('local')->put($imageName, base64_decode($image));
            // $path = Storage::putFile($path . $imageName, base64_decode($image));



            $data = new Attachment;
            $data->tla_path = $imageName;
            $data->tla_todolist = $request->todolist;
            $data->save();
            DB::commit();
            return response()->json([
                'status' => 'success'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail_todo(Request $request)
    {
        $Todo = DB::table('d_todolist')
                ->where('tl_id', $request->todolist)
                ->first();

        $member = DB::table('d_todolist_roles')
                ->join('m_users', 'tlr_users', 'us_id')
                ->where('tlr_todolist', $request->todolist)
                ->orderBy('tlr_role','ASC')
                ->groupBy('tlr_users')
                ->get();

        $TodoFile = DB::table('d_todolist_attachment')->where('tla_todolist', $request->todolist)->get();

        $dataFile = array();
        foreach ($TodoFile as $key => $value) {
            $path = asset('storage/files/' . $value->tla_path);
     
            $arr = [
                'id' => $value->tla_id,
                'todo' => $value->tla_todolist,
                'path' => $path,
                'filename' => $value->tla_path

            ];

            array_push($dataFile,$arr);
            
        }


        $statusKita = DB::table('d_todolist_roles')
                    ->where('tlr_todolist',$request->todolist)
                    ->where('tlr_users',Auth::user()->us_id)
                    ->orderBy('tlr_role','ASC')
                    ->first();

        return response()->json([
            'todo' => $Todo,
            'todo_member' => $member,
            'todo_file' => $dataFile,
            'status_kita' => $statusKita,
        ]);
    }
    public function detail_member_todo(Request $request)
    {
        $Member = DB::table('d_todolist_roles')
                 ->join('m_users', 'tlr_users', 'us_id')
                 ->where('tlr_users', $request->member)
                 ->where('tlr_todolist', $request->todo)
                 ->first();
        return response()->json($Member);
    }
    public function realisasi_todo(Request $request)
    {
        DB::BeginTransaction();
        try {
            DB::table('d_todolist_timeline')->insert([
                'tlt_todolist' => $request->todolist,
                'tlt_user' => Auth::user()->us_id,
                'tlt_progress' => $request->progress,
                'tlt_note' => $request->catatan,
                'tlt_created'=> Carbon::now('Asia/Jakarta'),
            ]);
            DB::table('d_todolist')->where('tl_id', $request->todolist)->update([
                'tl_progress' => $request->progress,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    public function storeAction(Request $request)
    {
        DB::BeginTransaction();
        try {
            switch ($request->type) {
                case 'Action':
                    $subId = DB::table('d_todolist_action')->where('tla_todolist',$request->todo)->max('tla_number') + 1;
                    DB::table('d_todolist_action')->insert([
                        'tla_todolist' => $request->todo,
                        'tla_number' => $subId,
                        'tla_title' => $request->title,
                        'tla_created' => Carbon::now('Asia/Jakarta'),
                        'tla_createduser' => Auth::user()->us_id,
                    ]);

                    break;

                case 'Ready':
                     $subId = DB::table('d_todolist_ready')->where('tlr_todolist',$request->todo)->max('tlr_number') + 1;
                    DB::table('d_todolist_ready')->insert([
                        'tlr_todolist' => $request->todo,
                        'tlr_number' => $subId,
                        'tlr_title' => $request->title,
                        'tlr_created' => Carbon::now('Asia/Jakarta'),
                        'tlr_createduser' => Auth::user()->us_id,
                    ]);
                    break;

                case 'Normal':
                    $subId = DB::table('d_todolist_normal')->where('tln_todolist',$request->todo)->max('tln_number') + 1;
                    DB::table('d_todolist_normal')->insert([
                        'tln_todolist' => $request->todo,
                        'tln_number' => $subId,
                        'tln_title' => $request->title,
                        'tln_created' => Carbon::now('Asia/Jakarta'),
                        'tln_createduser' => Auth::user()->us_id,
                    ]);
                    break;

                case 'Done':
                    $subId = DB::table('d_todolist_done')->where('tld_todolist',$request->todo)->max('tld_number') + 1;
                     DB::table('d_todolist_done')->insert([
                        'tld_todolist' => $request->todo,
                        'tld_number' => $subId,
                        'tld_title' => $request->title,
                        'tld_created' => Carbon::now('Asia/Jakarta'),
                        'tld_createduser' => Auth::user()->us_id,
                    ]);
                    break;
                default:
                    # code...
                    break;
            }
            
            DB::Commit();
            return response()->json([
                'status'=>'success',
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function storePeserta(Request $request)
    {
        $us_id = $request->user;
        $todos = $request->todo;
        $roles = $request->role;
        // return json_encode($request->all());
        $role = '';
        if ($roles == 'Admin') {
            $role = 2;
        } elseif ($roles == 'Executor') {
            $role = 3;
        } elseif ($roles == 'Viewer') {
            $role = 4;
        }

        $todo = DB::table('d_todolist_roles')->where('tlr_todolist', $todos)->where('tlr_users', $us_id)->first();
        if ($todo == null) {
            DB::BeginTransaction();
            try {
                DB::table('d_todolist_roles')
                ->insert([
                    'tlr_users'     => $us_id,
                    'tlr_todolist'  => $todos,
                    'tlr_role'      => $role,
                    'tlr_own'      => $request->own
                ]);

                DB::commit();
                return response()->json([
            'status' => 'success'
            ]);
            } catch (Exception $e) {
                DB::rollback();
                return $e;
            }
        } elseif ($todo != null) {
            if ($todo->tlr_role == 1) {
                return response()->json([
                'status' => 'owner'
            ]);
            } else {
                DB::BeginTransaction();
                try {
                    DB::table('d_todolist_roles')
                ->where('tlr_users', $us_id)
                ->where('tlr_todolist', $todos)
                ->update([
                    'tlr_users'     => $us_id,
                    'tlr_todolist'  => $todos,
                    'tlr_role'      => $role
                ]);

                    DB::commit();
                    return response()->json([
            'status' => 'success'
            ]);
                } catch (Exception $e) {
                    DB::rollback();
                    return $e;
                }
            }
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function show(Todo $todo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $todo = Todo::where('tl_id', $id)
                ->join('m_category', 'tl_category', 'c_id')
                ->first();
        $MemberTodo = DB::table('d_todolist_roles')
                    ->join('m_users', 'tlr_users', 'us_id')
                    ->join('m_roles', 'tlr_role', 'r_id')
                    ->where('tlr_todolist', $id)
                    ->orderBy('tlr_role','ASC')
                    ->groupBy('tlr_users')
                    ->get();
        $documentTodo = DB::table('d_todolist_attachment')
                        ->where('tla_todolist', $id)
                        ->get();
        return response()->json([
            'todo' => $todo,
            'member_todo' => $MemberTodo,
            'document_todo' => $documentTodo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function updateAction(Request $request, $id)
    {
        DB::BeginTransaction();
        try {            
            $cekAdminTodo = DB::table('d_todolist_roles')->where('tlr_todolist',$request->todo)->where('tlr_users',Auth::user()->us_id)->orderBy('tlr_role','ASC')->first();
            if($cekAdminTodo->tlr_role == '1' || $cekAdminTodo->tlr_role == '2' || $cekAdminTodo->tlr_role == 1 || $cekAdminTodo->tlr_role == 2){
                $autoValidationUser = Auth::user()->us_id;
                $autoValidationDate =  Carbon::now('Asia/Jakarta');
            }else{
                $autoValidationUser = null;
                $autoValidationDate = null;
            }
            switch ($request->type) {
                case 'Action':
                    $cekData = DB::table('d_todolist_action')
                              ->where('tla_todolist',$request->todo)
                              ->where('tla_number',$id)
                              ->first();

                    if($cekData->tla_executed == null){
                        $executedDate = Carbon::now('Asia/Jakarta');
                        $executedUser = Auth::user()->us_id;
                        $status = 'selesai';
                        $validationDate = $autoValidationDate;
                        $validationUser = $autoValidationUser;
                    }else{
                        $executedDate = null;
                        $executedUser = null;
                        $status = 'belum selesai';
                        $validationDate = null;
                        $validationUser = null;
                    }

                    DB::table('d_todolist_action')
                    ->where('tla_todolist',$request->todo)
                    ->where('tla_number',$id)
                    ->update([
                        'tla_executed' => $executedDate,
                        'tla_executeduser'  => $executedUser,
                        'tla_validationuser' => $validationUser,
                        'tla_validation' => $validationDate,
                    ]);

                break;
                case 'Done':

                    $cekData = DB::table('d_todolist_done')
                                ->where('tld_todolist',$request->todo)
                                ->where('tld_number',$id)
                                ->first();

                   if($cekData->tld_executed == null){
                        $executedDate = Carbon::now('Asia/Jakarta');
                        $executedUser = Auth::user()->us_id;
                        $status = 'selesai';
                        $validationDate = $autoValidationDate;
                        $validationUser = $autoValidationUser;
                    }else{
                        $executedDate = null;
                        $executedUser = null;
                        $status = 'belum selesai';
                        $validationDate = null;
                        $validationUser = null;
                    }

                     DB::table('d_todolist_done')
                    ->where('tld_todolist',$request->todo)
                    ->where('tld_number',$id)
                    ->update([
                        'tld_executed' => $executedDate,
                        'tld_executeduser' => $executedUser,
                        'tld_validationuser' => $validationUser,
                        'tld_validation' => $validationDate,
                    ]);


                break;
                case 'Normal':

                    $cekData = DB::table('d_todolist_normal')->where('tln_todolist',$request->todo)->where('tln_number',$id)->first();

                    if($cekData->tln_executed == null){
                        $executedDate = Carbon::now('Asia/Jakarta');
                        $executedUser = Auth::user()->us_id;
                        $status = 'selesai';
                        $validationDate = $autoValidationDate;
                        $validationUser = $autoValidationUser;
                    }else{
                        $executedDate = null;
                        $executedUser = null;
                        $status = 'belum selesai';
                        $validationDate = null;
                        $validationUser = null;
                    }

                     DB::table('d_todolist_normal')
                    ->where('tln_todolist',$request->todo)
                    ->where('tln_number',$id)
                    ->update([
                        'tln_executed' => $executedDate,
                        'tln_executeduser' => $executedUser,
                        'tln_validation' => $validationDate,
                        'tln_validationuser' => $validationUser,
                    ]);

                break;
                case 'Ready':

                    $cekData = DB::table('d_todolist_ready')->where('tlr_todolist',$request->todo)->where('tlr_number',$id)->first();
                  if($cekData->tlr_executed == null){
                        $executedDate = Carbon::now('Asia/Jakarta');
                        $executedUser = Auth::user()->us_id;
                        $status = 'selesai';
                        $validationDate = $autoValidationDate;
                        $validationUser = $autoValidationUser;
                    }else{
                        $executedDate = null;
                        $executedUser = null;
                        $status = 'belum selesai';
                        $validationDate = null;
                        $validationUser = null;
                    }
                     DB::table('d_todolist_ready')
                    ->where('tlr_todolist',$request->todo)
                    ->where('tlr_number',$id)
                    ->update([
                        'tlr_executed' => $executedDate,
                        'tlr_executeduser' => $executedUser,
                        'tlr_validationuser' => $validationUser,
                        'tlr_validation' => $validationDate,
                    ]);
                break;
                default:
                     return response()->json([
                        'status' => 'type todolist tidak ditemukan',
                    ]);
                break;
            }
            
            DB::commit();
            return response()->json([
                'status'=>$status,
                'validation' => $validationDate,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }

    public function update(Request $request, $id)
    {
        DB::BeginTransaction();
        try {
            $todo = Todo::find($id);
            $project = null;
            if ($request->category == '1') {
                $project = $request->project;
            }

            if ($request->allday == '0') {
                $planstart = Carbon::parse($request->planstart)->format('Y-m-d H:i:s');
                $planend = Carbon::parse($request->planend)->format('Y-m-d H:i:s');
            } else {
                $planstart = Carbon::parse($request->planstart)->setTime(00, 00, 00);
                $planend = Carbon::parse($request->planend)->setTime(23, 59, 59);
            }

            $todo->tl_title         = $request->title;
            $todo->tl_category      = $request->category;
            $todo->tl_project       = $project;
            $todo->tl_desc          = $request->desc;
            $todo->tl_status        = 'Open';
            $todo->tl_progress      = 0;
            $todo->tl_planstart     = $planstart;
            $todo->tl_planend       = $planend;
            $todo->tl_allday        = $request->allday;
            $todo->tl_exestart      = null;
            $todo->tl_exeend        = null;
            $todo->tl_created       = Carbon::now();
            $todo->tl_updated       = Carbon::now();
            $todo->update();

            $cekDataSebelumnya = DB::table('d_todolist')->where('tl_id', $id)->first();
            if ($request->category != '1') {
                $ProjectTodoMember = DB::table('d_todolist_roles')
                                ->where('tlr_todolist', $id)
                                ->where('tlr_own', 'P')
                                ->where('tlr_role', '!=', 1)
                                ->delete();
            
                $projetLeader = DB::table('d_todolist_roles')->where('tlr_own', 'P')->where('tlr_role', 1)->get();
                foreach ($projetLeader as $key => $value) {
                    DB::table('d_todolist_roles')->where('tlr_users', $value->tlr_users)->where('tlr_todolist', $id)->delete();
                    DB::table('d_todolist_roles')->insert([
                    'tlr_own' => 'T',
                    'tlr_users' => $value->tlr_users,
                    'tlr_role' => '1',
                    'tlr_todolist' => $id,
                ]);
                }
            }

            if ($request->category == '1') {
                $projectMember = DB::table('d_project_member')->where('mp_project', $request->project)->get();
                foreach ($projectMember as $key => $value) {
                    DB::table('d_todolist_roles')
                ->where('tlr_users', $value->mp_user)
                ->where('tlr_todolist', $id)
                ->where('tlr_own', 'P')
                ->delete();

                    DB::table('d_todolist_roles')->insert([
                    'tlr_users' => $value->mp_user,
                    'tlr_todolist' => $id,
                    'tlr_own' => 'P',
                    'tlr_role' => $value->mp_role,
                ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success'
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    public function todo_edit_addmember(Request $request)
    {
        DB::BeginTransaction();
        try {
            $cekUser = DB::table('m_users')->where('us_email', $request->member)->first();
            if ($cekUser == null) {
                return response()->json([
                    'status' => 'email belum terdaftar',
                ]);
            }

            $cekMember = DB::table('d_todolist_roles')->where('tlr_users', $cekUser->us_id)->where('tlr_todolist', $request->todolist)->first();
            if ($cekMember != null) {
                return response()->json([
                    'status' => 'member sudah terdaftar',
                ]);
            }
            DB::table('d_todolist_roles')->where('tlr_users', $cekUser->us_id)->where('tlr_todolist', $request->todolist)->where('tlr_own', 'T')->delete();
            DB::table('d_todolist_roles')->insert([
                'tlr_users' => $cekUser->us_id,
                'tlr_todolist' => $request->todolist,
                'tlr_role' => $request->role,
                'tlr_own' => 'T',
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    public function todo_edit_deletemember(Request $request)
    {
        DB::BeginTransaction();
        try {
            DB::table('d_todolist_roles')->where('tlr_users', $request->member)->where('tlr_todolist', $request->todolist)->where('tlr_own', 'T')->delete();
            DB::table('d_todolist_important')->where('tli_users', $request->member)->where('tli_todolist', $request->todolist)->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    public function todo_edit_ganti_statusmember(Request $request)
    {
        DB::BeginTransaction();
        try {
            DB::table('d_todolist_roles')
            ->where('tlr_users', $request->member)
            ->where('tlr_todolist', $request->todolist)
            ->where('tlr_own', 'T')
            ->update([
                'tlr_role' => $request->role,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        //
    }
    public function destroyPeserta($user, $todo)
    {
        DB::BeginTransaction();
        try {
            DB::table('d_todolist_roles')
            ->where('tlr_todolist', $todo)
            ->where('tlr_users', $user)
                ->delete();
            DB::Commit();
            return response()->json(
                [
                    'status' => 'success'
                ]
            );
        } catch (Exception $e) {
            return $e;
        }
    }

    public function destroyFile($id)
    {
        DB::BeginTransaction();
        try {
            $data = Attachment::find($id);
            unlink(storage_path('files/'.$data->tla_path));
            $data->delete();
            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return $th;
        }
    }
    public function todo_ready($id)
    {
        $todoReady = DB::table('d_todolist_ready')->where('tlr_todolist', $id)->get();
        return response()->json([
            'todo_ready' => $todoReady,
        ]);
    }
     public function todo_normal($id){
        $todoReady = DB::table('d_todolist_normal')->where('tln_todolist',$id)->get();
        return response()->json([
            'todo_normal' => $todoReady,
        ]);
    }
     public function todo_done($id){
        $todoReady = DB::table('d_todolist_done')->where('tld_todolist',$id)->get();
        return response()->json([
            'todo_done' => $todoReady,
        ]);
    }
    public function validation_listtodo(Request $request){
        DB::BeginTransaction();
        try {            
            $id = $request->id;
            switch ($request->type) {
                case 'Action':

                    $cekData = DB::table('d_todolist_action')
                              ->where('tla_todolist',$request->todo)
                              ->where('tla_number',$id)
                              ->first();

                    if($cekData->tla_validation == null){
                        $done = Carbon::now('Asia/Jakarta');
                        $status = 'validation';
                    }else{
                        $done = null;
                        $status = 'belum validation';
                    }

                    DB::table('d_todolist_action')
                    ->where('tla_todolist',$request->todo)
                    ->where('tla_number',$id)
                    ->update([
                        'tla_validation' => $done,
                    ]);

                break;
                case 'Done':

                    $cekData = DB::table('d_todolist_done')
                                ->where('tld_todolist',$request->todo)
                                ->where('tld_number',$id)
                                ->first();

                    if($cekData->tld_validation == null){
                        $done = Carbon::now('Asia/Jakarta');
                        $status = 'validation';
                    }else{
                        $done = null;
                        $status = 'belum validation';
                    }

                     DB::table('d_todolist_done')
                    ->where('tld_todolist',$request->todo)
                    ->where('tld_number',$id)
                    ->update([
                        'tld_validation' => $done,
                    ]);


                break;
                case 'Normal':

                    $cekData = DB::table('d_todolist_normal')->where('tln_todolist',$request->todo)->where('tln_number',$id)->first();

                    if($cekData->tln_validation == null){
                        $done = Carbon::now('Asia/Jakarta');
                        $status = 'validation';
                    }else{
                        $done = null;
                        $status = 'belum validation';
                    }

                     DB::table('d_todolist_normal')
                    ->where('tln_todolist',$request->todo)
                    ->where('tln_number',$id)
                    ->update([
                        'tln_validation' => $done,
                    ]);

                break;
                case 'Ready':

                    $cekData = DB::table('d_todolist_ready')->where('tlr_todolist',$request->todo)->where('tlr_number',$id)->first();
                    if($cekData->tlr_validation == null){
                        $done = Carbon::now('Asia/Jakarta');
                        $status = 'validation';
                    }else{
                        $done = null;
                        $status = 'belum validation';
                    }

                     DB::table('d_todolist_ready')
                    ->where('tlr_todolist',$request->todo)
                    ->where('tlr_number',$id)
                    ->update([
                        'tlr_validation' => $done,
                    ]);
                break;
                default:
                     return response()->json([
                        'status' => 'type todolist tidak ditemukan',
                    ]);
                break;
            }
            
            DB::commit();
            return response()->json([
                'status'=> $status,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
    public function started_todo(Request $request){
        setlocale(LC_TIME, 'IND');
        DB::BeginTransaction();
        try {
            switch ($request->type) {
                case 'baru mengerjakan':
                     DB::table('d_todolist')->where('tl_id',$request->todo)->update([
                        'tl_exestart' => Carbon::now('Asia/Jakarta'),
                    ]);
                    break;
                case 'pending':
                         DB::table('d_todolist')->where('tl_id',$request->todo)->update([
                            'tl_status' => 'Pending',
                        ]);
                    break;
                case 'selesai':
                         DB::table('d_todolist')->where('tl_id',$request->todo)->update([
                             'tl_exeend' => Carbon::now('Asia/Jakarta'),
                             'tl_status' => 'Finish',
                             'tl_progress' => 100,
                        ]);
                         DB::table('d_todolist_timeline')->insert([
                            'tlt_todolist' => $request->todo,
                            'tlt_user' => Auth::user()->us_id,
                            'tlt_progress' => 100,
                            'tlt_note' => 'To Do Sudah Selesai Dikerjakan',
                            'tlt_created' => Carbon::now('Asia/Jakarta'),
                         ]);
                    break;
                case 'mulai mengerjakan kembali':
                         DB::table('d_todolist')->where('tl_id',$request->todo)->update([
                             'tl_status' => 'Open',
                        ]);
                        break;
                
                default:
                    # code...
                    break;
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function todo_activity(Request $request){
          $TodoActivity = DB::table('d_todolist_timeline')
                        ->join('m_users', 'tlt_user', 'us_id')
                        ->where('tlt_todolist', $request->todolist)
                        ->orderBy('tlt_id', 'Desc')
                        ->get();
        return response()->json($TodoActivity);
    }
}
