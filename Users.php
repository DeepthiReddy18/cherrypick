<?php

class Users extends Controller {

    public function __construct(){
        $this -> userModel = $this -> model('User');
    }

    public function index(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $departments = $this -> userModel -> getDepartments();
        $usergroups =$this-> userModel -> getUserGroups();
        $data=[
            "departments"=> $departments,
            "usergroups" => $usergroups  
        ];

        $this -> view('users/index',$data);
    }

    public function getusers(){
        
        $action = $_POST['action'];
        $search = $_POST['search']['value'];
        $order = $_POST["order"]['0']['column'];
        $orderDir = $_POST["order"]['0']['dir'];
        $length = $_POST["length"];
        $start = $_POST['start'];

        $fromDate = isset($_POST["fromdt"]) && !empty($_POST['fromdt']) ? date('Y-m-d H:i:s', strtotime( str_replace('/', '-', $_POST['fromdt'] ) ) ): '';
        $toDate = isset($_POST["todt"]) && !empty($_POST['todt']) ? date('Y-m-d H:i:s', strtotime( str_replace('/', '-', $_POST['todt'] ) ) ): '';

        $depts = isset($_POST['depts']) ?  implode(',', $_POST['depts']) : '0000';
        $deptscnts = isset($_POST["depts"]) ? array_count_values($_POST["depts"]): '';

        $usrgrp = isset($_POST['usrgrp']) ?  implode(',', $_POST['usrgrp']) : '0000';
        $usrgrpcnts = isset($_POST["usrgrp"]) ? array_count_values($_POST["usrgrp"]): '';

        

        $users = $this -> userModel -> getUsers($search,$order,$orderDir,$length,$start,$fromDate,$toDate,$depts,$deptscnts,$usrgrp,$usrgrpcnts);
        $departments = $this -> userModel -> getDepartments();

        $FinalData = array();
        $results = $users['FinalData'];
        
         $i=1;
        foreach( $results as $data ) { 

            $editbtn = '<a href="'.URLROOT.'/users/edituser/'.$data -> userid.'" data-toggle="tooltip" data-placement="bottom" title="Edit User"><span style="color:#0275db;"><i class="fas fa-edit"></i></span></a>';
           // $resetbtn= '<button style="border:none;background:none;" title="Reset Password" value="Reset" href="#pwdModal" ><span style="color:#ff00ff"><i class="fas fa-key"></i></span>></button>';
           //$resetbtn = '<label class="switch"><button style="border:none;background:none;" title="Reset Password" id="'.$data -> userid.'" class="resetclass" value="Reset" href="#pwdModal"  data-success-url="'.URLROOT.'/users" data-url="'.URLROOT.'/users/resetpwd/'.$data -> userid.'" ><span style="color:#ff00ff"><i class="fas fa-key"></i></span></button> </label>';
           $resetbtn = '<button style="border:none;background:none;  href="#pwdModal" data-success-url="'.URLROOT.'/users" data-url="'.URLROOT.'/users/resetpwd/'.$data -> userid.'" data-toggle="modal" data-target="#pwdModal" data-title="Are you trying to Reset Password?" data-message="Are you sure, you want to reset the password of User:'.$data -> userid.' "><span style="color:#ff00ff"><i class="fas fa-key"></i></span></button>';
           $delbtn = '<button  style="border:none;background:none; href="#pwdModal" data-success-url="'.URLROOT.'/users" data-url="'.URLROOT.'/users/deluser/'.$data -> userid.'" data-toggle="modal" data-target="#myModal" data-title="Are you trying to Delete Password?" data-message="Are you sure, you want to Delete the User:'.$data -> user_name.', this process cannot be Undone."><span style="color:red"><i class="fas fa-trash-alt"></i></span></button>';
           $flag='<div class="custom-control custom-switch">';
            if($data->flag==1){
                $flag.='<input class="custom-control-input" type="checkbox" id="flag'.$data -> userid.'" onclick="usrchk('.$data -> userid.')" checked/><label class="custom-control-label" for="flag'.$data -> userid.'">';
                $flag.='';
                $flag.='</label><span class="info-text" id="flagtxt'.$data -> userid.'"></span></div>';
            } else if($data->flag==0){
                $flag='<div class="custom-control custom-switch"><input class="custom-control-input" type="checkbox" id="flag'.$data -> userid.'" onclick="usrchk('.$data -> userid.')" /><label class="custom-control-label" for="flag'.$data -> userid.'">';
                $flag.='';
                $flag.='</label><span class="info-text" id="flagtxt'.$data -> userid.'"></span>';
            } 
            $flag.='</div>';

            $FinalData[] = array(
                "id" => $data->userid,
                "user_id" => $data->user_id,
                "user_name" => $data -> user_name,
                "email_id" => $data->email_id,
                "designation" => $data->designation,
                "department" => $data->department,   
                "usergroupname" => $data->usergroupname, 
                "userenteredon" => date('d-M-y',strtotime($data->userenteredon)),
                // "userenteredon" => date_format(date_create($data->userenteredon),"d-M-y"),
                "flag"=>$flag,
                "editbtn"=>$editbtn,
                "resetbtn"=>$resetbtn,
                "delbtn"=>$delbtn
            ); 
            $i++;

        }
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal"   =>  $users['numRows'],
            "recordsFiltered" => $users['numRows'],
            "data"     => $FinalData

        );  
        echo json_encode($output,true);
    }

    public function chgflag(){
        $userid=$_POST['usrid'];
        $flag=$_POST['flag'];
        $result=$this -> userModel -> chgflag($userid,$flag);
    }
    

    public function getCasCadeFilters() {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if($action == 'getbrhierdata') {
            $zn = isset($_POST['zn']) ?  implode(',', $_POST['zn']) : '';
            $rgn = isset($_POST['rgn']) ?  implode(',', $_POST['rgn']) : '';                
            $area = isset($_POST['area']) ?  implode(',', $_POST['area']) : '';
            $br = isset($_POST['br']) ?  implode(',', $_POST['br']) : '';
            $sbr = isset($_POST['sbr']) ?  $_POST['sbr'] : '';
            $zones = $this -> userModel -> getzones();
            $regions = $this -> userModel  -> getCasCadeRegions($zn);
            $areas = $this -> userModel -> getCasCadeAreas($zn,$rgn);
            $branches = $this -> userModel -> getCasCadeBranchs($zn,$rgn,$area);

            $data = array(
                'zn' => $zones,
                'rgn' => $regions,
                'ara' => $areas,
                'brn' => $branches
            );
            echo json_encode($data);
        }
    }


    public function clientsdata(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
       // $users = $this -> userModel -> getClients();
        $data=[
            
        ];
        $this -> view('users/clients',$data);
    }

    public function clientslist() {
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $action = $_POST['action'];
            $search = $_POST['search']['value'];
            $order = $_POST["order"]['0']['column'];
            $orderDir = $_POST["order"]['0']['dir'];
            $length = $_POST["length"];
            $start = $_POST['start'];

            $label = isset($_POST["label"]) && !empty($_POST['label']) ? $_POST['label'] : '';
            $fromDate = isset($_POST["fromdt"]) && !empty($_POST['fromdt']) ? date('Y-m-d H:i:s', strtotime( str_replace('/', '-', $_POST['fromdt'] ) ) ): '';
            $toDate = isset($_POST["todt"]) && !empty($_POST['todt']) ? date('Y-m-d H:i:s', strtotime( str_replace('/', '-', $_POST['todt'] ) ) ): '';
            
            if(!empty($label) && !empty($fromDate)) {
                $data1 = $this -> userModel -> getClientslist($search,$order,$orderDir,$length,$start,$label,$fromDate,$toDate);
                $FinalData = array();
                $results = $data1['FinalData'];
                $i = 1;
                foreach( $results as $data ) {
                   /*  if($data->status == 'new' && $_SESSION['user_group'] == 12) {
                        $show = '<button style="border:none;background:none;" title="Update" value="Update" href="#approveModal" data-success-url="'.URLROOT.'/users/clientsdata" data-url="'.URLROOT.'/users/approveclient/'.$data -> id.'" data-bs-toggle="modal" data-bs-target="#approveModal" data-title="Are you trying to Update Client Data?" data-message="Are you sure, you want to update client data:'.$data -> client_id.'"><span style="color:#ff00ff"><i class="fas fa-check"></i></span></button>';
                    } else {
                        $show = '';
                    } */
                              
                    $FinalData[] = array(
                        "loan_appln_id" => $data -> loan_appln_id,
                        "cust_nm" => $data -> cust_nm,
                        "clnt_id" => $data->client_id,
                        "label" => $data -> label,
                        "finflux_label" => $data -> finflux_label,
                        "cre_on_ts" => $data -> cre_on_ts
                    ); 
                    $i++;
                }
                $output = array(
                    "draw" => intval($_POST["draw"]),
                    "recordsTotal"   =>  $data1['numRows'],
                    "recordsFiltered" => $data1['numRows'],
                    "data"     => $FinalData
                );  
                echo json_encode($output,true);
            } else {
                $output = array(
                    "draw" => intval($_POST["draw"]),
                    "recordsTotal"   =>  0,
                    "recordsFiltered" => 0,
                    "data"     => []
                );  
                echo json_encode($output,true);
            }
            
        }
    }

    public function addclient() {
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        // Check for the POST Method
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [
                'id' => trim($_POST['id']),
                'clnt_id' => trim($_POST['clnt_id']),
                'spouse_nm' => trim($_POST['spouse_nm']),
                'spouse_dob' => trim($_POST['spouse_dob']),
                'mobile' => trim($_POST['mobile']),
                'm_status' => trim($_POST['m_status']),
                'dob' => trim($_POST['dob']),
                'clnt_id_err' =>'',
                'spousenm_err' => '',
                'spousedob_err' => '',
                'm_status_err' => '',
                'mobile_err' => '',
                'dob_err' => '',
            ];
                   
            //Validate ID
            if(empty($data['clnt_id'])){
                $data['clnt_id_err'] = 'Please enter client id';
            } else{
                if(strlen($data['clnt_id']) != 9) 
                    $data['clnt_id_err'] = 'Client id must be 9 digits number';
                if($this -> userModel -> findClntByID($data['clnt_id'],$data['id'])){
                    $data['clnt_id_err']= 'Record already exist with this Id';
                }
            }

            //Validate marital status
            if(empty($data['m_status'])){
                $data['m_status_err']='Please select marital status';
            }

            if(empty($data['dob'])){
                $data['dob_err']='Please enter date of birth';
            }

            //Validate Mobile
            if(empty($data['mobile'])){
                $data['mobile_err']='Please enter mobile';
            }else {
                if(strlen($data['mobile']) != 10) {
                    $data['mobile_err']= 'Enter valid mobile number';
                }
                if($this -> userModel -> findClntByMobile($data['mobile'],$data['id'])){
                    $data['mobile_err']= 'Mobile number already taken';
                }
            }

            if(empty($data['spouse_nm'])){
                $data['spousenm_err']='Please select spouse name';
            }

            if(empty($data['spouse_dob'])){
                $data['spousedob_err']='Please enter date of birth of spouse';
            }
        
            // Make sure Error variables are emptty
            if(empty($data['clnt_id_err']) && empty($data['m_status_err']) && empty($data['mobile_err']) && empty($data['dob_err']) && empty($data['spousenm_err']) && empty($data['spousedob_err'])) {
                if($data['id'] > 0) {
                    if($this -> userModel -> editclient($data)){
                        flashMsg('CLIENTS','Client data successfully edited');
                        redirect('users/clientsdata');
                    }else {
                        die('Something went wrong');
                    } 
                } else {                
                    if($this -> userModel -> addclient($data)){
                            flashMsg('CLIENTS','Client successfully added');
                            redirect('users/clientsdata');
                    }else {
                            die('Something went wrong');
                    } 
                } 
            } else {
                $this -> view('users/addclient', $data);
            }
        } else {
            $data = [
                'id' => 0,
                'clnt_id' => '',
                'spouse_nm' => '',
                'spouse_dob' => '',
                'mobile' => '',
                'm_status' => '',
                'dob' => '',
                'clnt_id_err' =>'',
                'spousenm_err' => '',
                'spousedob_err' => '',
                'm_status_err' => '',
                'mobile_err' => '',
                'dob_err' => ''
            ];
            $this -> view('users/addclient', $data);
        }
    }
    
    public function editclient($id) {
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        // Check for the POST Method
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [
                'id' => trim($_POST['id']),
                'clnt_id' => trim($_POST['clnt_id']),
                'spouse_nm' => trim($_POST['spouse_nm']),
                'spouse_dob' => trim($_POST['spouse_dob']),
                'mobile' => trim($_POST['mobile']),
                'm_status' => trim($_POST['m_status']),
                'dob' => trim($_POST['dob']),
                'clnt_id_err' =>'',
                'spousenm_err' => '',
                'spousedob_err' => '',
                'm_status_err' => '',
                'mobile_err' => '',
                'dob_err' => ''
            ];
                   
            //Validate ID
            if(empty($data['clnt_id'])){
                $data['clnt_id_err'] = 'Please enter client id';
            } else{
                if(strlen($data['clnt_id']) != 9) 
                    $data['clnt_id_err'] = 'Client id must be 9 digits number';
                if($this -> userModel -> findClntByID($data['clnt_id'],$data['id'])){
                    $data['clnt_id_err']= 'Other users already exist with this Id';
                }
            }
            if(empty($data['spouse_nm'])){
                $data['spousenm_err']='Please select spouse name';
            }

            if(empty($data['spouse_dob'])){
                $data['spousedob_err']='Please enter date of birth of spouse';
            }

            //Validate marital status
            if(empty($data['m_status'])){
                $data['m_status_err']='Please select marital status';
            }

            if(empty($data['dob'])){
                $data['dob_err']='Please enter date of birth';
            }

            //Validate Mobile
            if(empty($data['mobile'])){
                $data['mobile_err']='Please enter mobile';
            }else {
                if(strlen($data['mobile']) != 10) {
                    $data['mobile_err']= 'Enter valid mobile number';
                }
                if($this -> userModel -> findClntByMobile($data['mobile'],$data['id'])){
                    $data['mobile_err']= 'Mobile number already taken for other client';
                }
            }
        
            // Make sure Error variables are emptty
            if(empty($data['clnt_id_err']) && empty($data['m_status_err']) && empty($data['mobile_err']) && empty($data['dob_err']) && empty($data['spousenm_err']) && empty($data['spousedob_err'])) {
                    if($this -> userModel -> editclient($data)){
                        flashMsg('CLIENTS','Client successfully updated');
                        redirect('users/clientsdata');
                    }else {
                        die('Something went wrong');
                    }   

            } else{
                $this -> view('users/editclient', $data);
            }
        

        } else {
            $user = $this -> userModel -> getClntById($id);
            $data = [
                'id' => $id,
                'clnt_id' => $user->client_id,
                'spouse_nm' => $user->spouse_nm,
                'spouse_dob' => $user->spouse_dob,
                'mobile' => $user->mobile_no,
                'm_status' => $user->marital_status,
                'dob' => $user->dob,
                'clnt_id_err' =>'',
                'spousenm_err' => '',
                'spousedob_err' => '',
                'm_status_err' => '',
                'mobile_err' => '',
                'dob_err' => '',
            ];
            $this -> view('users/addclient', $data);
        }
    }

    public function approveclient($id) {
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        if($this -> userModel -> approveclient($id)){
            flashMsg('CLIENTS','Client data successfully updated');
            redirect('users/clientsdata');
        }else {
            die('Something went wrong');
        } 
    }

    public function downloadclients($status='') {
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        if($_SERVER['REQUEST_METHOD']=='POST'){ 
            // Save Form date
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $from_date = trim($_POST['from_date']);
            if (empty($from_date)){
               $data['from_date_err'] = "From date is mandatory";
            }
          
            if(!empty($data['from_date_err'])){ 
               $data=[
                   'from_date' => $from_date,
                   'from_date_err' => $data['from_date_err'],
                   'myfilename' => '',
                   'mystatus' =>'',
                   'mydata' =>false
               ];               
            } else {              
                if($status == 'updated'){
                   $myfilename = 'Updated List ' . '-' . strtoupper(date_format(date_create($from_date),"dMY") );
                }elseif($status == 'new'){
                   $myfilename = 'New List ' . '-' . strtoupper(date_format(date_create($from_date),"dMY") );
                }
               
                $myfilename =$myfilename . '.csv';
                $mydata = $this -> userModel -> getClntsByDate($from_date,$status);
                $data=[
                   'from_date' => $from_date,
                   'from_date_err' => '',
                   'mystatus' => $status,
                   'myfilename' => $myfilename,
                   'mydata' => $mydata
                ];               
            }
            $this -> view('users/download',$data);
        } else {
            $from_date=date('d-M-y',strtotime('yesterday'));
            $data=[
                'from_date' => $from_date,
                'from_date_err' => '',
                'myfilename' => '',
                'mystatus' =>'',
                'mydata' =>false
            ];
            $this -> view('users/download',$data);
        }        
    }

    public function brusers() {
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $users = $this -> userModel -> getBranchUsers();
        $data=[
            'users' => $users
        ];
        $this -> view('users/branchindex',$data);
    }

    public function abbreviations() {
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $data=[
            
        ];
        $this -> view('users/abbreviations',$data);
    }

    public function adduser(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        // Check for the POST Method
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [
                'user_id' => trim($_POST['user_id']),
                'user_name' => trim($_POST['user_name']),
                'mobile' => trim($_POST['mobile']),
                'email' => trim($_POST['email']),
                'group_id' => trim($_POST['group_id']),
                'dept_id' => trim($_POST['dept_id']),
                'desg_id' => trim($_POST['desg_id']),
                'zn_ids' => isset($_POST['zones']) ?  $_POST['zones']: '',
                'rgn_ids' => isset($_POST['regions']) ?  $_POST['regions']: '',
                'area_ids' => isset($_POST['areas']) ?  $_POST['areas']: '',
                'brch_id' => isset($_POST['branch']) ? trim($_POST['branch']) : '',
                'brch_ids' => isset($_POST['branches']) ?  $_POST['branches']: '',
                'hod_flag' => trim($_POST['hod_flag']),
                'user_id_err' => '',
                'user_name_err' => '',
                'email_err'  => '',
                'mobile_err' => '',
                'password' =>'',
                'group_id_err' => '',
                'desg_id_err' => '',
                'dept_id_err' => '',
                'brch_id_err' => '',
                'hod_id_err' => ''
            ];

                   
            //Validate ID
            if(empty($data['user_id'])){
                $data['user_id_err']='Please enter user id';
            } else{
                if($this -> userModel -> findUserByID($data['user_id'])){
                    $data['user_id_err']= 'User already exist with this User Id';
                }
            }

            //Validate Name
            if(empty($data['user_name'])){
                $data['user_name_err']='Please enter name';
            }

            //Validate Email
            /* if(empty($data['email'])){
                $data['email_err']='Please enter email';
            }else {
                if($this -> userModel -> findUserByEmail($data['email'])){
                    $data['email_err']= 'Email already taken';
                }
            } */

            //Validate Mobile
            if(empty($data['mobile'])){
                $data['mobile_err']='Please enter mobile';
            }else {
                if(strlen($data['mobile']) != 10) {
                    $data['mobile_err']= 'Enter valid mobile number';
                }
                if($this -> userModel -> findUserByMobile($data['mobile'])){
                    $data['mobile_err']= 'Mobile number already taken';
                }
            }
            
            //Validate user group
            if(empty($data['group_id'])){
                $data['group_id_err']='Please select user gorup';
            }

            //validate department
            if(empty($data['desg_id'])){
                $data['desg_id_err'] = 'Please select designation';
            }

                //validate department
            if(empty($data['dept_id'])){
                $data['dept_id_err'] = 'Please select department';
            }

            // validate branch
            if(empty($data['brch_id'])){
                if($data['group_id'] == 2 || $data['group_id'] == 6 ) {
                    $data['brch_id_err'] = 'Please select branch';
                }
            }

            // validate branch
            if(empty($data['brch_ids'])){
                if($data['group_id'] == 7 || $data['group_id'] == 8 || $data['group_id'] == 10 || $data['group_id'] == 13) {
                    $data['brch_id_err'] = 'Please select branches';
                }
            }
            
        
            // Make sure Error variables are emptty
            if(empty($data['user_id_err']) && empty($data['user_name_err']) &&  empty($data['group_id_error']) && empty($data['desg_id_err']) && empty($data['dept_id_err']) && empty($data['brch_id_err']) && empty($data['mobile_err'])){
                //$data['password'] = $data['user_id'];      
                $data['password'] = password_hash($data['user_id'], PASSWORD_DEFAULT); 
                       
                if($this -> userModel -> addUser($data)){
                    flashMsg('USERS','User successfully added');
                    redirect('users');
                }else {
                    flashMsg('USERS','Something went wrong');
                    redirect('users');
                }

            } else{
                $usergroups=$this -> userModel -> getUserGroups();
                $designations = $this -> userModel -> getDesignations();
                $departments = $this -> userModel -> getDepartments();
                $zones = $this -> userModel -> getzones();
                $regions = $this -> userModel -> getRegions();
                $areas = $this -> userModel -> getAreas();
                $branches = $this -> userModel -> getBranches();
                $data['usergroups'] = $usergroups;
                $data['designations'] = $designations;
                $data['departments'] = $departments;
                $data['zones'] = $zones;
                $data['regions'] = $regions;
                $data['areas'] = $areas;
                $data['branches'] = $branches;
                $this -> view('users/adduser', $data);
            }        

        } else {
            // Load the form
            $usergroups=$this -> userModel -> getUserGroups();
            $designations = $this -> userModel -> getDesignations();
            $departments = $this -> userModel -> getDepartments();
            $zones = $this -> userModel -> getzones();
            $regions = $this -> userModel -> getRegions();
            $areas = $this -> userModel -> getAreas();
            $branches = $this -> userModel -> getBranches();
            $data = [
                'user_id' => '',
                'user_name' => '',
                'email' => '',
                'mobile' => '',
                'user_id_err' =>'',
                'user_name_err' => '',
                'email_err' => '',
                'mobile_err' => '',
                'usergroups' => $usergroups,
                'group_id' => '',
                'group_id_err' => '',
                'desg_id' =>'',
                'dept_id_err' => '',
                'desg_id_err' => '',
                'dept_id' => '',
                'brch_id' => '',
                'brch_id_err' => '',
                'hod_flag' => 'no',
                'hod_id_err' => '',
                'departments' => $departments,
                'designations' => $designations,
                'zones' => $zones,
                'zn_id' => '',
                'regions' => $regions,
                'rgn_id' => '',
                'areas' => $areas,
                'area_id' => '',
                'branches' => $branches
            ];
            $this -> view('users/adduser', $data);
        }
    }

    public function edituser($id){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        // Check for the POST Method
        
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [
                'id' => $id,
                'user_id' => trim($_POST['user_id']),
                'user_name' => trim($_POST['user_name']),
                'email' => trim($_POST['email']),
                'mobile' => trim($_POST['mobile']),
                'group_id' => trim($_POST['group_id']),
                'dept_id' => trim($_POST['dept_id']),
                'desg_id' => trim($_POST['desg_id']),
                'zn_ids' => isset($_POST['zones']) ?  $_POST['zones']: '',
                'rgn_ids' => isset($_POST['regions']) ?  $_POST['regions']: '',
                'area_ids' => isset($_POST['areas']) ?  $_POST['areas']: '',
                'brch_id' => trim($_POST['branch']),
                'brch_ids' => isset($_POST['branches']) ?  $_POST['branches']: '',
                'hod_flag' => trim($_POST['hod_flag']),
                'user_id_err' => '',
                'user_name_err' => '',
                'email_err'  => '',
                'mobile_err' => '',
                'password' =>'',
                'group_id_err' => '',
                'desg_id_err' => '',
                'dept_id_err' => '',
                'brch_id_err' => '',
                'hod_id_err' => ''
            ];

            $user = $this -> userModel -> getUserById($id);   
            //Validate ID
            if(empty($data['user_id'])){
                $data['user_id_err']='Please enter user id';
            } else{
                $udata = $this -> userModel -> findUserByID($data['user_id']);
                if(!empty($udata)) {
                    if($user->id != $udata->id)
                        $data['user_id_err']= 'Some other User exist with this User Id';
                }
            }

            //Validate Name
            if(empty($data['user_name'])){
                $data['user_name_err']='Please enter name';
            }

            //Validate Email
            /* if(empty($data['email'])){
                $data['email_err']='Please enter email';
            }else {
                $udata = $this -> userModel -> findUserByEmail($data['email']);
                if(!empty($udata)) {
                    if($user->id != $udata->id)
                        $data['email_err']= 'Some other User exist with this email';
                }
            } */

            //Validate Mobile
            if(empty($data['mobile'])){
                $data['mobile_err']='Please enter mobile';
            }else {
                if(strlen($data['mobile']) != 10) {
                    $data['mobile_err']= 'Enter valid mobile number';
                }
                $udata = $this -> userModel -> findUserByMobile($data['mobile']);
                if(!empty($udata)) {
                    if($user->id != $udata->id)
                        $data['mobile_err']= 'Some other User exist with this mobile';
                }
            }
            
            //Validate user group
            if(empty($data['group_id'])){
                $data['group_id_err']='Please select user gorup';
            }

            //validate department
            if(empty($data['desg_id'])){
                $data['desg_id_err'] = 'Please select designation';
            }

                //validate department
            if(empty($data['dept_id'])){
                $data['dept_id_err'] = 'Please select department';
            }

            // validate branch
            if(empty($data['brch_id'])){
                if($data['group_id'] == 2 || $data['group_id'] == 6 ) {
                    $data['brch_id_err'] = 'Please select branch';
                }
            }

            // validate branch
            if(empty($data['brch_ids'])){
                if($data['group_id'] == 7 || $data['group_id'] == 8 || $data['group_id'] == 10 || $data['group_id'] == 13) {
                    $data['brch_id_err'] = 'Please select branches';
                }
            }
            
        
            // Make sure Error variables are emptty
            if(empty($data['user_id_err']) && empty($data['user_name_err']) && empty($data['group_id_error']) && empty($data['desg_id_err']) && empty($data['dept_id_err']) && empty($data['brch_id_err']) && empty($data['mobile_err'])){
                
                if($this -> userModel -> editUser($data)){
                    flashMsg('USERS','Successfully update the User: <b>' . $user -> user_name . '</b>');
                    redirect('users');
                }else {
                    die('Something went wrong');
                }

            }else{
                $usergroups=$this -> userModel -> getUserGroups();
                $designations = $this -> userModel -> getDesignations();
                $departments = $this -> userModel -> getDepartments();
                $zones = $this -> userModel -> getzones();
                $regions = $this -> userModel -> getRegions();
                $areas = $this -> userModel -> getAreas();
                $branches = $this -> userModel -> getBranches();
                $data['usergroups'] = $usergroups;
                $data['designations'] = $designations;
                $data['departments'] = $departments;
                $data['zones'] = $zones;
                $data['regions'] = $regions;
                $data['areas'] = $areas;
                $data['branches'] = $branches;
                $this -> view('users/edituser', $data);
            }        

        }else{
            // Load the form
            $user = $this -> userModel -> getUserById($id);
            $zones = $this -> userModel -> getzones();
            $regions = $this -> userModel -> getRegions();
            $areas = $this -> userModel -> getAreas();
            $branches = $this -> userModel -> getBranches();
            $usergroups=$this -> userModel -> getUserGroups();
            $designations = $this -> userModel -> getDesignations();
            $departments = $this -> userModel -> getDepartments();
            $data = [
                'id' => $id,
                'user_id' => $user -> user_id,
                'user_name' => $user -> user_name,
                'email' => $user -> email_id,
                'mobile' => $user -> mobile,
                'user_id_err' =>'',
                'user_name_err' => '',
                'email_err' => '',
                'mobile_err' => '',
                'usergroups' => $usergroups,
                'group_id' => $user -> group_id,
                'group_id_err' => '',
                'desg_id' => $user -> desg_id,
                'dept_id' => $user -> dept_id,
                'zn_id' => !empty($user -> zn_id)?explode(',',$user->zn_id):'',
                'rgn_id' => !empty($user -> rgn_id)?explode(',',$user->rgn_id):'',
                'area_id' => !empty($user -> area_id)?explode(',',$user->area_id):'',
                'brch_id' => $user -> br_id,
                'brch_ids' => !empty($user -> br_ids)?explode(',',$user->br_ids):'',
                'hod_flag' => $user -> hod_flag,
                'hod_id_err' => '',
                'departments' => $departments,
                'designations' => $designations,
                'branches' => $branches,
                'zones' => $zones,
                'regions' => $regions,
                'areas' => $areas
            ];            
            
            $this -> view('users/edituser', $data);
        }
    }

    public function deluser($id){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $user = $this -> userModel -> getUserById($id);
        if($this -> userModel -> delUserData($id,'users','u')){
            flashMsg('USERS','Successfully deleted the User: <b>' . $user -> user_name . '</b>','alert alert-danger' );
            redirect('users');
        }else{
            die('Soemthing went wrong');
        }
    }    

    public function sendotp() {
        if(isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) {        
            redirect('dashboards'); 
        }
        $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
        $newMob = '91'.$mobile;
        $authkey = '381319AbEGZmfv7D630889b4P1';
        $tempid = '6337fe74d6fc0528a6785fd2';

        $url = "https://api.msg91.com/api/v5/otp?template_id=".$tempid."&otp_expiry=1&mobile=".$newMob."&authkey=".$authkey;
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $phoneList = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $response = json_decode($phoneList);
                    
        if($response->type == 'success') {
            $findata = array(
                'success' => true,
                'msg' => ''
            );
        } else {
            $findata = array(
                'success' => false,
                'msg' => $response->message
            );         
        }   
        
        echo json_encode($findata,true);
    }

    public function verifyotp() {
        if(isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) {        
            redirect('dashboards'); 
        }
        if($_SERVER['REQUEST_METHOD']=='POST') {
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [                
                'mobile_num' => isset($_POST['mobile_num']) ? trim($_POST['mobile_num']) : '',
                'otp1' => isset($_POST['otp1']) ? trim($_POST['otp1']) : '',
                'otp2' => isset($_POST['otp2']) ? trim($_POST['otp2']) : '',
                'otp3' => isset($_POST['otp3']) ? trim($_POST['otp3']) : '',
                'otp4' => isset($_POST['otp4']) ? trim($_POST['otp4']) : '',
                'otp_err' => '',
                'mobile_err' => ''
            ];

             if(strlen($data['otp1']) == 0 || strlen($data['otp2']) == 0 || strlen($data['otp3']) == 0 || strlen($data['otp4']) == 0) {
                $data['otp_err'] = 'Enter 4 digits otp';
                $data['success'] = false;
            } else { 
                $newMob = '91'.$data['mobile_num'];
                $authkey = '381319AbEGZmfv7D630889b4P1';
                $tempid = '6337fe74d6fc0528a6785fd2';
                $finalotp = $data['otp1'].$data['otp2'].$data['otp3'].$data['otp4'];

                $url = "https://api.msg91.com/api/v5/otp/verify?otp=".$finalotp."&authkey=381319AbEGZmfv7D630889b4P1&mobile=".$newMob;
               
    
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json'
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $phoneList = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);

                $response = json_decode($phoneList);
                
                if($response->type == 'success') {
                    $_SESSION['otp_verify'] = true;
                    $data['otp_err'] = '';
                    $data['success'] = true;
                } else {
                    $_SESSION['otp_verify'] = false;
                    $data['otp_err'] = $response->message;
                    $data['success'] = false;
                }
            }

            $findata = array(
                'success' => $data['success'],
                'msg' => $data['otp_err']
            );

            echo json_encode($findata,true);
            
        }
    }

    public function login(){
        if($_SERVER['REQUEST_METHOD']=='POST') {
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [                
                'user_id' => isset($_POST['user_id']) ? trim($_POST['user_id']) : '',
                'password' => isset($_POST['password']) ? trim($_POST['password']) : '',
                'user_id_err' => '',
                'password_err' => '',
                'mobile_num' => isset($_POST['mobile_num']) ? trim($_POST['mobile_num']) : '',
                'mobile_err' => '',
                'otp_err' => ''
            ];

            // user id password login 
            
            if(empty($data['user_id'])){
                $data['user_id_err'] = 'Please enter User Id';
            } 

           
            if (empty($data['password'])){
                $data['password_err']='Please enter Password';
            } 

            if(!empty($data['user_id'])) {

                if($this -> userModel -> findUserByID($data['user_id'])){
                }else{
                    $data['user_id_err']='Invalid user id';
                }
            }             

            if(empty($data['user_id_err']) && empty($data['password_err'])) {
                $loggedInUser=$this ->userModel ->login($data['user_id'], $data['password']);
               
                if($loggedInUser) {
                    $mydata=[
                        'id' => $loggedInUser -> id,
                        'user_id' => $loggedInUser -> user_id,
                        'user_name' => $loggedInUser -> user_id,
                        'password' => '',
                        'confirm_password' => '',
                        'password_err' => '',
                        'confirm_password_err' => ''
                    ];                   
                    if($data['user_id'] == $data['password']) { 
                        $mydata['pwd_type'] = 'reset';
                        $mydata['pwd_msg'] = 'You are a new user or your password was reset by Administrator, you need to change the password to login into application'; 
                        $this -> view('users/changepassword',$mydata);
                    } else {                        
                        if(!empty($loggedInUser -> password_updated_at)) {
                            /* $pwd_setdate=date_create($loggedInUser -> password_updated_at);
                            date_add($pwd_setdate,date_interval_create_from_date_string("30 days")); */
                            $new_setdt = date('Y-m-d', strtotime($loggedInUser -> password_updated_at. ' + 30 days')); 
                            $curr_date = date('Y-m-d');
                            
                            if($curr_date > $new_setdt) {
                                $mydata['pwd_type'] = 'expir';
                                $mydata['pwd_msg'] = 'Your Password is expired, You need to change your password.'; 
                                $mydata['curr_password'] = '';
                                $mydata['curr_password_err'] = '';
                                $this -> view('users/changepassword',$mydata);
                            } else {
                                if($loggedInUser -> group_id != 2) {
                                    $this -> createUserSession($loggedInUser);
                                } else {
                                    //if($loggedInUser -> user_id == '10002_bm'){
                                        $this -> createUserSession($loggedInUser);
                                    /* } else {
                                        $mydata['user_mobile'] = $loggedInUser -> mobile;
                                        $mydata['user_mobilechk'] = $loggedInUser -> mobile_chk;
                                        $mydata['br_id'] = $loggedInUser -> br_id;
                                        $mydata['otp_verify'] = false;
                                        $this -> view('dashboards/verifyotp',$mydata);
                                    } */
                                }                                
                            }
                        } else {
                            if($loggedInUser -> group_id != 2) {
                                $this -> createUserSession($loggedInUser);
                            } else {
                               // if($loggedInUser -> user_id == '10002_bm'){
                                    $this -> createUserSession($loggedInUser);
                                /* } else {
                                    $mydata['user_mobile'] = $loggedInUser -> mobile;
                                    $mydata['user_mobilechk'] = $loggedInUser -> mobile_chk;
                                    $mydata['br_id'] = $loggedInUser -> br_id;
                                    $mydata['otp_verify'] = false;
                                    $this -> view('dashboards/verifyotp',$mydata);
                                } */
                            }
                        }                        
                    }                    
                } else {
                    $data['password_err']='Password incorrect';
                    $this -> view('users/login',$data);
                }
            } else {
                $this -> view('users/login', $data);
            }
        } else {
            if(isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) {        
                redirect('dashboards'); 
            } else {
                $data = [
                    'user_id' => '',
                    'password' => '',
                    'user_id_err' => '',
                    'password_err' => '',
                    'mobile_num' => '',
                    'mobile_err' => '',
                    'otp_err' => '',
                ];
                
                $this -> view('users/login', $data);
            }            
        }
    }

    //******************User Group Data goes here*****************************
    public function usergroup(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $groups = $this -> userModel -> getUserGroups();        
        $data = [
            'groups' => $groups
        ];
        //Load View
        $this -> view('users/usergroup', $data);
    }

    public function addusergroup(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        // Check for the POST Method
        if($_SERVER['REQUEST_METHOD']=='POST'){
            // Save Form data
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [                
                'group_name' => trim($_POST['group_name']),
                'group_name_err' => ''
            ];

            //VALIDATE EMAIL
            if(empty($data['group_name'])){
                $data['group_name_err'] = 'Please enter User group';
            }else{
                if($this -> userModel -> getGroupByName($data['group_name'])){
                    $data['group_name_err']= 'User group already exist';
                }
            }
            
            //Check error variables are empty
            if(empty($data['group_name_err'])){
                
                if($this -> userModel -> addUserGroup($data)){
                    flashMsg('UserGroup','Successfully added the group');
                    redirect('users/usergroup');
                }else {
                    die('Something went wrong');
                }

            }else{
                //Load View
                $this -> view('users/addusergroup',$data);
            }


        }else{
            // Load the form
            $data = [
                'group_name' => '',
            ];

            //Load View
            $this -> view('users/addusergroup', $data);
        }
    }

    public function status(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $data1 = $this->userModel->getstatus();
        $FinalData = array();
        $results = $data1['FinalData'];
        // print_r($results);
        // die();

        foreach($results as $data){
            $editbtn = '<a href="'.URLROOT.'/users/edituser/'.$data -> group_id.'" data-toggle="tooltip" data-placement="bottom" title="Edit User"><span style="color:#0275db;"><i class="fas fa-edit"></i></span></a>';
            $delbtn = '<button  style="border:none;background:none; href="#pwdModal" data-success-url="'.URLROOT.'/users" data-url="'.URLROOT.'/users/deluser/'.$data -> group_id.'" data-toggle="modal" data-target="#myModal" data-title="Are you trying to Delete Password?" data-message="Are you sure, you want to Delete the User:'.$data -> usergroupname.', this process cannot be Undone."><span style="color:red"><i class="fas fa-trash-alt"></i></span></button>';
            $flag='<div class="custom-control custom-switch">';
                if($data->flag==1){
                    $flag.='<input class="custom-control-input" type="checkbox" id="flag'.$data -> group_id.'" onclick="usrchk('.$data -> group_id.')" checked/><label class="custom-control-label" for="flag'.$data -> group_id.'">';
                    $flag.='';
                    $flag.='</label><span class="info-text" id="flagtxt'.$data -> group_id.'"></span></div>';
                } else if($data->flag==0){
                    $flag='<div class="custom-control custom-switch"><input class="custom-control-input" type="checkbox" id="flag'.$data -> group_id.'" onclick="usrchk('.$data -> group_id.')" /><label class="custom-control-label" for="flag'.$data -> group_id.'">';
                    $flag.='';
                    $flag.='</label><span class="info-text" id="flagtxt'.$data -> group_id.'"></span>';
                } 
                $flag.='</div>';
                
            $FinalData[] = array(
                "group_id" => $data->group_id,
                "usergroupname" => $data->usergroupname, 
                "entered_by" => $data -> entered_by,  
                "userenteredon" => date('d-M-y',strtotime($data->userenteredon)),
                "flag"=>$flag,
                "editbtn"=>$editbtn,
                "delbtn"=>$delbtn
            );    
        }      
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal"   =>  $data1['numRows'],
            "recordsFiltered" => $data1['numRows'],
            "data" => $FinalData
        );   
        echo json_encode($output,true);       
    }

    public function dept_status(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $data1 = $this->userModel->getdeptstatus();
        $FinalData = array();
        $results = $data1['FinalData'];
        // print_r($results);
        // die();

        foreach($results as $data){
            $editbtn = '<a href="'.URLROOT.'/users/edituser/'.$data -> deptid.'" data-toggle="tooltip" data-placement="bottom" title="Edit User"><span style="color:#0275db;"><i class="fas fa-edit"></i></span></a>';
            $delbtn = '<button  style="border:none;background:none; href="#pwdModal" data-success-url="'.URLROOT.'/users" data-url="'.URLROOT.'/users/deluser/'.$data -> deptid.'" data-toggle="modal" data-target="#myModal" data-title="Are you trying to Delete Password?" data-message="Are you sure, you want to Delete the User:'.$data -> department.', this process cannot be Undone."><span style="color:red"><i class="fas fa-trash-alt"></i></span></button>';
            $flag='<div class="custom-control custom-switch">';
                if($data->flag==1){
                    $flag.='<input class="custom-control-input" type="checkbox" id="flag'.$data -> deptid.'" onclick="usrchk('.$data -> deptid.')" checked/><label class="custom-control-label" for="flag'.$data -> deptid.'">';
                    $flag.='';
                    $flag.='</label><span class="info-text" id="flagtxt'.$data -> deptid.'"></span></div>';
                } else if($data->flag==0){
                    $flag='<div class="custom-control custom-switch"><input class="custom-control-input" type="checkbox" id="flag'.$data -> deptid.'" onclick="usrchk('.$data -> deptid.')" /><label class="custom-control-label" for="flag'.$data -> deptid.'">';
                    $flag.='';
                    $flag.='</label><span class="info-text" id="flagtxt'.$data -> deptid.'"></span>';
                } 
                $flag.='</div>';
                
            $FinalData[] = array(
                "deptid" => $data->deptid,
                "department" => $data->department, 
                "entered_by" => $data -> entered_by,  
                "userenteredon" => date('d-M-y',strtotime($data->userenteredon)),
                "flag"=>$flag,
                "editbtn"=>$editbtn,
                "delbtn"=>$delbtn
            );    
        }      
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal"   =>  $data1['numRows'],
            "recordsFiltered" => $data1['numRows'],
            "data" => $FinalData
        );   
        echo json_encode($output,true);       
    }

    public function desig_status(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $data1 = $this->userModel->getdesigstatus();
        $FinalData = array();
        $results = $data1['FinalData'];
        // print_r($results);
        // die();

        foreach($results as $data){
            $editbtn = '<a href="'.URLROOT.'/users/edituser/'.$data -> desgid.'" data-toggle="tooltip" data-placement="bottom" title="Edit User"><span style="color:#0275db;"><i class="fas fa-edit"></i></span></a>';
            $delbtn = '<button  style="border:none;background:none; href="#pwdModal" data-success-url="'.URLROOT.'/users" data-url="'.URLROOT.'/users/deluser/'.$data -> desgid.'" data-toggle="modal" data-target="#myModal" data-title="Are you trying to Delete Password?" data-message="Are you sure, you want to Delete the User:'.$data -> designation.', this process cannot be Undone."><span style="color:red"><i class="fas fa-trash-alt"></i></span></button>';
            $flag='<div class="custom-control custom-switch">';
                if($data->flag==1){
                    $flag.='<input class="custom-control-input" type="checkbox" id="flag'.$data -> desgid.'" onclick="usrchk('.$data -> desgid.')" checked/><label class="custom-control-label" for="flag'.$data -> desgid.'">';
                    $flag.='';
                    $flag.='</label><span class="info-text" id="flagtxt'.$data -> desgid.'"></span></div>';
                } else if($data->flag==0){
                    $flag='<div class="custom-control custom-switch"><input class="custom-control-input" type="checkbox" id="flag'.$data -> desgid.'" onclick="usrchk('.$data -> desgid.')" /><label class="custom-control-label" for="flag'.$data -> desgid.'">';
                    $flag.='';
                    $flag.='</label><span class="info-text" id="flagtxt'.$data -> desgid.'"></span>';
                } 
                $flag.='</div>';
                
            $FinalData[] = array(
                "desgid" => $data->desgid,
                "designation" => $data->designation, 
                "entered_by" => $data -> entered_by,  
                "userenteredon" => date('d-M-y',strtotime($data->userenteredon)),
                "flag"=>$flag,
                "editbtn"=>$editbtn,
                "delbtn"=>$delbtn
            );    
        }      
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal"   =>  $data1['numRows'],
            "recordsFiltered" => $data1['numRows'],
            "data" => $FinalData
        );   
        echo json_encode($output,true);       
    }
    

    public function editusergroup($id){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        // Check for the POST Method
        if($_SERVER['REQUEST_METHOD']=='POST'){
            // Save Form data
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [
                'id' => $id,
                'group_name' => trim($_POST['group_name']),
                'group_name_err' => ''
            ];
            
            //VALIDATE EMAIL
            if(empty($data['group_name'])){
                $data['group_name_err'] = 'Please enter User group';
            }

            //Check error variables are empty
            if(empty($data['group_name_err'])){
                //die('SUCCESS');
                if($this -> userModel -> editUserGroup($data)){
                    flashMsg('UserGroup','Successfully modified the group');
                    redirect('users/usergroup');
                }else {
                    die('Something went wrong');
                }


            }else{
                //Load View
                $this -> view('users/editusergroup',$data);
            }


        }else{
            //Fetch the User group data
            $usergroup = $this -> userModel -> getGroupById($id);
            // Load the form
            
            $data = [
                'id' => $id,
                'group_name' => $usergroup -> group_name
            ];

            //Load View
            $this -> view('users/editusergroup', $data);
        }
    }

    public function delusergroup($id){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        if($this -> userModel -> delUserData($id,'usergroup', 'ug')){
            flashMsg('UserGroup','Successfully deleted the user group','alert alert-danger');
            redirect('users/usergroup');
        }else{
            die('Something went wrong');
        }
    }

    //******************User Group Data goes here*****************************


    //******************Designation Data goes here*****************************
    public function designations(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $designations = $this -> userModel -> getDesignations();
        
            $data = [
                'designations' => $designations
            ];
            //Load View
            $this -> view('users/designations', $data);
    }

    public function adddesignation(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        // Check for the POST Method
        if($_SERVER['REQUEST_METHOD']=='POST'){
            // Save Form data
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [
                
                'desg_name' => trim($_POST['desg_name']),
                'desg_name_err' => ''
            ];

            //VALIDATE EMAIL
            if(empty($data['desg_name'])){
                $data['desg_name_err'] = 'Please enter designation';
            }else{
                if($this -> userModel -> getDesignationByName($data['desg_name'])){
                    $data['desg_name_err']= 'Designation already exist';
                }
            }

            
            //Check error variables are empty
            if(empty($data['desg_name_err'])){
                
                if($this -> userModel -> addDesignation($data)){
                    flashMsg('DESIGNATION','Successfully added the new Designation');
                    redirect('users/designations');
                }else {
                    die('Something went wrong');
                }


            }else{
                //Load View
                $this -> view('users/adddesignation',$data);
            }


        }else{
            // Load the form
            $data = [
                'desg_name' => '',
            ];

            //Load View
            $this -> view('users/adddesignation', $data);
        }
    }

    public function editdesignation($id){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        // Check for the POST Method
        if($_SERVER['REQUEST_METHOD']=='POST'){
            // Save Form data
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [
                'id' => $id,
                'desg_name' => trim($_POST['desg_name']),
                'desg_name_err' => ''
            ];
            
            //VALIDATE EMAIL
            if(empty($data['desg_name'])){
                $data['desg_name_err'] = 'Please enter designation';
            }

            //Check error variables are empty
            if(empty($data['desg_name_err'])){
                //die('SUCCESS');
                if($this -> userModel -> editDesignation($data)){
                    flashMsg('DESIGNATION','Successfully modified the Designaiton');
                    redirect('users/designations');
                }else {
                    die('Something went wrong');
                }


            }else{
                //Load View
                $this -> view('users/editdesignation',$data);
            }


        }else{
            //Fetch the User group data
            $desg = $this -> userModel -> getDesignaitonById($id);
            // Load the form
            
            $data = [
                'id' => $id,
                'desg_name' => $desg -> desg_name
            ];

            //Load View
            $this -> view('users/editdesignation', $data);
        }
    }

    public function deldesignation($id){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $desg = $this -> userModel -> getDesignaitonById($id);
        if($this -> userModel -> delUserData($id,'designation','de')){
            flashMsg('DESIGNATION','Successfully deleted the Designaiton: <b>' . $desg -> desg_name .'</b>','alert alert-danger');
            redirect('users/designations');
        }else{
            die('Something went wrong');
        }
    }

    //******************Designation Data goes here*****************************


    //******************Department Data goes here*****************************
    public function departments(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $departments = $this -> userModel -> getDepartments();
        
            $data = [
                'departments' => $departments
            ];
            //Load View
            $this -> view('users/departments', $data);
    }

    public function adddepartment(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        // Check for the POST Method
        if($_SERVER['REQUEST_METHOD']=='POST'){
            // Save Form data
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [
                
                'dept_name' => trim($_POST['dept_name']),
                'dept_name_err' => ''
            ];

            //VALIDATE EMAIL
            if(empty($data['dept_name'])){
                $data['dept_name_err'] = 'Please enter department';
            }else{
                if($this -> userModel -> getDepartmentByName($data['dept_name'])){
                    $data['dept_name_err']= 'Department already exist';
                }
            }

            
            //Check error variables are empty
            if(empty($data['dept_name_err'])){
                
                if($this -> userModel -> addDepartment($data)){
                    flashMsg('DEPARTMENT','Successfully added the new Department');
                    redirect('users/departments');
                }else {
                    die('Something went wrong');
                }


            }else{
                //Load View
                $this -> view('users/adddepartment',$data);
            }


        }else{
            // Load the form
            $data = [
                'dept_name' => '',
            ];

            //Load View
            $this -> view('users/adddepartment', $data);
        }
    }

    public function editdepartment($id){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        // Check for the POST Method
        if($_SERVER['REQUEST_METHOD']=='POST'){
            // Save Form data
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [
                'id' => $id,
                'dept_name' => trim($_POST['dept_name']),
                'dept_name_err' => ''
            ];
            
            //VALIDATE EMAIL
            if(empty($data['dept_name'])){
                $data['dept_name_err'] = 'Please enter department';
            }

            //Check error variables are empty
            if(empty($data['dept_name_err'])){
                //die('SUCCESS');
                if($this -> userModel -> editDepartment($data)){
                    flashMsg('DEPARTMENT','Successfully modified the Department');
                    redirect('users/departments');
                }else {
                    die('Something went wrong');
                }


            }else{
                //Load View
                $this -> view('users/editdepartment',$data);
            }


        }else{
            //Fetch the User group data
            $dept = $this -> userModel -> getDepartmentById($id);
            // Load the form
            
            $data = [
                'id' => $id,
                'dept_name' => $dept -> dept_name
            ];

            //Load View
            $this -> view('users/editdepartment', $data);
        }
    }

    public function deldepartment($id){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $dept = $this -> userModel -> getDepartmentById($id);
        if($this -> userModel -> delUserData($id,'department','dp')){
            flashMsg('DEPARTMENT','Successfully deleted the Department: <b>' . $dept -> dept_name .'</b>','alert alert-danger');
            redirect('users/departments');
        }else{
            die('Something went wrong');
        }
    }

    //******************Department Data goes here*****************************

    public function valid_phone($phone) {
        return preg_match('/^[0-9]{10}+$/', $phone);
    }

    public function updateUserMobile() {
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $mobile = isset($_POST['nmobile']) ? trim($_POST['nmobile']) : '';
        if(empty($mobile)){
            $data['mobile_err']='Please enter mobile';
            $data['success'] = false;
        } else {
            if(!$this -> valid_phone($mobile)) { 
                $data['mobile_err']= 'Enter valid mobile number';
                $data['success'] = false;
            } else {
                if($this -> userModel -> UserByMobile($mobile)){
                    $data['mobile_err']= 'Mobile number already registred, please enter another number';
                    $data['success'] = false;
                } else {
                   $upd = $this -> userModel -> updateMobile($mobile,$_SESSION['user_id'],$_SESSION['user_mobilechk']);
                    if($upd) {
                        unset($_SESSION['user_mobile']);
                        unset($_SESSION['user_mobilechk']);
                        $_SESSION['user_mobile'] = $mobile;
                        $_SESSION['user_mobilechk'] = 1;
                        $data['mobile_err']= '';
                        $data['success'] = true;
                    } else {
                        $data['mobile_err']= 'Something Went Wrong. Try Again';
                        $data['success'] = false;
                    }
                }
            }            
        }

        $findata = array(
            'success' =>  $data['success'],
            'mobile_err' => $data['mobile_err']
        );

        echo json_encode($findata);        

    }

    public function createUserSession($user) {
        $dept = $this -> userModel -> getDepartmentById($user -> dept_id);
        $desg = $this -> userModel -> getDesignaitonById($user -> desg_id);
        $_SESSION['user_id'] = $user -> user_id;
        $_SESSION['br_id'] = $user -> br_id;
        $_SESSION['br_ids'] = $user -> br_ids;
        $_SESSION['user_email'] = $user -> email_id;
        $_SESSION['user_name'] = $user -> user_name;
        $_SESSION['user_group'] = $user -> group_id;
        $_SESSION['user_desg'] = $user -> desg_id;
        $_SESSION['user_dept'] = $user -> dept_id;
        $_SESSION['user_dept_name'] = $dept -> dept_name;
        $_SESSION['user_desg_name'] = $desg -> desg_name;
        $_SESSION['user_mobile'] = $user -> mobile;
        $_SESSION['user_mobilechk'] = $user -> mobile_chk;
        $_SESSION['login_time_stamp'] = time();
        $_SESSION['otp_verify'] = false;
        if($this -> userModel -> saveLogInfo($user -> user_id,'IN')){            
            redirect('dashboards');
        }else{
            redirect('users/logout');
        }
    }

    

    public function dashboard($usr) {
        $user = $this -> userModel -> findUserByID($usr);
        $dept = $this -> userModel -> getDepartmentById($user -> dept_id);
        $desg = $this -> userModel -> getDesignaitonById($user -> desg_id);
        $_SESSION['user_id'] = $user -> user_id;
        $_SESSION['br_id'] = $user -> br_id;
        $_SESSION['br_ids'] = $user -> br_ids;
        $_SESSION['user_email'] = $user -> email_id;
        $_SESSION['user_name'] = $user -> user_name;
        $_SESSION['user_group'] = $user -> group_id;
        $_SESSION['user_desg'] = $user -> desg_id;
        $_SESSION['user_dept'] = $user -> dept_id;
        $_SESSION['user_dept_name'] = $dept -> dept_name;
        $_SESSION['user_desg_name'] = $desg -> desg_name;
        $_SESSION['user_mobile'] = $user -> mobile;
        $_SESSION['user_mobilechk'] = $user -> mobile_chk;
        $_SESSION['login_time_stamp'] = time();
        $_SESSION['otp_verify'] = false;
        if($this -> userModel -> saveLogInfo($user -> user_id,'IN')){            
            redirect('dashboards');
        }else{
            redirect('users/logout');
        }
    }

    public function logout(){
        if(isset($_SESSION['user_id'])) {
            if($this -> userModel -> saveLogInfo($_SESSION['user_id'],'OUT')){
                unset($_SESSION['user_id']);
                unset($_SESSION['br_id']);
                unset($_SESSION['br_ids']);
                unset($_SESSION['user_email']);
                unset($_SESSION['user_name']);
                unset($_SESSION['user_group']);
                unset($_SESSION['user_desg']);
                unset($_SESSION['user_dept']);
                unset($_SESSION['user_dept_name']);
                unset($_SESSION['user_desg_name']);
                unset($_SESSION['user_mobile']);
                unset($_SESSION['user_mobilechk']);
                unset($_SESSION['login_time_stamp']);
                unset($_SESSION['fregions']);
                unset($_SESSION['fbranches']);
                unset($_SESSION['fpms']);
                unset($_SESSION['fvillages']);
                unset($_SESSION['fcenters']);
                unset($_SESSION['fgroups']);
                unset($_SESSION['fcustomers']);
                unset($_SESSION['inputstatus']); 
                unset($_SESSION['otp_verify']);
                unset($_SESSION['pregions']);
                unset($_SESSION['pbranches']);
                unset($_SESSION['ppms']);
                unset($_SESSION['pvillages']);
                unset($_SESSION['pcenters']);
                unset($_SESSION['pgroups']);
                unset($_SESSION['pcustomers']);
                unset($_SESSION['pddstatus']); 
                unset($_SESSION['pddfrdt']);
                unset($_SESSION['pddtodt']);
                unset($_SESSION['lbranches']);
                unset($_SESSION['lregions']);
                unset($_SESSION['lpms']);
                unset($_SESSION['lvillages']);
                unset($_SESSION['lcenters']);
                unset($_SESSION['lgroups']);
                unset($_SESSION['pstatus']);
                unset($_SESSION['lfrondt']); 
                unset($_SESSION['ltodt']);

                unset($_SESSION['gprmst']);
                unset($_SESSION['gprmrgn']);
                unset($_SESSION['gprmara']);
                unset($_SESSION['gprmbr']); 
                unset($_SESSION['gprmpm']);


                unset($_SESSION['cntrisksts']);
                unset($_SESSION['cntriskrgns']);
                unset($_SESSION['cntriskbrs']); 
                unset($_SESSION['cntriskcntrs']);


                session_destroy();
                redirect('users/login');
            }else{
                die('Something went wrong');
            }
        }  else {
            session_destroy();
            redirect('users/login');
        }
    }   

    
    /* public function showusers(){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        //Get the users data from the database
        $users = $this -> userModel -> getUsers();
        
        //Assign users data to $data array
        $data = [
            'users' => $users
        ];
        //Load View
        $this -> view('users/showusers', $data);
    } */

    public function modifypwd(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            $data = [
                'id' => trim($_POST['user_id']),
                'password'  => trim($_POST['password']),
                'confirm_password'  => trim($_POST['confirm_password']),
                'password_err'  => '',
                'confirm_password_err' => '',
                'user_name' => trim($_POST['user_name']),
                'curr_password' => isset($_POST['curr_password']) ? trim($_POST['curr_password']) : '',
                'curr_password_err' => '',
                'pwd_type' => trim($_POST['pwd_type']),
                'pwd_msg' => trim($_POST['pwd_msg'])
            ];
            
             //Validate Password
            if(empty($data['password'])){
                $data['password_err']='Please enter password';
            } elseif(strlen($data['password']) < 6){
                $data['password_err']='Password must be at least 6 characters';
            }

            //Validate Password
            if(empty($data['confirm_password'])){
                $data['confirm_password_err']='Please enter confirm password';
            }elseif($data['password'] != $data['confirm_password']){
                $data['confirm_password_err']='Passwords do not match';
            }
            $user = $this -> userModel -> getUserById($data['id']);
            if($data['pwd_type'] == 'expir') {
                if(empty($data['curr_password'])){
                    $data['curr_password_err']='Please enter current password';
                }else {
                    $hashedold_password = $user -> password;
                    if (!password_verify($data['curr_password'], $hashedold_password)){ 
                        $data['curr_password_err']='Your current password does not matches with the password you provided. Please try again.';
                    }                     
                }
            }

            if($data['pwd_type'] == 'expir') { 
                if($data['curr_password'] == $data['password']) {
                    $data['password_err'] = 'New Password cannot be same as your current password. Please choose a different password.';
                } 
                if(empty($data['curr_password_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {                    
                    $data['user_id'] = $user -> user_id;
                    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                    if($this -> userModel -> changePWD($data)){
                        flashMsg('LOGIN','Password changed successfully, You can now login !');
                        redirect('users/login');
                    }else {
                        $data['pwd_msg'] = 'Something went wrong, please try again!';
                        $this -> view('users/changepassword', $data);
                    }
                } else {
                    $this -> view('users/changepassword', $data);
                }
            } else if($data['pwd_type'] == 'reset' && empty($data['password_err']) && empty($data['confirm_password_err'])){
                $data['user_id'] = $user -> user_id;
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                if($this -> userModel -> changePWD($data)){
                    flashMsg('LOGIN','Password changed successfully, You can now login !');
                    redirect('users/login');
                }else {
                    $data['pwd_msg'] = 'Something went wrong, please try again!';
                    $this -> view('users/changepassword', $data);
                }
            } else{
                $this -> view('users/changepassword', $data);
            }
        }
    }

    public function resetpwd($id){        
        $user = $this -> userModel -> getUserById($id);
        if($this -> userModel -> resetPWD($id,$user -> user_id)){
            flashMsg('USERS','Successfully reset the password for User: <b>' . $user -> user_id .'</b>');
            redirect('users');
        }else{
            die('Something went wrong');
        }
    }

    public function audittrail($page){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $records_per_page = 10;
        $total_records= $this -> userModel -> getAuditTrailRecords();
        $total_pages = ceil($total_records/$records_per_page);
        $start = ($page-1) * $records_per_page;
        $audittrails = $this -> userModel -> getAuditTrails($page,$records_per_page,$start);
        if($page == 1){
            $previous = 1;
        }else{
            $previous = $page - 1;
        }

        if($page ==  $total_pages){
            $next = $total_pages;
        }else{
            $next = $page + 1;
        }
        $start_page = 1;
        $myPageLimit = 2;
        if ($page == 1){
            $start_page = $page;
        }elseif ($page == $total_pages){
            $start_page = $total_pages - $myPageLimit;
        }else{
            $start_page = $page-1;
        }
        
        $page_limit = $start_page + $myPageLimit;
        
        $data = [
            'audittrails' => $audittrails,
            'start_no' => $start,
            'pages' => $total_pages,
            'current_page'=> $page,
            'previous' => $previous,
            'next' => $next,
            'first' => 1,
            'last' => $total_pages,
            'start_page' => $start_page,
            'page_limit' => $page_limit

        ];
        $this -> view('users/audittrail',$data);
    }

    public function audittrail_show($id){
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }
        $audit_trail_data = $this -> userModel -> getAuditTrailDataById($id);
        $data = [
            'id' => $id,
            'user_id' => $audit_trail_data -> user_id,
            'user_name' => $audit_trail_data -> user_name,
            'action_time' => $audit_trail_data -> action_time,
            'action_type' => $audit_trail_data -> action_type,
            'action_table' => $audit_trail_data -> affected_table,
            'record_id' => $audit_trail_data -> affected_record_id,
            'old_data' => $audit_trail_data -> old_data,
            'new_data' => $audit_trail_data -> new_data
        ];
        $this -> view('users/audittrail_show',$data);
    }

    public function hashpassword()  {
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }

        if($_SERVER['REQUEST_METHOD']=='POST'){
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            if($_FILES['user_file']['tmp_name'] != ''){                
                $csvfile = $_FILES['user_file']['tmp_name'];
                $myfile = fopen($csvfile,'r');
                while($data = fgetcsv($myfile)){
                    if($data[0] == 'user_id') {

                    } else {
                        if(strlen($data[0]) > 0) { 
                           $data = $this -> userModel -> hashpassword($data);
                        }                        
                    }           
                }
                flashMsg('USERS','User Password Hashed successfully');
                redirect('users/hashpassword');
            }else{
                $data = [
                    'user_file_err' => 'Please select CSV file to upload the Details'
                ];
                $this -> view('users/hashpassword',$data);
            }
        } else {            
            $data=[
                'user_file_err'=>'',
                'user_file' => ''
            ];
            $this -> view('users/hashpassword',$data);
        }
    }

    public function changemobile()  {
        if(!isset($_SESSION["user_id"])) {        
            redirect('users/logout'); 
        }

        if($_SERVER['REQUEST_METHOD']=='POST'){
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
            if($_FILES['user_file']['tmp_name'] != ''){                
                $csvfile = $_FILES['user_file']['tmp_name'];
                $myfile = fopen($csvfile,'r');
                while($data = fgetcsv($myfile)){
                    if($data[0] == 'user_id') {

                    } else {
                        if(strlen($data[0]) > 0 && strlen($data[1]) > 0) { 
                           $data = $this -> userModel -> changemobile($data);
                        }                        
                    }           
                }
                flashMsg('USERS','User data modified successfully');
                redirect('users/changemobile');
            }else{
                $data = [
                    'user_file_err' => 'Please select CSV file to upload the Details'
                ];
                $this -> view('users/changemobile',$data);
            }
        } else {            
            $data=[
                'user_file_err'=>'',
                'user_file' => ''
            ];
            $this -> view('users/changemobile',$data);
        }
    }
   
}



       
       
        