<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Recommendation;
use App\Dataset;
use App\DecisionTree;
use Session;
use Illuminate\Validation\Rule;

class RecommendationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('role:kaprodi,kjfd');
    }
    
    public function index(Request $request)
    {
        // $recommendations = Recommendation::latest()->paginate(15);
        if ($request->filled('search')) {
            $search = Recommendation::query();
            if($request->search != ''){
                $search->where('NIM', 'LIKE', $request->search.'%');
            }
            $recommendations = $search->latest()->paginate(15);
        }else{
            $recommendations = Recommendation::query()->latest()->paginate(15);
            if($recommendations->isEmpty()){
                $recommendations = NULL;
            }
        }
        return view('pages.recommendation.index', compact(['recommendations']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $selected_tree = DecisionTree::latest()->first();
        if(!$selected_tree){
            $selected_tree = NULL;
        }
        $tree = collect(DecisionTree::latest()->first())->isEmpty() ? FALSE:TRUE;
        return view('pages.recommendation.create', compact(['tree', 'selected_tree']));
    }

    public function createBYtree($id)
    {
        $selected_tree = DecisionTree::where('tree_id', $id)->first();
        if(!$selected_tree){
            $selected_tree = NULL;
        }
        $tree = collect(DecisionTree::where('tree_id', $id)->get())->isEmpty() ? FALSE:TRUE;
        return view('pages.recommendation.create', compact(['tree', 'selected_tree']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function transformGrade($grade){
        if($grade == 'A' || $grade == 'a'){
            return 'SB';
        }elseif($grade == 'B+' || $grade == 'b+'){
            return 'B';
        }elseif($grade == 'B' || $grade == 'b'){
            return 'B';
        }elseif($grade == 'C+' || $grade == 'c+'){
            return 'C';
        }elseif($grade == 'C' || $grade == 'c'){
            return 'C';
        }elseif($grade == 'D+' || $grade == 'd+'){
            return 'K';
        }elseif($grade == 'D' || $grade == 'd'){
            return 'K';
        }elseif($grade == 'E' || $grade == 'e'){
            return 'K';
        }elseif($grade == 'K' || $grade == 'k'){
            return 'K';
        }elseif($grade == 'NULL' || $grade == '?' || $grade == '' || $grade == NULL){
            return 'N';
        }else{
            return NULL;
        }
    }

    public function store(Request $request)
    {
        $check = collect($request->all())->except(['_token', 'tree_id', 'NIM']);
        $check_counter = 0;
        foreach($check as $mk){
            if(!$mk){
                $check_counter += 1;
            }
        }
        
        $request->validate([
            'NIM' => 'required|min:10|max:16|unique:recommendations,NIM',
            'mk_PGI' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_SIGD1' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_SIGD2' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_SIGL' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_SPK' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_ABD' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_BDT' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_DBD' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_DM' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_DW' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_KB' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_PBD' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_ADSI' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_DPSI' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_IPSI' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_PABW' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_PBPU' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_PPP' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_SE' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_PL' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_DDAP' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_DIAP' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_EPAP' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_EASI' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_MO' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_MITI' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_MLTI' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_MP' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_MPSI' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_MRS' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_MR' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_PPB' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_PSSI' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_TKTI' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_EA' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_SBF' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
            'mk_MHP' => Rule::in(['A','a','B+', 'b+', 'B', 'b', 'C+', 'c+', 'C', 'c', 'D+', 'd+', 'D', 'd', 'E', 'e', 'K', 'k', NULL, 'NULL', 'null', '']),
        ],
        [
            'NIM.required' => 'NIM harus diisi.',
            'NIM.min' => 'Minimal panjang NIM adalah 10.',
            'NIM.max' => 'Maksimal panjang NIM adalah 16.',
            'NIM.unique' => 'Mahasiswa dengan NIM ini telah mendapatkan rekomendasi.',
            'mk_PGI.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_SIGD1.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_SIGD2.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_SIGL.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_SPK.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_ABD.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_BDT.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_DBD.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_DM.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_DW.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_KB.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_PBD.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_ADSI.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_DPSI.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_IPSI.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_PABW.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_PBPU.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_PPP.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_SE.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_PL.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_DDAP.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_DIAP.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_EPAP.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_EASI.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_MO.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_MITI.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_MLTI.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_MP.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_MPSI.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_MRS.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_MR.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_PPB.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_PSSI.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_TKTI.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_EA.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_SBF.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
            'mk_MHP.in' => 'Nilai yang diijinkan adalah: A, B+, B, C+, C, D+, D, E, K, NULL, " " atau (kosong).',
        ]);
        
        $recommendation = new Recommendation;
        if($check_counter == 37){
            return redirect()->back()->with('empty_error', "Semua nilai mata kuliah tidak boleh kosong!")->withInput($request->input());
        }else{
            $recommendation = Recommendation::create([
                'NIM' => $request->NIM,
                'skripsi_bidang_rekomendasi' => NULL,
                'mk_PGI' => $this->transformGrade($request->mk_PGI),
                'mk_SIGD1' => $this->transformGrade($request->mk_SIGD1),
                'mk_SIGD2' => $this->transformGrade($request->mk_SIGD2),
                'mk_SIGL' => $this->transformGrade($request->mk_SIGL),
                'mk_SPK' => $this->transformGrade($request->mk_SPK),
                'mk_ABD' => $this->transformGrade($request->mk_ABD),
                'mk_BDT' => $this->transformGrade($request->mk_BDT),
                'mk_DBD' => $this->transformGrade($request->mk_DBD),
                'mk_DM' => $this->transformGrade($request->mk_DM),
                'mk_DW' => $this->transformGrade($request->mk_DW),
                'mk_KB' => $this->transformGrade($request->mk_KB),
                'mk_PBD' => $this->transformGrade($request->mk_PBD),
                'mk_ADSI' => $this->transformGrade($request->mk_ADSI),
                'mk_DPSI' => $this->transformGrade($request->mk_DPSI),
                'mk_IPSI' => $this->transformGrade($request->mk_IPSI),
                'mk_PABW' => $this->transformGrade($request->mk_PABW),
                'mk_PBPU' => $this->transformGrade($request->mk_PBPU),
                'mk_PPP' => $this->transformGrade($request->mk_PPP),
                'mk_SE' => $this->transformGrade($request->mk_SE),
                'mk_PL' => $this->transformGrade($request->mk_PL),
                'mk_DDAP' => $this->transformGrade($request->mk_DDAP),
                'mk_DIAP' => $this->transformGrade($request->mk_DIAP),
                'mk_EPAP' => $this->transformGrade($request->mk_EPAP),
                'mk_EASI' => $this->transformGrade($request->mk_EASI),
                'mk_MO' => $this->transformGrade($request->mk_MO),
                'mk_MITI' => $this->transformGrade($request->mk_MITI),
                'mk_MLTI' => $this->transformGrade($request->mk_MLTI),
                'mk_MP' => $this->transformGrade($request->mk_MP),
                'mk_MPSI' => $this->transformGrade($request->mk_MPSI),
                'mk_MRS' => $this->transformGrade($request->mk_MRS),
                'mk_MR' => $this->transformGrade($request->mk_MR),
                'mk_PPB' => $this->transformGrade($request->mk_PPB),
                'mk_PSSI' => $this->transformGrade($request->mk_PSSI),
                'mk_TKTI' => $this->transformGrade($request->mk_TKTI),
                'mk_EA' => $this->transformGrade($request->mk_EA),
                'mk_SBF' => $this->transformGrade($request->mk_SBF),
                'mk_MHP' => $this->transformGrade($request->mk_MHP)
            ]);
        }
        // dd($recommendation);
        $table = 'recommendations';
        $decision_tree = new DecisionTreeController;
        $decision_tree->useModel($request->NIM, $table, $request->tree_id);

        return redirect('admin/recommendation/'.$request->NIM)->with('pp_success', "Data telah di pre-process dan berhasil ditambahkan");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($NIM)
    {
        $p_check = ["PGI" => "mk_PGI", 
        "SIGD1" => "mk_SIGD1", 
        "SIGD2" => "mk_SIGD2", 
        "SIGL" => "mk_SIGL", 
        "SPK" => "mk_SPK", 
        "ABD" => "mk_ABD", 
        "BDT" => "mk_BDT", 
        "DBD" => "mk_DBD", 
        "DM" => "mk_DM", 
        "DW" => "mk_DW", 
        "KB" => "mk_KB", 
        "PBD" => "mk_PBD", 
        "ADSI" => "mk_ADSI", 
        "DPSI" => "mk_DPSI", 
        "IPSI" => "mk_IPSI", 
        "PABW" => "mk_PABW", 
        "PBPU" => "mk_PBPU", 
        "PPP" => "mk_PPP", 
        "SE" => "mk_SE", 
        "PL" => "mk_PL", 
        "DDAP" => "mk_DDAP", 
        "DIAP" => "mk_DIAP", 
        "EPAP" => "mk_EPAP", 
        "EASI" => "mk_EASI", 
        "MO" => "mk_MO", 
        "MITI" => "mk_MITI", 
        "MLTI" => "mk_MLTI", 
        "MP" => "mk_MP", 
        "MPSI" => "mk_MPSI", 
        "MRS" => "mk_MRS", 
        "MR" => "mk_MR", 
        "PPB" => "mk_PPB", 
        "PSSI" => "mk_PSSI", 
        "TKTI" => "mk_TKTI", 
        "EA" => "mk_EA", 
        "SBF" => "mk_SBF", 
        "MHP" => "mk_MHP"];

        $pointer = ["mk_PGI" => "PGI", 
        "mk_SIGD1" => "SIGD1", 
        "mk_SIGD2" => "SIGD2", 
        "mk_SIGL" => "SIGL", 
        "mk_SPK" => "SPK", 
        "mk_ABD" => "ABD", 
        "mk_BDT" => "BDT", 
        "mk_DBD" => "DBD", 
        "mk_DM" => "DM", 
        "mk_DW" => "DW", 
        "mk_KB" => "KB", 
        "mk_PBD" => "PBD", 
        "mk_ADSI" => "ADSI", 
        "mk_DPSI" => "DPSI", 
        "mk_IPSI" => "IPSI", 
        "mk_PABW" => "PABW", 
        "mk_PBPU" => "PBPU", 
        "mk_PPP" => "PPP", 
        "mk_SE" => "SE", 
        "mk_PL" => "PL", 
        "mk_DDAP" => "DDAP", 
        "mk_DIAP" => "DIAP", 
        "mk_EPAP" => "EPAP", 
        "mk_EASI" => "EASI", 
        "mk_MO" => "MO", 
        "mk_MITI" => "MITI", 
        "mk_MLTI" => "MLTI", 
        "mk_MP" => "MP", 
        "mk_MPSI" => "MPSI", 
        "mk_MRS" => "MRS", 
        "mk_MR" => "MR", 
        "mk_PPB" => "PPB", 
        "mk_PSSI" => "PSSI", 
        "mk_TKTI" => "TKTI", 
        "mk_EA" => "EA", 
        "mk_SBF" => "SBF", 
        "mk_MHP" => "MHP"];
        $recommendation = Recommendation::where('NIM', $NIM)->first();
        $rec = collect($recommendation);
        $g_sb = [];
        $g_b = [];
        $g_c = [];
        $g_k = [];
        $g_n = [];
        foreach($rec as $key => $value){
            if(in_array($key, $p_check)){
                if($value == 'SB'){
                    array_push($g_sb, $pointer[$key]);
                }elseif($value == 'B'){
                    array_push($g_b, $pointer[$key]);
                }elseif($value == 'C'){
                    array_push($g_c, $pointer[$key]);
                }elseif($value == 'K'){
                    array_push($g_k, $pointer[$key]);
                }elseif($value == 'N'){
                    array_push($g_n, $pointer[$key]);
                }
            }
        }
        $ggrade = collect([
            'Sangat Baik' => $g_sb,
            'Baik' => $g_b,
            'Cukup' => $g_c,
            'Kurang' => $g_k,
            'Tidak Ada' => $g_n,
        ]);
        // dd($ggrade);
        $researches = Dataset::where('skripsi_bidang', $recommendation->skripsi_bidang_rekomendasi)->paginate(15);
        return view('pages.recommendation.show', compact(['recommendation', 'researches', 'ggrade']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
