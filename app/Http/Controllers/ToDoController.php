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
use App\Http\Controllers\TokenController;
use Response;

class ToDoController extends Controller
{

    public function tesnotif(Request $request){
              $send_notif = new TokenController();
              $send_notif->sendNotif('Todolist','tesnotif','12');
              return response()->json($request->user);
    }
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
        $data = DB::table('d_todolist_action')->join('d_todolist', 'tla_todolist', 'tl_id')->where('tla_todolist', $id)->get();
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
        })
        ->where(function ($q) {
            $q->Where('tl_status', 'Finish');
        })
        //
        ->where('tlr_users', Auth::user()->us_id)
        ->groupBy('tl_id')
        ->get();

        $datas = array(
            
        );
        foreach ($data as $key => $value) {
            // $sum = DB::table('d_todolist')->where('tl_project', $value->p_id)->sum('tl_progress');
            // $count = DB::table('d_todolist')->where('tl_project', $value->p_id)->count('tl_progress');

            // $percent = $count > 0 ? round(((($sum/$count) / 100) * 100), 2) : 0;

            $arr = [
                    'id' => $value->tl_id,
                    'title' => $value->tl_title,
                    'start' => $value->tl_planstart,
                    'end'   => $value->tl_planend,
                    'status' =>$value->tl_status,
                    'progress' => $value->tl_progress,
                    'allday' =>$value->tl_allday
                ];
            array_push($datas, $arr);
        }
        return response()->json($datas);
    }

    public function getArchive()
    {
        $data = todolistRole::leftJoin('d_todolist', function ($q) {
            $q->on('tlr_todolist', 'tl_id');
        })
        ->where(function ($q) {
            $q->Where('tl_status', 'Pending');
        })
        //
        ->where('tlr_users', Auth::user()->us_id)
        ->groupBy('tl_id')
        ->get();

        $datas = array(
            
        );
        foreach ($data as $key => $value) {
            // $sum = DB::table('d_todolist')->where('tl_project', $value->p_id)->sum('tl_progress');
            // $count = DB::table('d_todolist')->where('tl_project', $value->p_id)->count('tl_progress');

            // $percent = $count > 0 ? round(((($sum/$count) / 100) * 100), 2) : 0;

            $arr = [
                    'id' => $value->tl_id,
                    'title' => $value->tl_title,
                    'start' => $value->tl_planstart,
                    'end'   => $value->tl_planend,
                    'status' =>$value->tl_status,
                    'progress' => $value->tl_progress,
                    'allday' =>$value->tl_allday
                ];
            array_push($datas, $arr);
        }
        return response()->json($datas);
    }

    public function getPeserta($todo, $access)
    {
        $roleUser=DB::table('d_todolist_roles')->where('tlr_todolist', $todo)->where('tlr_users', '=', Auth::user()->us_id)->value('tlr_role');
        $data = User::leftJoin('d_todolist_roles', 'tlr_users', 'us_id')
        ->leftJoin('m_roles', 'tlr_role', 'r_id')
        ->where('tlr_todolist', $todo)
        ->where('tlr_own','T')
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
                'image' => $value->us_image,
                'name' => $value->us_name,
                'email' => $value->us_email,
                'idaccess' => $value->r_id,
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
        $data = DB::table('m_category')
                ->where('c_user', null)
                ->orWhere('c_user', Auth::user()->us_id)
                ->orderBy('c_name', 'ASC')
                ->get();
        $datas = array();
        foreach ($data as $key => $value) {
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
        $sortBy = null;

        $type = $index;
        $statusPending = 0;
        $statusMolor = 0;
        $todos = array();
        $data = Todo::join('d_todolist_roles', 'tlr_todolist', 'tl_id')
                ->leftJoin('d_todolist_important', function ($join) {
                    $join->on('d_todolist.tl_id', '=', 'd_todolist_important.tli_todolist')
                        ->where('d_todolist_important.tli_users', Auth::user()->us_id);
                })
                ->where('tlr_users', Auth::user()->us_id)
                ->when($sortBy, function ($query, $sortBy) {
                    return $query->orderBy($sortBy);
                                 
                }, function ($query) {
                    return $query->orderBy('tli_todolist','desc');
                })
                ->groupBy('tl_id')
                ->orderBy('tl_planstart','ASC');

                

        $dataPending = Todo::orderBy('tl_planstart', 'ASC')
            ->join('d_todolist_roles', 'tlr_todolist', 'tl_id')
            ->where('tlr_users', Auth::user()->us_id)
            ->where(function ($q) {
            $q->where('tl_status', '=', 'Pending');
        })->first();

        $dataMolor = Todo::orderBy('tl_planstart', 'ASC')
            ->join('d_todolist_roles', 'tlr_todolist', 'tl_id')
            ->where('tlr_users', Auth::user()->us_id)
            ->where(function ($q) {
            $q->where("tl_planend", '<', Carbon::today())->where('tl_status', '=', 'Open');
        })->first();

         if($dataPending != null){
                $statusPending = 1;
        }
        if($dataMolor != null){
            $statusMolor = 1;
        }

        if ($type == "1") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '<', Carbon::today())->where('tl_status', '=', 'Open');
            })->get();
        } elseif ($type == "2") {
            $data = $data->where(function ($q) {
                $q->where("tli_users", '!=', null);
            })->get();
        } elseif ($type == "3") {
            $data = $data->where(function ($q) {
                $q->where("tl_planstart", '<=', Carbon::today()->setTime(23, 59, 59));
                $q->where("tl_planend", '>=', Carbon::today());
            })->get();
        } elseif ($type == "4") {
            $data = $data->where(function ($q) {
                $q->where("tl_planstart", '<=', Carbon::tomorrow()->setTime(23, 59, 59))
            ->Where('tl_planend', '>=', Carbon::tomorrow());
            })->get();
        } elseif ($type == "5") {
            $data = $data->where(function ($q) {
                $q->where("tl_planstart", '<=', Carbon::tomorrow()->addDay()->setTime(23, 59, 59))
                ->where('tl_planend', '>=', Carbon::tomorrow()->addDay());
            })->get();
        } elseif ($type == "6") {
            $data = $data->where(function ($q) {
                $q->whereBetween("tl_planstart", [Carbon::now()->startOfWeek(Carbon::SUNDAY),Carbon::now()->endOfWeek(Carbon::SATURDAY)->setTime(23, 59, 59)]);
                $q->OrWhereBetween("tl_planend", [Carbon::now()->startOfWeek(Carbon::SUNDAY),Carbon::now()->endOfWeek(Carbon::SATURDAY)->setTime(23, 59, 59)]);
            })->get();
        } elseif ($type == "7") {
            $data = $data->where(function ($q) {
                $q->where('tl_planstart', '<=', Carbon::now()->lastOfMonth()->setTime(23, 59, 59))
               ->where('tl_planend', '>=', Carbon::now()->firstOfMonth());
            })->get();
        } else {
            $data = $data->where(function ($q) {
                $q->where('tl_status', '=', 'Pending');
            })->get();
        }

        foreach ($data as $key => $value) {
            $statusProgress = '';
            if ($value->tl_status == 'Finish') {
                $statusProgress = 'compleshed';
            } elseif ($value->tl_status == 'Pending') {
                $statusProgress = 'pending';
            } elseif ($value->tl_status == 'Open' && $value->tl_planend < Carbon::today() && $value->tl_progress < 100) {
                $statusProgress = 'overdue';
            } elseif ($value->tl_status == 'Open' && $value->tl_exestart == null) {
                $statusProgress = 'waiting';
            } elseif ($value->tl_status == 'Open' && $value->tl_exestart != null) {
                $statusProgress = 'working';
            }
            $arr = [
                'id'    => $value->tl_id,
                'title' => $value->tl_title,
                'start' => $value->tl_planstart,
                'end'   => $value->tl_planend,
                'status' => $value->tl_status,
                'allday' => (int)$value->tl_allday,
                'category' => $value->tl_category,
                'statuspinned' => $value->tli_todolist,
                'statusprogress'    => $statusProgress,
              
            ];
            array_push($todos, $arr);
        }
        return response()->json([
            'todo' => $todos,
            'statusmolor'    => $statusMolor,
            'statuspending'    => $statusPending,
        ]);
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
                ->groupBy('tl_id')
                ->orderBy('tl_planstart','ASC');;

        if ($type == "1") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '<', Carbon::today())->where('tl_status', '=', 'Open');
            })->get();
        } elseif ($type == "2") {
            $data = $data->where(function ($q) {
                $q->where("tl_planstart", '<=', Carbon::today()->setTime(23, 59, 59));
                $q->where("tl_planend", '>=', Carbon::today());
            })->get();
        } elseif ($type == "3") {
            $data = $data->where(function ($q) {
                $q->where("tl_planstart", '<=', Carbon::tomorrow()->setTime(23, 59, 59))
            ->Where('tl_planend', '>=', Carbon::tomorrow());
            })->get();
        } elseif ($type == "4") {
            $data = $data->where(function ($q) {
                $q->where("tl_planstart", '<=', Carbon::tomorrow()->addDay()->setTime(23, 59, 59))
                ->where('tl_planend', '>=', Carbon::tomorrow()->addDay());
            })->get();
        } elseif ($type == "5") {
            $data = $data->where(function ($q) {
                $q->whereBetween("tl_planstart", [Carbon::now()->startOfWeek(Carbon::SUNDAY),Carbon::now()->endOfWeek(Carbon::SATURDAY)->setTime(23, 59, 59)]);
                $q->OrWhereBetween("tl_planend", [Carbon::now()->startOfWeek(Carbon::SUNDAY),Carbon::now()->endOfWeek(Carbon::SATURDAY)->setTime(23, 59, 59)]);
            })->get();
        } elseif ($type == "6") {
            $data = $data->where(function ($q) {
                $q->where('tl_planstart', '<=', Carbon::now()->lastOfMonth()->setTime(23, 59, 59))
               ->where('tl_planend', '>=', Carbon::now()->firstOfMonth());
            })->get();
        } else {
            $data = $data->where(function ($q) {
                $q->where('tl_status', '=', 'Pending');
            })->get();
        }

        $todos = array();

        foreach ($data as $key => $value) {
            $statusProgress = '';
            if ($value->tl_status == 'Finish') {
                $statusProgress = 'compleshed';
            } elseif ($value->tl_status == 'Pending') {
                $statusProgress = 'pending';
            } elseif ($value->tl_status == 'Open' && $value->tl_planend < Carbon::today() && $value->tl_progress < 100) {
                $statusProgress = 'overdue';
            } elseif ($value->tl_status == 'Open' && $value->tl_exestart == null) {
                $statusProgress = 'waiting';
            } elseif ($value->tl_status == 'Open' && $value->tl_exestart != null) {
                $statusProgress = 'working';
            }

            $arr = [
                'id'    => $value->tl_id,
                'title' => $value->tl_title,
                'start' => $value->tl_planstart,
                'end'   => $value->tl_planend,
                'status' => $value->tl_status,
                'allday' => (int)$value->tl_allday,
                'category' => $value->tl_category,
                'statuspinned' => $value->tli_todolist,
                'statusprogress'    => $statusProgress
            ];
            array_push($todos, $arr);
        }

        return response()->json([
            'todo' => $todos,
            'counttodo' => count($data),
        ]);
    }
    public function search_todo_project(Request $request)
    {
        $type = $request->filter;
        $statusPending = 0;
        $statusMolor = 0;
        $sortBy = null;
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
               ->groupBy('tl_id')
               ->when($sortBy, function ($query, $sortBy) {
                    return $query->orderBy($sortBy);
                                 
                }, function ($query) {
                    return $query->orderBy('tli_todolist','desc');
                })
                ->orderBy('tl_planstart','ASC');
               
        $dataPending = Todo::orderBy('tl_planstart', 'ASC')
            ->join('d_todolist_roles', 'tlr_todolist', 'tl_id')
            ->where('tlr_users', Auth::user()->us_id)
            ->where(function ($q) {
                $q->where('tl_status', '=', 'Pending');
                // $q->where("tl_planend", '<', Carbon::today())->where('tl_status', '=', 'Open');
            })->first();

        $dataMolor = Todo::orderBy('tl_planstart', 'ASC')
            ->join('d_todolist_roles', 'tlr_todolist', 'tl_id')
            ->where('tlr_users', Auth::user()->us_id)
            ->where(function ($q) {
                // $q->where('tl_status', '=', 'Pending');
                $q->where("tl_planend", '<', Carbon::today())->where('tl_status', '=', 'Open');
            })->first();

         if ($dataPending != null) {
             $statusPending = 1;
         }
        if ($dataMolor != null) {
            $statusMolor = 1;
        }


        if ($type == "1") {
            $data = $data->where(function ($q) {
                $q->where("tl_planend", '<', Carbon::today())->where('tl_status', '=', 'Open');
            })->get();
        } elseif ($type == "2") {
            $data = $data->where(function ($q) {
                $q->where("tli_users", '!=', null);
            })->get();
        } elseif ($type == "3") {
            $data = $data->where(function ($q) {
                $q->where("tl_planstart", '<=', Carbon::today()->setTime(23, 59, 59));
                $q->where("tl_planend", '>=', Carbon::today());
            })->get();
        } elseif ($type == "4") {
            $data = $data->where(function ($q) {
                $q->where("tl_planstart", '<=', Carbon::tomorrow()->setTime(23, 59, 59))
            ->Where('tl_planend', '>=', Carbon::tomorrow());
            })->get();
        } elseif ($type == "5") {
            $data = $data->where(function ($q) {
                $q->where("tl_planstart", '<=', Carbon::tomorrow()->addDay()->setTime(23, 59, 59))
                ->where('tl_planend', '>=', Carbon::tomorrow()->addDay());
            })->get();
        } elseif ($type == "6") {
            $data = $data->where(function ($q) {
                $q->whereBetween("tl_planstart", [Carbon::now()->startOfWeek(Carbon::SUNDAY),Carbon::now()->endOfWeek(Carbon::SATURDAY)->setTime(23, 59, 59)]);
                $q->OrWhereBetween("tl_planend", [Carbon::now()->startOfWeek(Carbon::SUNDAY),Carbon::now()->endOfWeek(Carbon::SATURDAY)->setTime(23, 59, 59)]);
            })->get();
        } elseif ($type == "7") {
            $data = $data->where(function ($q) {
                $q->where('tl_planstart', '<=', Carbon::now()->lastOfMonth()->setTime(23, 59, 59))
               ->where('tl_planend', '>=', Carbon::now()->firstOfMonth());
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

        $todos = array();

        foreach ($data as $key => $value) {
            $statusProgress = '';
            if ($value->tl_status == 'Finish') {
                $statusProgress = 'compleshed';
            } elseif ($value->tl_status == 'Pending') {
                $statusProgress = 'pending';
            } elseif ($value->tl_status == 'Open' && $value->tl_planend < Carbon::today() && $value->tl_progress < 100) {
                $statusProgress = 'overdue';
            } elseif ($value->tl_status == 'Open' && $value->tl_exestart == null) {
                $statusProgress = 'waiting';
            } elseif ($value->tl_status == 'Open' && $value->tl_exestart != null) {
                $statusProgress = 'working';
            }

            $arr = [
                'id'    => $value->tl_id,
                'title' => $value->tl_title,
                'start' => $value->tl_planstart,
                'end'   => $value->tl_planend,
                'status' => $value->tl_status,
                'allday' => (int)$value->tl_allday,
                'category' => $value->tl_category,
                'statuspinned' => $value->tli_todolist,
                'statusprogress'    => $statusProgress,            
            ];
            array_push($todos, $arr);
        }

        return response()->json([
            'todo' => $todos,
            'counttodo' => count($data),
            'project' => $Project,
            'countproject' => count($Project),
             'statusmolor'    => $statusMolor,
                'statuspending'    => $statusPending,
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

            if($request->allday == '1' || $request->allday == 1){

                $dateStart = Carbon::parse($request->planstart)->setTime(00,00,00);
                $dateEnd = Carbon::parse($request->planend)->setTime(23,59,59);
            }else{
                $dateStart = Carbon::parse($request->planstart)->format('Y-m-d H:i:s');
                $dateEnd = Carbon::parse($request->planend)->format('Y-m-d H:i:s');

            }

            $todo->tl_title         = $request->title;
            $todo->tl_category      = $request->category;
            $todo->tl_project       = $project;
            $todo->tl_desc          = $request->desc;
            $todo->tl_status        = 'Open';
            $todo->tl_progress      = 0;
            $todo->tl_planstart     = $dateStart;
            $todo->tl_planend       = $dateEnd;
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
                $namaProject = DB::table('d_project')->where('p_id',$request->project)->first();                
                $masterNotif = DB::table('m_notifications')->where('n_id','3')->first();
                $memberProject = DB::table('d_project_member')->where('mp_project', $request->project)->get();
                foreach ($memberProject as $key => $value) {
                    DB::table('d_todolist_roles')->insert([
                        'tlr_users' => $value->mp_user,
                        'tlr_todolist' => $todo->tl_id,
                        'tlr_role' => $value->mp_role,
                        'tlr_own' => 'P',
                    ]);

                    if($value->mp_user != Auth::user()->us_id){
                        DB::table('d_notifications_todolist')->insert([
                        'nt_notifications' => '3',
                        'nt_todolist' => $todo->tl_id,
                        'nt_fromuser' => Auth::user()->us_id,
                        'nt_project' => $request->project,
                        'nt_touser' => $value->mp_user, 
                        'nt_fromuser' => Auth::user()->us_id,
                        'nt_status' => 'N',
                        'nt_created' => Carbon::now('Asia/Jakarta'),
                        ]);
                        $send_notif = new TokenController();
                        $send_notif->sendNotif(''.$masterNotif->n_title .' - Todolist',$request->title . ' ' . $masterNotif->n_message . ' ' . $namaProject->p_name,'12');
                    }
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
                ->orderBy('tlr_role', 'ASC')
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

            array_push($dataFile, $arr);
        }


        $statusKita = DB::table('d_todolist_roles')
                    ->where('tlr_todolist', $request->todolist)
                    ->where('tlr_users', Auth::user()->us_id)
                    ->orderBy('tlr_role', 'ASC')
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
            if ($request->progress == 100 || $request->progress == '100') {
                DB::table('d_todolist')->where('tl_id', $request->todolist)->update([
                    'tl_status' => 'Finish',
                    'tl_exeend' => Carbon::now('Asia/Jakarta'),
                    'tl_progress' => $request->progress,
                ]);
            } else {
                DB::table('d_todolist')->where('tl_id', $request->todolist)->update([
                'tl_progress' => $request->progress,
            ]);
            }
            DB::table('d_todolist_timeline')->insert([
                'tlt_todolist' => $request->todolist,
                'tlt_user' => Auth::user()->us_id,
                'tlt_progress' => $request->progress,
                'tlt_note' => $request->catatan,
                'tlt_created'=> Carbon::now('Asia/Jakarta'),
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
                    $subId = DB::table('d_todolist_action')->where('tla_todolist', $request->todo)->max('tla_number') + 1;
                    DB::table('d_todolist_action')->insert([
                        'tla_todolist' => $request->todo,
                        'tla_number' => $subId,
                        'tla_title' => $request->title,
                        'tla_created' => Carbon::now('Asia/Jakarta'),
                        'tla_createduser' => Auth::user()->us_id,
                    ]);

                    break;

                case 'Ready':
                     $subId = DB::table('d_todolist_ready')->where('tlr_todolist', $request->todo)->max('tlr_number') + 1;
                    DB::table('d_todolist_ready')->insert([
                        'tlr_todolist' => $request->todo,
                        'tlr_number' => $subId,
                        'tlr_title' => $request->title,
                        'tlr_created' => Carbon::now('Asia/Jakarta'),
                        'tlr_createduser' => Auth::user()->us_id,
                    ]);
                    break;

                case 'Normal':
                    $subId = DB::table('d_todolist_normal')->where('tln_todolist', $request->todo)->max('tln_number') + 1;
                    DB::table('d_todolist_normal')->insert([
                        'tln_todolist' => $request->todo,
                        'tln_number' => $subId,
                        'tln_title' => $request->title,
                        'tln_created' => Carbon::now('Asia/Jakarta'),
                        'tln_createduser' => Auth::user()->us_id,
                    ]);
                    break;

                case 'Done':
                    $subId = DB::table('d_todolist_done')->where('tld_todolist', $request->todo)->max('tld_number') + 1;
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
                    ->where('tlr_own','T')
                    ->orderBy('tlr_role', 'ASC')
                    ->groupBy('tlr_users')
                    ->get();
        $documentTodo = DB::table('d_todolist_attachment')
                        ->where('tla_todolist', $id)
                        ->get();
        $statusKita = DB::table('d_todolist_roles')->where('tlr_users', Auth::user()->us_id)->orderBy('tlr_role', 'ASC')->first();

        return response()->json([
            'todo' => $todo,
            'member_todo' => $MemberTodo,
            'document_todo' => $documentTodo,
            'statuskita' => $statusKita,
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
            $cekAdminTodo = DB::table('d_todolist_roles')->where('tlr_todolist', $request->todo)->where('tlr_users', Auth::user()->us_id)->orderBy('tlr_role', 'ASC')->first();
            if ($cekAdminTodo->tlr_role == '1' || $cekAdminTodo->tlr_role == '2' || $cekAdminTodo->tlr_role == 1 || $cekAdminTodo->tlr_role == 2) {
                $autoValidationUser = Auth::user()->us_id;
                $autoValidationDate =  Carbon::now('Asia/Jakarta');
            } else {
                $autoValidationUser = null;
                $autoValidationDate = null;
            }
            switch ($request->type) {
                case 'Action':
                    $cekData = DB::table('d_todolist_action')
                              ->where('tla_todolist', $request->todo)
                              ->where('tla_number', $id)
                              ->first();

                    if ($cekData->tla_executed == null) {
                        $executedDate = Carbon::now('Asia/Jakarta');
                        $executedUser = Auth::user()->us_id;
                        $status = 'selesai';
                        $validationDate = $autoValidationDate;
                        $validationUser = $autoValidationUser;
                    } else {
                        $executedDate = null;
                        $executedUser = null;
                        $status = 'belum selesai';
                        $validationDate = null;
                        $validationUser = null;
                    }

                    DB::table('d_todolist_action')
                    ->where('tla_todolist', $request->todo)
                    ->where('tla_number', $id)
                    ->update([
                        'tla_executed' => $executedDate,
                        'tla_executeduser'  => $executedUser,
                        'tla_validationuser' => $validationUser,
                        'tla_validation' => $validationDate,
                    ]);

                break;
                case 'Done':

                    $cekData = DB::table('d_todolist_done')
                                ->where('tld_todolist', $request->todo)
                                ->where('tld_number', $id)
                                ->first();

                   if ($cekData->tld_executed == null) {
                       $executedDate = Carbon::now('Asia/Jakarta');
                       $executedUser = Auth::user()->us_id;
                       $status = 'selesai';
                       $validationDate = $autoValidationDate;
                       $validationUser = $autoValidationUser;
                   } else {
                       $executedDate = null;
                       $executedUser = null;
                       $status = 'belum selesai';
                       $validationDate = null;
                       $validationUser = null;
                   }

                     DB::table('d_todolist_done')
                    ->where('tld_todolist', $request->todo)
                    ->where('tld_number', $id)
                    ->update([
                        'tld_executed' => $executedDate,
                        'tld_executeduser' => $executedUser,
                        'tld_validationuser' => $validationUser,
                        'tld_validation' => $validationDate,
                    ]);


                break;
                case 'Normal':

                    $cekData = DB::table('d_todolist_normal')->where('tln_todolist', $request->todo)->where('tln_number', $id)->first();

                    if ($cekData->tln_executed == null) {
                        $executedDate = Carbon::now('Asia/Jakarta');
                        $executedUser = Auth::user()->us_id;
                        $status = 'selesai';
                        $validationDate = $autoValidationDate;
                        $validationUser = $autoValidationUser;
                    } else {
                        $executedDate = null;
                        $executedUser = null;
                        $status = 'belum selesai';
                        $validationDate = null;
                        $validationUser = null;
                    }

                     DB::table('d_todolist_normal')
                    ->where('tln_todolist', $request->todo)
                    ->where('tln_number', $id)
                    ->update([
                        'tln_executed' => $executedDate,
                        'tln_executeduser' => $executedUser,
                        'tln_validation' => $validationDate,
                        'tln_validationuser' => $validationUser,
                    ]);

                break;
                case 'Ready':

                    $cekData = DB::table('d_todolist_ready')->where('tlr_todolist', $request->todo)->where('tlr_number', $id)->first();
                  if ($cekData->tlr_executed == null) {
                      $executedDate = Carbon::now('Asia/Jakarta');
                      $executedUser = Auth::user()->us_id;
                      $status = 'selesai';
                      $validationDate = $autoValidationDate;
                      $validationUser = $autoValidationUser;
                  } else {
                      $executedDate = null;
                      $executedUser = null;
                      $status = 'belum selesai';
                      $validationDate = null;
                      $validationUser = null;
                  }
                     DB::table('d_todolist_ready')
                    ->where('tlr_todolist', $request->todo)
                    ->where('tlr_number', $id)
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

            $cekDataSebelumnya = DB::table('d_todolist')->where('tl_id', $id)->first();
            if ($request->category != '1') {
                $ProjectTodoMember = DB::table('d_todolist_roles')
                                ->where('tlr_todolist', $id)
                                ->where('tlr_own', 'P')
                                ->where('tlr_role', '!=', 1)
                                ->delete();
                if($cekDataSebelumnya->tl_project != null){
                    $masterNotif = DB::table('m_notifications')->where('n_id','4')->first();
                    $namaProject = DB::table('d_project')->where('p_id',$cekDataSebelumnya->tl_project)->first();
                    $memberProject = DB::table('d_project_member')->where('mp_project',$cekDataSebelumnya->tl_project)->where('mp_user','!=',Auth::user()->us_id)->get();
                    foreach ($memberProject as $key => $person) {
                         DB::table('d_notifications_todolist')->insert([
                            'nt_notifications' => '4',
                            'nt_todolist' => $id,
                            'nt_fromuser' => Auth::user()->us_id,
                            'nt_touser' => $person->mp_user,
                            'nt_project' => $cekDataSebelumnya->tl_project,
                            'nt_status' => 'N',
                            'nt_created' => Carbon::now('Asia/Jakarta'),
                        ]);

                         $send_notif = new TokenController();
                         $send_notif->sendNotif(''.$masterNotif->n_title .' - Todolist',$request->title . ' ' . $masterNotif->n_message . ' ' . $namaProject->p_name,$person->mp_user);

                    }
                }
                $projetLeader = DB::table('d_todolist_roles')
                                ->where('tlr_own', 'P')->where('tlr_todolist',$id)->where('tlr_role', 1)->get();
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
                if($cekDataSebelumnya->tl_project == null){
                    $masterNotif = DB::table('m_notifications')->where('n_id','3')->first();
                    $namaProject = DB::table('d_project')->where('p_id',$request->project)->first();

                    $memberProject = DB::table('d_project_member')->where('mp_project',$request->project)->where('mp_user','!=',Auth::user()->us_id)->get();
                    foreach ($memberProject as $key => $member) {
                        DB::table('d_notifications_todolist')->insert([
                            'nt_notifications' => '3',
                            'nt_todolist' => $id,
                            'nt_fromuser' => Auth::user()->us_id,
                            'nt_touser' => $member->mp_user,
                            'nt_project' => $request->project,
                            'nt_status' => 'N',
                            'nt_created' => Carbon::now('Asia/Jakarta'),
                        ]);

                        $send_notif = new TokenController();
                        $send_notif->sendNotif(''.$masterNotif->n_title .' - Todolist',$request->title . ' ' . $masterNotif->n_message . ' ' . $namaProject->p_name,$member->mp_user);
                    }
                }else{
                    if($cekDataSebelumnya->tl_project != $request->project){
                        $masterNotifOut = DB::table('m_notifications')->where('n_id','4')->first();
                        $namaProjectOld = DB::table('d_project')->where('p_id',$cekDataSebelumnya->tl_project)->first();

                         $projectMemberOld = DB::table('d_project_member')->where('mp_project',$cekDataSebelumnya->tl_project)->where('mp_user','!=',Auth::user()->us_id)->get();
                         foreach ($projectMemberOld as $key => $oldmember) {
                            DB::table('d_notifications_todolist')->insert([
                                'nt_notifications' => '4',
                                'nt_todolist' => $id,
                                'nt_fromuser' => Auth::user()->us_id,
                                'nt_touser' => $oldmember->mp_user,
                                'nt_project' => $cekDataSebelumnya->tl_project,
                                'nt_status' => 'N',
                                'nt_created' => Carbon::now('Asia/Jakarta'),
                            ]);
                            $send_notif = new TokenController();
                            $send_notif->sendNotif(''.$masterNotifOut->n_title .' - Todolist',$request->title . ' ' . $masterNotifOut->n_message . ' ' . $namaProjectOld->p_name,$oldmember->mp_user);
                        }

                        $projectMemberNew = DB::table('d_project_member')->where('mp_project',$request->project)->where('mp_user','!=',Auth::user()->us_id)->get();
                        $masterNotifIn = DB::table('m_notifications')->where('n_id','3')->first();
                        $namaProjectNew = DB::table('d_project')->where('p_id',$request->tl_project)->first();
                        foreach ($projectMemberNew as $key => $newmember) {
                            DB::table('d_notifications_todolist')->insert([
                                'nt_notifications' => '3',
                                'nt_todolist' => $id,
                                'nt_fromuser' => Auth::user()->us_id,
                                'nt_touser' => $newmember->mp_user,
                                'nt_project' => $request->project,
                                'nt_status' => 'N',
                                'nt_created' => Carbon::now('Asia/Jakarta'),
                            ]);
                        $send_notif = new TokenController();
                        $send_notif->sendNotif(''.$masterNotifIn->n_title .' - Todolist',$request->title . ' ' . $masterNotifIn->n_message . ' ' . $namaProjectOld->p_name,$newmember->mp_user);
                        }


                    }    
                }
                
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

            $cekMember = DB::table('d_todolist_roles')->where('tlr_users', $cekUser->us_id)->where('tlr_todolist', $request->todolist)->where('tlr_own','T')->first();
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
            DB::table('d_notifications_todolist')->insert([
                'nt_notifications' => '1',
                'nt_todolist' => $request->todolist,
                'nt_fromuser' => Auth::user()->us_id,
                'nt_touser' => $cekUser->us_id,
                'nt_project' => null,
                'nt_status' => 'N',
                'nt_created' => Carbon::now('Asia/Jakarta'),
            ]);            
            $namaNotif = DB::table('m_notifications')->where('n_id','1')->first();
            $namaTodo = DB::table('d_todolist')->where('tl_id',$request->todolist)->first();
            $send_notif = new TokenController();
            $send_notif->sendNotif(''. $namaNotif->n_title .' - Todolist',Auth::user()->us_name. ' '. $namaNotif->n_message . ' ' . $namaTodo->tl_title,$cekUser->us_id);

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
            DB::table('d_notifications_todolist')->insert([
                'nt_notifications' => '2',
                'nt_todolist' => $request->todolist,
                'nt_fromuser' => Auth::user()->us_id,
                'nt_touser' => $request->member,
                'nt_project' => null,
                'nt_status' => 'N',
                'nt_created' => Carbon::now('Asia/Jakarta'),
            ]);
            $getMember = DB::table('m_users')->where('us_id',$request->member)->first();
            $masterNotif = DB::table('m_notifications')->where('n_id','2')->first();
            $getTodo = DB::table('d_todolist')->where('tl_id',$request->todolist)->first();
            $send_notif = new TokenController();
            $send_notif->sendNotif(''.$masterNotif->n_title .' - Todolist',Auth::user()->us_name . ' ' . $masterNotif->n_message . ' '.$getTodo->tl_title,$request->member);

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
            $notifikasi = DB::table('m_notifications')->where('n_id','2')->first();
            $todolist = DB::table('d_todolist')->where('tl_id',$todo)->first();
            $getPeserta = DB::table('m_users')->where('us_id',$user)->first();
            $send_notif = new TokenController();
            $send_notif->sendNotif(''.$notifikasi->n_title.' - Todolist', Auth::user()->us_name .' '. $notifikasi->n_message . ' ' .$todolist->tl_title ,$user);

            DB::table('d_notifications_todolist')->insert([
                'nt_notifications' => '2',
                'nt_fromuser' => Auth::user()->us_id,
                'nt_todolist' => $todo,
                'nt_touser' => $user,
                'nt_status' => 'N',
                'nt_created' => Carbon::now('Asia/Jakarta'),
            ]);
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
    public function todo_normal($id)
    {
        $todoReady = DB::table('d_todolist_normal')->where('tln_todolist', $id)->get();
        return response()->json([
            'todo_normal' => $todoReady,
        ]);
    }
    public function todo_done($id)
    {
        $todoReady = DB::table('d_todolist_done')->where('tld_todolist', $id)->get();
        return response()->json([
            'todo_done' => $todoReady,
        ]);
    }
    public function validation_listtodo(Request $request)
    {
        DB::BeginTransaction();
        try {
            $id = $request->id;
            switch ($request->type) {
                case 'Action':

                    $cekData = DB::table('d_todolist_action')
                              ->where('tla_todolist', $request->todo)
                              ->where('tla_number', $id)
                              ->first();

                    if ($cekData->tla_validation == null) {
                        $done = Carbon::now('Asia/Jakarta');
                        $status = 'validation';
                    } else {
                        $done = null;
                        $status = 'belum validation';
                    }

                    DB::table('d_todolist_action')
                    ->where('tla_todolist', $request->todo)
                    ->where('tla_number', $id)
                    ->update([
                        'tla_validation' => $done,
                    ]);

                break;
                case 'Done':

                    $cekData = DB::table('d_todolist_done')
                                ->where('tld_todolist', $request->todo)
                                ->where('tld_number', $id)
                                ->first();

                    if ($cekData->tld_validation == null) {
                        $done = Carbon::now('Asia/Jakarta');
                        $status = 'validation';
                    } else {
                        $done = null;
                        $status = 'belum validation';
                    }

                     DB::table('d_todolist_done')
                    ->where('tld_todolist', $request->todo)
                    ->where('tld_number', $id)
                    ->update([
                        'tld_validation' => $done,
                    ]);


                break;
                case 'Normal':

                    $cekData = DB::table('d_todolist_normal')->where('tln_todolist', $request->todo)->where('tln_number', $id)->first();

                    if ($cekData->tln_validation == null) {
                        $done = Carbon::now('Asia/Jakarta');
                        $status = 'validation';
                    } else {
                        $done = null;
                        $status = 'belum validation';
                    }

                     DB::table('d_todolist_normal')
                    ->where('tln_todolist', $request->todo)
                    ->where('tln_number', $id)
                    ->update([
                        'tln_validation' => $done,
                    ]);

                break;
                case 'Ready':

                    $cekData = DB::table('d_todolist_ready')->where('tlr_todolist', $request->todo)->where('tlr_number', $id)->first();
                    if ($cekData->tlr_validation == null) {
                        $done = Carbon::now('Asia/Jakarta');
                        $status = 'validation';
                    } else {
                        $done = null;
                        $status = 'belum validation';
                    }

                     DB::table('d_todolist_ready')
                    ->where('tlr_todolist', $request->todo)
                    ->where('tlr_number', $id)
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
    public function started_todo(Request $request)
    {
        setlocale(LC_TIME, 'IND');
        DB::BeginTransaction();
        try {
            $namaTodo = DB::table('d_todolist')->where('tl_id',$request->todo)->first();

            switch ($request->type) {
                case 'baru mengerjakan':
                     DB::table('d_todolist')->where('tl_id', $request->todo)->update([
                        'tl_exestart' => Carbon::now('Asia/Jakarta'),
                    ]);
                     $masterNotifstart = DB::table('m_notifications')->where('n_id','7')->first();
                     // return response()->json($masterNotifstart);
                     $todoMember = DB::table('d_todolist_roles')->where('tlr_todolist',$request->todo)->groupBy('tlr_users')->get();
                     foreach ($todoMember as $key => $value) {
                         DB::table('d_notifications_todolist')->insert([
                            'nt_notifications' => '7',
                            'nt_todolist' => $request->todo,
                            'nt_project' => null,
                            'nt_fromuser' => Auth::user()->us_id,
                            'nt_touser' => $value->tlr_users,
                            'nt_status' => 'N',
                            'nt_created' => Carbon::now('Asia/Jakarta'),
                         ]);

                         $send_notif = new TokenController();
                         $send_notif->sendNotif(''.$masterNotifstart->n_title .' - Todolist',$namaTodo->tl_title . ' ' . $masterNotifstart->n_message,$value->tlr_users);
                     }
                    break;
                case 'pending':
                         DB::table('d_todolist')->where('tl_id', $request->todo)->update([
                            'tl_status' => 'Pending',
                        ]);
                         $masterNotifpending = DB::table('m_notifications')->where('n_id','8')->first();

                         $todoMember = DB::table('d_todolist_roles')->where('tlr_todolist',$request->todo)->groupBy('tlr_users')->get();
                     foreach ($todoMember as $key => $value) {
                         DB::table('d_notifications_todolist')->insert([
                            'nt_notifications' => '8',
                            'nt_todolist' => $request->todo,
                            'nt_project' => null,
                            'nt_fromuser' => Auth::user()->us_id,
                            'nt_touser' => $value->tlr_users,
                            'nt_status' => 'N',
                            'nt_created' => Carbon::now('Asia/Jakarta'),
                         ]);
                         $send_notif = new TokenController();
                         $send_notif->sendNotif(''.$masterNotifpending->n_title .' - Todolist',$namaTodo->tl_title . ' ' . $masterNotifpending->n_message,$value->tlr_users);
                     }
                    break;
                case 'selesai':
                        $masterNotifdone = DB::table('m_notifications')->where('n_id','9')->first();
                         DB::table('d_todolist')->where('tl_id', $request->todo)->update([
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
                         $todoMember = DB::table('d_todolist_roles')->where('tlr_todolist',$request->todo)->groupBy('tlr_users')->get();
                     foreach ($todoMember as $key => $value) {
                         DB::table('d_notifications_todolist')->insert([
                            'nt_notifications' => '9',
                            'nt_todolist' => $request->todo,
                            'nt_project' => null,
                            'nt_fromuser' => Auth::user()->us_id,
                            'nt_touser' => $value->tlr_users,
                            'nt_status' => 'N',
                            'nt_created' => Carbon::now('Asia/Jakarta'),
                         ]);
                         $send_notif = new TokenController();
                         $send_notif->sendNotif(''.$masterNotifdone->n_title .' - Todolist',$namaTodo->tl_title . ' ' . $masterNotifdone->n_message,$value->tlr_users);
                     }
                    break;
                case 'mulai mengerjakan kembali':
                        $masterNotifstart = DB::table('m_notifications')->where('n_id','7')->first();

                         DB::table('d_todolist')->where('tl_id', $request->todo)->update([
                             'tl_status' => 'Open',
                        ]);
                          $todoMember = DB::table('d_todolist_roles')->where('tlr_todolist',$request->todo)->groupBy('tlr_users')->get();
                     foreach ($todoMember as $key => $value) {
                         DB::table('d_notifications_todolist')->insert([
                            'nt_notifications' => '7',
                            'nt_todolist' => $request->todo,
                            'nt_project' => null,
                            'nt_fromuser' => Auth::user()->us_id,
                            'nt_touser' => $value->tlr_users,
                            'nt_status' => 'N',
                            'nt_created' => Carbon::now('Asia/Jakarta'),
                         ]);
                         $send_notif = new TokenController();
                         $send_notif->sendNotif(''.$masterNotifstart->n_title .' - Todolist',$namaTodo->tl_title . ' ' . $masterNotifstart->n_message,$value->tlr_users);
                     }
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

    public function todo_activity(Request $request)
    {
        $TodoActivity = DB::table('d_todolist_timeline')
                        ->join('m_users', 'tlt_user', 'us_id')
                        ->where('tlt_todolist', $request->todolist)
                        ->orderBy('tlt_id', 'Desc')
                        ->get();
        return response()->json($TodoActivity);
    }

    public function delete_todo(Request $request)
    {
        DB::BeginTransaction();
        try {
            DB::table('d_todolist')->where('tl_id', $request->todolist)->delete();
            DB::table('d_todolist_action')->where('tla_todolist', $request->todolist)->delete();
            DB::table('d_todolist_attachment')->where('tla_todolist', $request->todolist)->delete();
            DB::table('d_todolist_done')->where('tld_todolist', $request->todolist)->delete();
            DB::table('d_todolist_important')->where('tli_todolist', $request->todolist)->delete();
            DB::table('d_todolist_normal')->where('tln_todolist', $request->todolist)->delete();
            DB::table('d_todolist_ready')->where('tlr_todolist', $request->todolist)->delete();
            DB::table('d_todolist_roles')->where('tlr_todolist', $request->todolist)->delete();
            DB::table('d_todolist_timeline')->where('tlt_todolist', $request->todolist)->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
}
