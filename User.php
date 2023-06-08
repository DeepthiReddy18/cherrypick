<?php

    class User {
        private $db;
        private $uschema = DB_UTABLE;
        private $rlschema = DB_UGTABLE;
        private $dpschema = DB_DPTABLE;
        private $deschema = DB_DSTABLE;
        private $ulschema = DB_ULTABLE;
        private $adschema = DB_ADTABLE;
        private $offherTable = DB_OTABLE;
        private $clntschema = DB_CLNTABLE;
        private $qschema = DB_LTABLE;
        private $brschema = DB_HRTABLE;


        public function __construct(){
            $this -> db = new Database;
        }

        public function getzones() {
            $this -> db -> query('SELECT DISTINCT(zn_nm), zn_id  FROM '.$this->brschema.' WHERE 1=1 AND zn_nm is not null ORDER BY zn_nm ASC');
            $rows = $this -> db -> resultSet();
            return $rows;
        }
        

        public function getRegions() {
            $this -> db -> query('SELECT DISTINCT(rgn_nm), rgn_id  FROM '.$this->brschema.' WHERE 1=1 AND rgn_nm is not null ORDER BY rgn_nm ASC');
            $rows = $this -> db -> resultSet();
            return $rows;
        }

        public function getCasCadeRegions($zn) {
            if(!empty($zn))
                $this -> db -> query('SELECT DISTINCT(rgn_nm), rgn_id  FROM '.$this->brschema.' WHERE 1=1 AND rgn_nm is not null and zn_id in ('.$zn.') ORDER BY rgn_nm ASC');
            else
                $this -> db -> query('SELECT DISTINCT(rgn_nm), rgn_id  FROM '.$this->brschema.' WHERE 1=1 AND rgn_nm is not null ORDER BY rgn_nm ASC');
            $rows = $this -> db -> resultSet();
            return $rows;
        }

        public function getAreas() {
            $this -> db -> query('SELECT DISTINCT(area_nm), area_id  FROM '.$this->brschema.' WHERE 1=1 AND area_nm is not null ORDER BY area_nm ASC');
            $rows = $this -> db -> resultSet();
            return $rows;
        }

        public function getCasCadeAreas($zn,$rgn) {
            $where = ' Where area_nm is not null ';
            if(!empty($zn)) 
                $where .= ' AND zn_id in ('.$zn.')';

            if(!empty($rgn)) 
                $where .= ' AND rgn_id in ('.$rgn.')';

            $sql = 'SELECT DISTINCT(area_nm), area_id  FROM '.$this->brschema.''.$where.' ORDER BY area_nm ASC';
            $this -> db -> query($sql);
            $rows = $this -> db -> resultSet();
            return $rows;
        }

        public function getBranches() {            
            $this -> db -> query('SELECT DISTINCT(br_nm), br_id  FROM '.$this->brschema.' WHERE 1=1 AND br_nm is not null ORDER BY br_nm ASC');
            $rows = $this -> db -> resultSet();
            return $rows;
        }

        public function getCasCadeBranchs($zn,$rgn,$area) {
            $where = ' Where br_nm is not null ';
            if(!empty($zn)) 
                $where .= ' AND zn_id in ('.$zn.')';

            if(!empty($rgn)) 
                $where .= ' AND rgn_id in ('.$rgn.')';

            if(!empty($area)) 
                $where .= ' AND area_id in ('.$area.')';

            $sql = 'SELECT DISTINCT(br_nm), br_id  FROM '.$this->brschema.''.$where.' ORDER BY br_nm ASC';
            $this -> db -> query($sql);
            $rows = $this -> db -> resultSet();
            return $rows;
        }

        public function delUserData($id,$tablename, $case){
            //Prepare for the audit trail
            $auditdata = [
                'user_id' => $_SESSION['user_id'],
                'action_type' => 'Delete',
                'affected_table' => $tablename,
                'affected_record_id' => $id,
                'old_data' => 'Record deleted',
                'new_data' => 'Record deleted'
            ];

            if($case == 'ug') 
                $schema =  $this->rlschema;
            if($case == 'dp') 
                $schema =  $this->dpschema;
            if($case == 'de') 
                $schema =  $this->deschema;
            if($case == 'u') 
                $schema =  $this->uschema;

            //Physically not deleting Changin flag to 0
            $qryStr = "UPDATE $schema SET del_flag=0 WHERE id=:id";
          //  echo $qryStr.$id;
           // die();
            $this -> db -> query($qryStr);
            $this -> db -> bind(':id',$id);
           
            if($this -> db -> execute()){
                if($this -> audit_trail($auditdata)){
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }
        }

        //Audit trail data save from the below function
        public function audit_trail($data){
            //Prepare the statements
           
            $this -> db -> query('INSERT INTO '.$this->adschema.' (user_id,action_type,affected_table,affected_record_id,old_data,new_data,module) 
                                    VALUES(:user_id,:action_type,:affected_table,:affected_record_id,:old_data,:new_data,:module)');
            //Bind values
            $this -> db -> bind(':user_id',$data['user_id']);
            $this -> db -> bind(':action_type',$data['action_type']);
            $this -> db -> bind(':affected_table',$data['affected_table']);
            $this -> db -> bind(':affected_record_id',$data['affected_record_id']);
            $this -> db -> bind(':old_data',$data['old_data']);
            $this -> db -> bind(':new_data',$data['new_data']);
            $this -> db -> bind(':module',$data['module']);
            //Execute and return 
            if($this -> db -> execute()){
                return true;
            }else {
                return false;
            }
        }

        public function adduser($data){   
            //Prepare Audit Trail data
            $new_data = 'user_id:' . $data['user_id'] . ', '.'group_id:' . $data['group_id'] . ', ' . 'user_name:' . $data['user_name'] . ', ' . 'email_id:' . $data['email'] . ', ' . 'desg_id:' . $data['desg_id'] . ', ' . 'dept_id:' . $data['dept_id']. ', ' . 'mobile:' . $data['mobile']. ', ' . 'hod_flag:' . $data['hod_flag'];
            
            if(!empty($data['zn_ids'])) {
                echo 'noemty block';
                $new_data .= 'zn_ids:' . implode(',', $data['zn_ids']);
                $zns = implode(',', $data['zn_ids']);
            } else {
                echo 'empty block';
                $new_data .= 'zn_ids:' . null;
                $zns = null;
            }
           
            if(!empty($data['rgn_ids'])) {
                $new_data .= 'rgn_ids:' . implode(',', $data['rgn_ids']);
                $rgns = implode(',', $data['rgn_ids']);
            } else {
                $new_data .= 'rgn_ids:' . null;
                $rgns = null;
            }

            if(!empty($data['area_ids'])) {
                $new_data .= 'area_ids:' . implode(',', $data['area_ids']);
                $ars = implode(',', $data['area_ids']);
            } else {
                $new_data .= 'area_ids:' . null;
                $ars = null;
            }                        
            
            if(!empty($data['brch_id']) && ($data['group_id'] == 2 ||$data['group_id'] == 6)) {
                $new_data .= 'br_id:' . $data['brch_id'];
                $brch = $data['brch_id'];
            } else {
                $new_data .= 'br_id:' . null;
                $brch = null;
            }

            if(!empty($data['brch_ids'])) {
                $new_data .= 'br_ids:' . implode(',', $data['brch_ids']);
                $brchs = implode(',', $data['brch_ids']);
            } else {
                $new_data .= 'br_ids:' . null;
                $brchs = null;
            }

            

            $auditdata = [
                'user_id' => $_SESSION['user_id'],
                'action_type' => 'Insert',
                'affected_table' => 'users',
                'affected_record_id' => 0,
                'old_data' => 'New record',
                'new_data' => $new_data,
                'module' => 'Users'
            ];
           
            $this -> db -> query('SELECT * FROM '.$this->uschema.' WHERE user_id=:user_id and del_flag=1');
            $this -> db -> bind(':user_id',$data['user_id']);
            $exirow = $this -> db -> single();

            if($exirow) {
                return false;
            } else {
                //Prepare the statements
                $this -> db -> query('INSERT INTO '.$this->uschema.'(user_id,user_name,email_id,group_id,password,desg_id,dept_id,zn_id,rgn_id,area_id,br_id,br_ids,mobile,entered_by,hod_flag) 
                                        VALUES(:user_id,:user_name, :email_id, :group_id, :password,:desg_id,:dept_id,:zn_id,:rgn_id,:area_id,:br_id,:br_ids,:mobile,:entered_by,:hod_flag)');

                //Bind values
                $this -> db -> bind(':user_id',$data['user_id']);
                $this -> db -> bind(':user_name',$data['user_name']);
                $this -> db -> bind(':email_id',$data['email']);
                $this -> db -> bind(':group_id',$data['group_id']);
                $this -> db -> bind(':password',$data['password']);
                $this -> db -> bind(':desg_id',$data['desg_id']);
                $this -> db -> bind(':dept_id',$data['dept_id']);            
                $this -> db -> bind(':dept_id',$data['dept_id']);
                $this -> db -> bind(':zn_id',$zns);
                $this -> db -> bind(':rgn_id',$rgns);
                $this -> db -> bind(':area_id',$ars);
                $this -> db -> bind(':br_id',$brch);
                $this -> db -> bind(':br_ids',$brchs);
                $this -> db -> bind(':mobile',$data['mobile']);
                $this -> db -> bind(':entered_by',$_SESSION['user_id']);
                $this -> db -> bind(':hod_flag',$data['hod_flag']);

                if($this -> db -> execute()){
                    $this -> db -> query('SELECT * FROM '.$this->uschema.' WHERE user_id=:user_id and email_id=:email_id and del_flag=1');
                    $this -> db -> bind(':user_id',$data['user_id']);
                    $this -> db -> bind(':email_id',$data['email']);
                    $row = $this -> db -> single();
                    $record_no = $row -> id;
                    $auditdata['affected_record_id'] = $record_no;
                    if($this -> audit_trail($auditdata)){
                        return true;
                    }else {
                        return false;
                    }
                }else {
                    return false;
                }
            }
        }

        public function getstatus(){
            $columns = array(
                1 => 'group_id',              
                2 => 'usergroupname',
                3 => 'entered_by',
                4 => 'userenteredon',
                5 => 'flag',
                6 => 'editbtn',
                7 => 'delbtn'
            );  
            
            if(!empty($search)) 
                $where .=" AND (
                u.group_id like '".$search."%' OR
                ug.group_name like '".$search."%'
                ) ";

            $sql = 'SELECT u.*,ug.*,u.id as userid,u.user_id as user_id,
                u.del_flag as flag,
                u.group_id as group_id,
                ug.entered_by as entered_by,
                ug.group_name as usergroupname,
                COALESCE(u.entered_on,  to_timestamp(0)) as userenteredon,
                ug.id as userGroupId
                FROM '.$this->uschema.' u
                LEFT JOIN '.$this->rlschema.' ug
                ON u.group_id=ug.id WHERE u.group_id IN (7,8,13) order by group_id';

            $this -> db -> query($sql);
            $results = $this -> db -> resultSet();
            $numRows = count($results);
            //  return $results;
          $output = array(
            'numRows' => $numRows,
            'FinalData' => $results
          );
        return $output;      
        }

        public function getdesigstatus(){
            $columns = array(
                1 => 'desgid',              
                2 => 'designation',
                3 => 'entered_by',
                4 => 'userenteredon',
                5 => 'flag',
                6 => 'editbtn',
                7 => 'delbtn'
            );  
            
            if(!empty($search)) 
                $where .=" AND (
                de.id like '".$search."%' OR
                de.desg_name::text like '".$search."%' 
                ) ";

                $sql='SELECT u.*,ug.*,u.id as userid,
                u.user_id as user_id,
                de.entered_by as entered_by,
                u.del_flag as flag,
                de.id as desgid,
                de.desg_name as designation,
                COALESCE(u.entered_on,  to_timestamp(0)) as userenteredon
                FROM '.$this->uschema.' u
                LEFT JOIN '.$this->rlschema.'  ug
                ON u.group_id=ug.id
                LEFT JOIN '.$this->deschema.' de
                ON u.desg_id=de.id
                LEFT JOIN '.$this->dpschema.' dp
                ON u.dept_id=dp.id 
                WHERE u.group_id IN (7,8,13) order by desgid';

            $this -> db -> query($sql);
            $results = $this -> db -> resultSet();
            $numRows = count($results);
            //  return $results;
          $output = array(
            'numRows' => $numRows,
            'FinalData' => $results
          );
        return $output;      
        }

        public function getdeptstatus(){
            $columns = array(
                1 => 'deptid',              
                2 => 'department',
                3 => 'entered_by',
                4 => 'userenteredon',
                5 => 'flag',
                6 => 'editbtn',
                7 => 'delbtn'
            );  
            
            if(!empty($search)) 
                $where .=" AND (
                dp.id like '".$search."%' OR
                dp.dept_name::text like '".$search."%'
                ) ";

                $sql='SELECT u.id as userid,
                u.del_flag as flag,
                ug.id as userGroupId, 
                de.id as desgid,
                dp.id as deptid,
                dp.entered_by as entered_by,
                dp.dept_name as department,
                COALESCE(u.entered_on,  to_timestamp(0)) as userenteredon
                FROM '.$this->uschema.' u
                LEFT JOIN '.$this->rlschema.'  ug
                ON u.group_id=ug.id
                LEFT JOIN '.$this->deschema.' de
                ON u.desg_id=de.id
                LEFT JOIN '.$this->dpschema.' dp
                ON u.dept_id=dp.id 
                WHERE u.group_id IN (7,8,13) order by deptid';

            $this -> db -> query($sql);
            $results = $this -> db -> resultSet();
            $numRows = count($results);
            //  return $results;
          $output = array(
            'numRows' => $numRows,
            'FinalData' => $results
          );
        return $output;      
        }


        public function edituser($data){
            //Prepare audit trail data
            $user = $this -> getUserById($data['id']);
            $old_data = 'user_id:' . $user -> user_id . ', ' .'group_id:' . $user -> group_id . ', '. 'user_name:' . $user -> user_name . ', ' . 'email_id:' . $user -> email_id . ', ' . 'desg_id:' . $user -> desg_id . ', ' . 'dept_id:' . $user -> dept_id. ', ' . 'br_id:' . $user -> br_id. ', ' . 'br_ids:' . $user -> br_ids. ', ' . 'mobile:' . $user -> mobile. ', ' . 'hod_flag:' . $user -> hod_flag.','.'zn_ids:'.$user->zn_id.','.'rgn_ids:'.$user->rgn_id.','.'area_ids'.$user->area_id;
            $new_data = 'user_id:' . $data['user_id'] . ', ' .'group_id:' . $data['group_id'] . ', '. 'user_name:' . $data['user_name'] . ', ' . 'email_id:' . $data['email'] . ', ' . 'desg_id:' . $data['desg_id'] . ', ' . 'dept_id:' . $data['dept_id']. ', ' . 'mobile:' . $data['mobile']. ', ' . 'hod_flag:' . $data['hod_flag'];
            
            if(!empty($data['zn_ids'])) {
                $new_data .= 'zn_ids:' . implode(',', $data['zn_ids']);
                $zns = implode(',', $data['zn_ids']);
            } else {
                $new_data .= 'zn_ids:' . null;
                $zns = null;
            }

            if(!empty($data['rgn_ids'])) {
                $new_data .= 'rgn_ids:' . implode(',', $data['rgn_ids']);
                $rgns = implode(',', $data['rgn_ids']);
            } else {
                $new_data .= 'rgn_ids:' . null;
                $rgns = null;
            }

            if(!empty($data['area_ids'])) {
                $new_data .= 'area_ids:' . implode(',', $data['area_ids']);
                $ars = implode(',', $data['area_ids']);
            } else {
                $new_data .= 'area_ids:' . null;
                $ars = null;
            }        
            
            if(!empty($data['brch_id']) && ($data['group_id'] == 2 ||$data['group_id'] == 6)) {
                $new_data .= 'br_id:' . $data['brch_id'];
                $brch = $data['brch_id'];
            }                
            else {
                $new_data .= 'br_id:' . null;
                $brch = null;
            }
            if(!empty($data['brch_ids'])) {
                $new_data .= 'br_ids:' . implode(',', $data['brch_ids']);
                $brchs = implode(',', $data['brch_ids']);
            }                
            else {
                $new_data .= 'br_ids:' . null;
                $brchs = null;
            }
            $auditdata = [
                'user_id' => $_SESSION['user_id'],
                'action_type' => 'Edit',
                'affected_table' => 'users',
                'affected_record_id' => $data['id'],
                'old_data' => $old_data,
                'new_data' => $new_data,
                'module' => 'Users'
            ];
            //Prepare the statements
            $this -> db -> query('UPDATE '.$this->uschema.' SET 
                                    user_id = :user_id,
                                    user_name = :user_name,
                                    email_id = :email_id,
                                    group_id = :group_id,
                                    desg_id = :desg_id,
                                    dept_id = :dept_id,
                                    zn_id = :zn_id,
                                    rgn_id = :rgn_id,
                                    area_id = :area_id,
                                    br_id = :br_id,
                                    br_ids = :br_ids,
                                    mobile = :mobile,
                                    entered_by = :entered_by,
                                    hod_flag = :hod_flag
                                    WHERE id=:id' 
                                    );
            //Bind values
            $this -> db -> bind(':user_id',$data['user_id']);
            $this -> db -> bind(':user_name',$data['user_name']);
            $this -> db -> bind(':email_id',$data['email']);
            $this -> db -> bind(':group_id',$data['group_id']);
            $this -> db -> bind(':desg_id',$data['desg_id']);
            $this -> db -> bind(':dept_id',$data['dept_id']);
            $this -> db -> bind(':zn_id',$zns);
            $this -> db -> bind(':rgn_id',$rgns);
            $this -> db -> bind(':area_id',$ars);
            $this -> db -> bind(':br_id',$brch);
            $this -> db -> bind(':br_ids',$brchs);
            $this -> db -> bind(':mobile',$data['mobile']);
            $this -> db -> bind(':entered_by',$_SESSION['user_id']);
            $this -> db -> bind(':hod_flag',$data['hod_flag']);
            $this -> db -> bind(':id',$data['id']);

            //Execute and return 
            if($this -> db -> execute()){
                if($this -> audit_trail($auditdata)){
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }
        }

        public function updateMobile($mobile,$user_id) {
            $this -> db -> query('UPDATE '.$this->uschema.' SET 
            mobile = :mob,
            mobile_chk = :mobchk
            WHERE user_id=:id' 
            );
            $this -> db -> bind(':mob',$mobile);
            $this -> db -> bind(':mobchk',1);
            $this -> db -> bind(':id',$user_id);
            if($this -> db -> execute()){                
                return true;
            }else {
                return false;
            }
        }

        public function login($user_id, $user_pwd){
            $this -> db -> query('SELECT * FROM '.$this->uschema.' WHERE user_id=:user_id and del_flag=1');
            $this -> db -> bind(':user_id', $user_id);
            $row = $this -> db -> single();
            $hashed_password = $row -> password;
            if (password_verify($user_pwd, $hashed_password)){
                return $row;
            }else{
                return false;
            }
        }

        /* 
        public function login($user_id, $user_pwd){ old method
            $this -> db -> query('SELECT * FROM '.$this->uschema.' WHERE user_id=:user_id and password=:user_pwd and del_flag=1');
            $this -> db -> bind(':user_id', $user_id);
            $this -> db -> bind(':user_pwd', $user_pwd);
            $row = $this -> db -> single();
             return $row;
        } */

        //Find user by Email id
        public function findUserByEmail($email_id){
            $this -> db -> query('SELECT * FROM '.$this->uschema.' WHERE email_id=:email_id and del_flag=1');
            $this -> db -> bind(':email_id', $email_id);
            $row = $this -> db -> single();
            return $row;
            /*if($this -> db -> rowCount() > 0){
                return true;
            }else {
                return false;
            }*/
        }


        public function findUserByMobile($mobile) {
            $this -> db -> query('SELECT * FROM '.$this->uschema.' WHERE mobile=:mobile and del_flag=1');
            $this -> db -> bind(':mobile', $mobile);
            $row = $this -> db -> single();
            return $row;
        }

        public function UserByMobile($mobile) {
            $this -> db -> query('SELECT * FROM '.$this->uschema.' WHERE mobile=:mobile and del_flag=1 and user_id!=:usr');
            $this -> db -> bind(':mobile', $mobile);
            $this -> db -> bind(':usr', $_SESSION['user_id']);
            $row = $this -> db -> single();
            return $row;
        }
        
         //Find user by User id
         public function findUserByID($user_id){
            $this -> db -> query('SELECT * FROM '.$this->uschema.' WHERE user_id=:user_id and del_flag=1');
            $this -> db -> bind(':user_id', $user_id);
            $row = $this -> db -> single();
            return $row;           
        }

        // Get user name based on the User ID
        public function getUserById($userid){
            $this -> db -> query('SELECT * FROM '.$this->uschema.' WHERE id=:user_id and del_flag=1');
            $this -> db -> bind(':user_id', $userid);
            $row = $this -> db -> single();
            return $row;
        }

        

       
        // Get all the list of Users from the database
        public function getUsers($search,$order,$orderDir,$length,$start,$from,$to,$depts,$deptscnts,$usrgrp,$usrgrpcnts){
            $where = $sqlMul = $sqlTot = $sqlRec = "";
            $columns = array(
                1 => 'user_id',
                2 => 'user_name',
                3=> 'email_id',
                4 => 'designation',
                5 => 'department',
                6 => 'usergroupname',
                7 => 'userenteredon'
            );  
            
            if(!empty($search)) 
                $where .=" AND (
                u.user_id like '".$search."%' OR
                u.user_name::text like '".$search."%' OR
                u.email_id like '".$search."%' OR
                de.desg_name like '".$search."%' OR
                dp.dept_name like '".$search."%' OR
                ug.group_name like '".$search."%'

                ) ";

             if(!empty($from) && !empty($to))
             $where .=" AND (u.entered_on >='".$from."' AND u.entered_on <= '".$to."')";
             
             if(isset($depts) && $depts !== '0000') {
                if(!empty($deptscnts) && isset($deptscnts['0']) &&  $deptscnts['0'] > 0) { 
                    $where .=" AND (dept_id IN (".$depts.") OR dept_id is null) ";
                } else 
                    $where .=" AND dept_id IN (".rtrim($depts,",").") ";
             }  

             if(isset($usrgrp) && $usrgrp !== '0000') {
                if(!empty($usrgrpcnts) && isset($usrgrpcnts['0']) &&  $usrgrpcnts['0'] > 0) { 
                    $where .=" AND (group_id IN (".$usrgrp.") OR group_id is null) ";
                } else 
                    $where .=" AND group_id IN (".rtrim($usrgrp,",").") ";
             }         
            
            if($_SESSION['user_group'] == 12) {
               
                $sql='SELECT u.id as userid,
                u.user_id as user_id,
                u.user_name as user_name,
                u.email_id as  email_id,u.del_flag as flag,
                ug.id as userGroupId, 
                de.id as desgid,
                dp.id as deptid,
                ug.group_name as usergroupname,
                de.desg_name as designation,
                dp.dept_name as department,
                COALESCE(u.entered_on,  to_timestamp(0)) as userenteredon
                FROM '.$this->uschema.' u
                LEFT JOIN '.$this->rlschema.'  ug
                ON u.group_id=ug.id
                LEFT JOIN '.$this->deschema.' de
                ON u.desg_id=de.id
                LEFT JOIN '.$this->dpschema.' dp
                ON u.dept_id=dp.id 
                WHERE u.group_id IN (7,8,13) 
                ';

            //     $this -> db -> query('SELECT u.id as userid,
            //     u.user_id as user_id,
            //     u.user_name as user_name,
            //     u.email_id as  email_id,u.del_flag as flag,
            //     ug.id as userGroupId, 
            //     de.id as desgid,
            //     dp.id as deptid,
            //     ug.group_name as usergroupname,
            //     de.desg_name as designation,
            //     dp.dept_name as department,
            //     u.entered_on as userenteredon
            //     FROM '.$this->uschema.' u
            //     LEFT JOIN '.$this->rlschema.'  ug
            //     ON u.group_id=ug.id
            //     LEFT JOIN '.$this->deschema.' de
            //     ON u.desg_id=de.id
            //     LEFT JOIN '.$this->dpschema.' dp
            //     ON u.dept_id=dp.id 
            //     WHERE  u.group_id IN (7,8,13) '.$where.'
            //     ORDER BY u.user_name'
            // );
            } else {

                $sql='SELECT u.id as userid,
                u.user_id as user_id,
                u.user_name as user_name,
                u.email_id as  email_id,u.del_flag as flag,
                ug.id as userGroupId, 
                de.id as desgid,
                dp.id as deptid,
                ug.group_name as usergroupname,
                de.desg_name as designation,
                dp.dept_name as department,
                COALESCE(u.entered_on,  to_timestamp(0)) as userenteredon
                FROM '.$this->uschema.' u
                LEFT JOIN '.$this->rlschema.'  ug
                ON u.group_id=ug.id
                LEFT JOIN '.$this->deschema.' de
                ON u.desg_id=de.id
                LEFT JOIN '.$this->dpschema.' dp
                ON u.dept_id=dp.id 
                WHERE  u.group_id !=1  
                ';

                // $this -> db -> query('SELECT u.id as userid,
                // u.user_id as user_id,
                // u.user_name as user_name,
                // u.email_id as  email_id,u.del_flag as flag,
                // ug.id as userGroupId, 
                // de.id as desgid,
                // dp.id as deptid,
                // ug.group_name as usergroupname,
                // de.desg_name as designation,
                // dp.dept_name as department,
                // u.entered_on as userenteredon
                // FROM '.$this->uschema.' u
                // LEFT JOIN '.$this->rlschema.'  ug
                // ON u.group_id=ug.id
                // LEFT JOIN '.$this->deschema.' de
                // ON u.desg_id=de.id
                // LEFT JOIN '.$this->dpschema.' dp
                // ON u.dept_id=dp.id 
                // WHERE  u.group_id !=1  '.$where.'
                // ORDER BY u.user_name'
                // );
            }       
            
            $sqlTot .= $sql;
            $sqlRec .= $sql;
            if(isset($where) && $where != '') {
                $sqlTot .= $where;
                $sqlRec .= $where;                   
            }
            if(isset($order)){
                $sqlRec .= ' ORDER BY '.$columns[$order].' '.$orderDir.' ';
            } else {
                $sqlRec .= ' ORDER BY u.user_name ';
            }    
            if($length != -1){
                $sqlRec .= ' LIMIT ' . $length . ' OFFSET ' . $start;
            }   
            //echo $sqlRec;exit;      
            $this -> db -> query($sqlRec);
            $results=$this -> db -> resultSet();

            $this -> db -> query($sqlTot);
            $results1=$this -> db -> resultSet();
            $numRows = count($results1);
            //  return $results;
          $output = array(
            'numRows' => $numRows,
            'FinalData' => $results
          );
        return $output;
        }


        public function getBranchUsers() {
            $this -> db -> query("SELECT u.id as userid,
                                    u.user_id as user_id,
                                    u.user_name as user_name,
                                    u.email_id as  email_id,
                                    ug.id as userGroupId, 
                                    de.id as desgid,
                                    dp.id as deptid,
                                    ug.group_name as usergroupname,
                                    de.desg_name as designation,
                                    dp.dept_name as department,
                                    u.entered_on as userenteredon
                                    FROM ".$this->uschema." u
                                    LEFT JOIN ".$this->rlschema."  ug
                                    ON u.group_id=ug.id
                                    LEFT JOIN ".$this->deschema." de
                                    ON u.desg_id=de.id
                                    LEFT JOIN ".$this->dpschema." dp
                                    ON u.dept_id=dp.id 
                                    WHERE u.del_flag=1 and u.group_id =2 and u.br_id is not null  and user_id!='10002_bm'
                                    ORDER BY u.user_id"
                                );
            $results=$this -> db -> resultSet();
            return $results;
        }

        public function getClientslist($search,$order,$orderDir,$length,$start,$label,$fromDate,$toDate) {
            $where = $sqlMul = $sqlTot = $sqlRec = "";              
        
            if(!empty($search) ) 
                $where .=" AND (client_id ILIKE '".$search."%' OR
                spouse_nm ILIKE '".$search."%' OR
                marital_status ILIKE '".$search."%' OR
                mobile_no ILIKE '".$search."%' OR
                status ILIKE '".$search."%' OR
                entered_on ILIKE '".$search."%' OR
                updated_on ILIKE '".$search."%'  ) ";

              
            if(!empty($fromDate) && !empty($toDate))
                $where .=" AND (lq.cre_on_ts >='".$fromDate."' AND lq.cre_on_ts <= '".$toDate."')";  
            else {
                if(!empty($fromDate)) {
                    $where .=" AND (lq.cre_on_ts >='".$fromDate."')";
                }
    
                if(!empty($toDate)) {
                    $where .=" AND (lq.cre_on_ts <='".$toDate."')";
                }
            }

            $where .= " AND q.p_status in ('hoa','riskapprove')";

            if ($label == 'nm')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.first_name as label, 
                        mc.display_name as finflux_label, lq.cre_on_ts FROM bc_int.qc q
                        inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                        inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                        left join pragatifin.m_client mc on mc.id = q.cust_id 
                        WHERE uc.del_flag=1 AND COALESCE(uc.first_name,'')!='' ";
                
            if ($label == 'msts')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.marital_status as label, 
                        mar.code_value as finflux_label, lq.cre_on_ts FROM bc_int.qc q
                        inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                        inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                        left join pragatifin.m_client mc on mc.id = q.cust_id 
                        left join pragatifin.m_code_value mar on mar.id = mc.marital_status_cv_id
                        WHERE uc.del_flag=1 AND COALESCE(uc.marital_status,'')!='' ";
            
            if ($label == 'mob')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.mobile_no as label, 
                        mc.mobile_no as finflux_label, lq.cre_on_ts FROM bc_int.qc q
                        inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                        inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                        left join pragatifin.m_client mc on mc.id = q.cust_id 
                        WHERE uc.del_flag=1 AND COALESCE(uc.mobile_no::text,'')!='' ";
               
            if ($label == 'cdob')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.dob as label, 
                        mc.date_of_birth as finflux_label, lq.cre_on_ts FROM bc_int.qc q
                        inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                        inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                        left join pragatifin.m_client mc on mc.id = q.cust_id 
                        WHERE uc.del_flag=1 AND COALESCE(uc.dob::text,'')!='' ";
                
            if ($label == 'snm')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.spouse_nm as label, 
                        concat(coalesce(fam.firstname,' '),coalesce(fam.middlename,' '),coalesce(fam.lastname,' ')) as finflux_label, lq.cre_on_ts FROM bc_int.qc q
                        inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                        inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                        left join pragatifin.f_loan_application_reference flar on flar.id = q.loan_appln_id
                        left join pragatifin.f_family_details fam on fam.client_id = flar.client_id AND fam.relationship_cv_id in (5,21,27)
                        WHERE uc.del_flag=1 AND COALESCE(uc.spouse_nm,'')!='' ";

            if ($label == 'sdob')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.spouse_dob as label, 
                        fam.date_of_birth as finflux_label, lq.cre_on_ts FROM bc_int.qc q
                        inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                        inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                        left join pragatifin.f_loan_application_reference flar on flar.id = q.loan_appln_id
                        left join pragatifin.f_family_details fam ON fam.client_id = flar.client_id AND fam.relationship_cv_id = 21
                        WHERE uc.del_flag=1 AND COALESCE(uc.spouse_dob::text,'')!='' ";

            if ($label == 'nnm')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.nominee_nm as label, 
                        concat(coalesce(de.firstname,' '),coalesce(de.middlename,' '),coalesce(de.lastname,' ')) as finflux_label, lq.cre_on_ts FROM bc_int.qc q
                        inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                        inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                        left join pragatifin.f_loan_application_reference flar on flar.id = q.loan_appln_id
                        left join pragatifin.f_loan_application_nominee fno on fno.loan_application_id = flar.id
                        left join pragatifin.f_family_details de on de.id = fno.family_member_id
                        WHERE uc.del_flag=1 AND COALESCE(uc.nominee_nm,'')!='' ";

            if ($label == 'ndob')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.nominee_dob as label, 
                        de.date_of_birth as finflux_label, lq.cre_on_ts FROM bc_int.qc q
                        inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                        inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                        left join pragatifin.f_loan_application_reference flar on flar.id = q.loan_appln_id
                        left join pragatifin.f_loan_application_nominee fno on fno.loan_application_id = flar.id
                        left join pragatifin.f_family_details de on de.id = fno.family_member_id
                        WHERE uc.del_flag=1 AND COALESCE(uc.nominee_dob::text,'')!='' ";

            if ($label == 'nrela')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.nominee_relation as label, 
                        rlnt.code_value as finflux_label, lq.cre_on_ts FROM bc_int.qc q
                        inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                        inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                        left join pragatifin.f_loan_application_reference flar on flar.id = q.loan_appln_id
                        left join pragatifin.f_loan_application_nominee fno on fno.loan_application_id = flar.id
                        left join pragatifin.f_family_details de on de.id = fno.family_member_id
                        left join pragatifin.m_code_value rlnt on rlnt.id=de.relationship_cv_id
                        WHERE uc.del_flag=1 AND COALESCE(uc.nominee_relation,'')!='' ";

            if ($label == 'ngndr')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.nominee_gender as label, 
                        gndr.code_value as finflux_label, lq.cre_on_ts FROM bc_int.qc q
                        inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                        inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                        left join pragatifin.f_loan_application_reference flar on flar.id = q.loan_appln_id
                        left join pragatifin.f_loan_application_nominee fno on fno.loan_application_id = flar.id
                        left join pragatifin.f_family_details de on de.id = fno.family_member_id
                        left join pragatifin.m_code_value gndr on gndr.id=de.gender_cv_id
                        WHERE uc.del_flag=1 AND COALESCE(uc.nominee_gender,'')!='' ";

            if ($label == 'inc')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.ins_new_type as label, 
                case when snp.ins_type is not null and snp.ins_type in ('insurance charge double (topup)'
                   ,'Insurance Premium (Double)') then 'Double'
               when snp.ins_type is not null and snp.ins_type in ('Insurance charge single (topup)'
                   ,'Insurance Premium (Single)') then 'Single'				
               when (last_value(ch.name) over (partition by q.cust_id order by q.cre_ts 
                   range between unbounded PRECEDING and UNBOUNDED FOLLOWING))
                    in ('insurance charge double (topup)'
                    ,'Insurance Premium (Double)') then 'Double'
               when (last_value(ch.name) over (partition by q.cust_id order by q.cre_ts 
                    range between unbounded PRECEDING and UNBOUNDED FOLLOWING))
                    in ('Insurance charge single (topup)' ,'Insurance Premium (Single)') then 'Single' end finflux_label,
                    lq.cre_on_ts FROM bc_int.qc q
                               inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                               inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                               left join pragatifin.f_loan_application_reference flar on flar.id= q.loan_appln_id
                               left join pragatifin.f_loan_application_charge fc on fc.loan_app_ref_id = flar.id
                               inner join pragatifin.m_charge ch ON ch.id = fc.charge_id AND ch.name LIKE '%nsurance%'
                               left join bc_int.loan_snpsht snp on snp.loan_id = flar.loan_id
                               WHERE uc.del_flag=1 AND COALESCE(uc.ins_new_type,'')!='' ";   

            if ($label == 'hopi')
                $sql = "SELECT q.loan_appln_id,q.cust_nm,uc.client_id,uc.hospi_new_type as label, 
                case when ch.name='Hospicash Double' then 'Double'
                when ch.name='Hospicash Single' then 'Single' else ch.name end finflux_label, 
                lq.cre_on_ts FROM bc_int.qc q
                inner join bc_int.updated_clnt_data uc on q.cust_id=uc.client_id::bigint 
                inner join bc_int.loan_qc lq on q.loan_appln_id=lq.loan_appln_id and lq.cur_rec_flag='y'
                left join pragatifin.f_loan_application_reference flar on flar.id= q.loan_appln_id
                left join pragatifin.f_loan_application_charge fc on fc.loan_app_ref_id = flar.id
                inner join pragatifin.m_charge ch ON ch.id = fc.charge_id AND ch.name LIKE '%ospicash%'
                WHERE uc.del_flag=1 AND COALESCE(uc.hospi_new_type,'')!='' ";


            $sqlTot .= $sql;
            $sqlRec .= $sql;
            if(isset($where) && $where != '') {
                $sqlTot .= $where;
                $sqlRec .= $where;                   
            }
            /* if(isset($order)){
                $sqlRec .= ' ORDER BY '.$columns[$order].' '.$orderDir.' ';
            } else { */
                $sqlRec .= ' ORDER BY uc.client_id DESC ';
           // }    
            if($length != -1){
                $sqlRec .= ' LIMIT ' . $length . ' OFFSET ' . $start;
            }   
            
           /*   echo $sqlRec;
            die();  */
        
            $this -> db -> query($sqlRec);
            $results=$this -> db -> resultSet();

            $this -> db -> query($sqlTot);
            $results1=$this -> db -> resultSet();

            $numRows = count($results1);
        
            $output = array(
                'numRows' => $numRows,
                'FinalData' => $results
            );
            return $output;
        }

        public function findClntByID($cid,$id) {
            if($id > 0) {
                $this -> db -> query('SELECT * FROM '.$this->clntschema.' WHERE client_id=:user_id and id!=:id and del_flag=1');
                $this -> db -> bind(':user_id', $cid);
                $this -> db -> bind(':id', $id);
                $row = $this -> db -> single();
                return $row; 
                
            } else {
                $this -> db -> query('SELECT * FROM '.$this->clntschema.' WHERE client_id=:user_id and del_flag=1');
                $this -> db -> bind(':user_id', $cid);
                $row = $this -> db -> single();
                return $row;
            }
        }

        public function findClntByMobile($mobile,$id) {
            if($id > 0) {
                $this -> db -> query('SELECT * FROM '.$this->clntschema.' WHERE mobile_no=:mobile and id!=:id and del_flag=1');
                $this -> db -> bind(':mobile', $mobile);
                $this -> db -> bind(':id', $id);
                $row = $this -> db -> single();
                return $row; 
            } else {
                $this -> db -> query('SELECT * FROM '.$this->clntschema.' WHERE mobile_no=:mobile and del_flag=1');
                $this -> db -> bind(':mobile', $mobile);
                $row = $this -> db -> single();
                return $row;               
            }
        }

        public function addclient($data){
            $this -> db -> query('INSERT INTO '.$this->clntschema.'(client_id,spouse_nm,spouse_dob,marital_status,mobile_no,status,dob,entered_by,entered_on,br_id) 
                                    VALUES(:clnt_id,:s_nm, :s_dob, :m_status, :mobile,:status,:dob,:entered_by,:entered_on,:br_id)');
            //Bind values
            $this -> db -> bind(':clnt_id',$data['clnt_id']);
            $this -> db -> bind(':s_nm',$data['spouse_nm']);
            $this -> db -> bind(':s_dob',date('Y-m-d', strtotime($data['spouse_dob'])));
            $this -> db -> bind(':m_status',$data['m_status']);
            $this -> db -> bind(':mobile',$data['mobile']);
            $this -> db -> bind(':status','new');
            $this -> db -> bind(':dob',date('Y-m-d', strtotime($data['dob'])));
            $this -> db -> bind(':entered_by',$_SESSION['user_id']);
            $this -> db -> bind(':entered_on',date('Y-m-d H:i:s'));
            $this -> db -> bind(':br_id',$_SESSION['br_id']);

            if($this -> db -> execute()){
                return true;               
            } else {
                return false;
            }
        }

        public function getClntById($id) {
            $this -> db -> query('SELECT * FROM '.$this->clntschema.' WHERE id=:id and del_flag=1');
            $this -> db -> bind(':id', $id);
            $row = $this -> db -> single();
            return $row;
        }

        public function editclient($data) {            
            $this -> db -> query('UPDATE '.$this->clntschema.' SET 
            client_id = :clnt_id,
            spouse_nm = :s_nm,
            spouse_dob = :s_dob,
            marital_status = :m_status,
            mobile_no = :mobile,
            dob = :dob,
            entered_by = :entered_by,
            entered_on = :entered_on,
            br_id= :br_id
            WHERE id=:id' 
            );
            //Bind values
            $this -> db -> bind(':clnt_id',$data['clnt_id']);
            $this -> db -> bind(':s_nm',$data['spouse_nm']);
            $this -> db -> bind(':s_dob',date('Y-m-d', strtotime($data['spouse_dob'])));
            $this -> db -> bind(':m_status',$data['m_status']);
            $this -> db -> bind(':mobile',$data['mobile']);
            $this -> db -> bind(':dob',date('Y-m-d', strtotime($data['dob'])));
            $this -> db -> bind(':entered_by',$_SESSION['user_id']);
            $this -> db -> bind(':entered_on',date('Y-m-d H:i:s'));
            $this -> db -> bind(':br_id',$_SESSION['br_id']);
            $this -> db -> bind(':id',$data['id']);
           
            //Execute and return 
            if($this -> db -> execute()){
                return true;
            }else {
                return false;
            }
        }

        public function approveclient($id) {
            $this -> db -> query('UPDATE '.$this->clntschema.' SET 
            status = :status,
            updated_by = :entered_by,
            updated_on = :entered_on
            WHERE id=:id' 
            );
            //Bind values
            $this -> db -> bind(':status','updated');
            $this -> db -> bind(':entered_by',$_SESSION['user_id']);
            $this -> db -> bind(':entered_on',date('Y-m-d H:i:s'));
            $this -> db -> bind(':id',$id);           
            //Execute and return 
            if($this -> db -> execute()){
                return true;
            }else {
                return false;
            }
        }

        public function getClntsByDate($from_date,$status) {
            if($status == 'new') {
                if($_SESSION['user_group'] == 2) {
                    $this -> db -> query("SELECT * FROM ".$this->clntschema." WHERE DATE(entered_on)=:en_date and status=:status and br_id=:br_id ORDER BY client_id");
                    $this -> db -> bind(':en_date', $from_date);
                    $this -> db -> bind(':status', $status);
                    $this -> db -> bind(':br_id', $_SESSION['br_id']);
                    $rows = $this -> db -> resultSet();
                    return $rows;
                } else {                    
                    $this -> db -> query("SELECT * FROM ".$this->clntschema." WHERE DATE(entered_on)=:en_date and status=:status ORDER BY client_id");
                    $this -> db -> bind(':en_date', $from_date);
                    $this -> db -> bind(':status', $status);
                    $rows = $this -> db -> resultSet();
                    return $rows;
                }
            }else {
                if($_SESSION['user_group'] == 2) {
                    $this -> db -> query("SELECT * FROM ".$this->clntschema." WHERE DATE(updated_on)=:en_date and status=:status and br_id=:br_id ORDER BY client_id");
                    $this -> db -> bind(':en_date', $from_date);
                    $this -> db -> bind(':status', $status);
                    $this -> db -> bind(':br_id', $_SESSION['br_id']);
                    $rows = $this -> db -> resultSet();
                    return $rows;
                } else {                    
                    $this -> db -> query("SELECT * FROM ".$this->clntschema." WHERE DATE(updated_on)=:en_date and status=:status ORDER BY client_id");
                    $this -> db -> bind(':en_date', $from_date);
                    $this -> db -> bind(':status', $status);
                    $rows = $this -> db -> resultSet();
                    return $rows;
                }
            }
        }

        // ********** This code is for User Group **************************
        public function getUserGroups(){
            if($_SESSION['user_group'] == 12) { 
                $this -> db -> query('SELECT *,coalesce(entered_on, to_timestamp(0)) as entered_on FROM '.$this->rlschema.' WHERE del_flag=1 and id in (7,8,13) ORDER BY group_name');
                $rows = $this -> db -> resultSet();
                return $rows;
            }else {
                $this -> db -> query('SELECT *,coalesce(entered_on, to_timestamp(0)) as entered_on FROM '.$this->rlschema.' WHERE del_flag=1 ORDER BY group_name');
                $rows = $this -> db -> resultSet();
                return $rows;
            }
        }

        public function getGroupByName($group_name){
            
            $this -> db -> query('SELECT * FROM '.$this->rlschema.' WHERE group_name=:group_name');
            $this -> db -> bind(':group_name', $group_name);
            $row = $this -> db -> single();
            return $row;
        }

        public function addUserGroup($data){
            //Prepare Audit Trail data
            $new_data = 'group_name:' . $data['group_name'];
            $auditdata = [
                'user_id' => $_SESSION['user_id'],
                'action_type' => 'Insert',
                'affected_table' => 'usergroup',
                'affected_record_id' => 0,
                'old_data' => 'New record',
                'new_data' => $new_data,
                'module' => 'Users'
            ];

            $this -> db -> query('SELECT * FROM '.$this->rlschema.' WHERE group_name=:group_name');
            $this -> db -> bind(':group_name',$data['group_name']);
            $exirow = $this -> db -> single();

            if($exirow) {
                return false;
            } else {
                $this -> db -> query('INSERT INTO '.$this->rlschema.'(group_name,entered_by) VALUES(:group_name,:entered_by)');
                //Bind values
                $this -> db -> bind(':group_name',$data['group_name']);
                $this -> db -> bind(':entered_by', $_SESSION['user_id']);
                //Execute 
    
                if($this -> db -> execute()){
                    $this -> db -> query('SELECT * FROM '.$this->rlschema.' WHERE group_name=:group_name and del_flag=1');
                    $this -> db -> bind(':group_name',$data['group_name']);
                    $row = $this -> db -> single();
                    $record_no = $row -> id;
                    $auditdata['affected_record_id'] = $record_no;
                    //Saving audit trail data
                    if($this -> audit_trail($auditdata)){
                        return true;
                    }else {
                        return false;
                    }
                }else {
                    return false;
                }
            }
            
        }

        public function getGroupById($id){            
            $this -> db -> query('SELECT * FROM '.$this->rlschema.' WHERE id=:id');
            $this -> db -> bind(':id', $id);
            $row = $this -> db -> single();
            return $row;
        }       
       

        public function editUserGroup($data){
             //Prepare audit trail data
             $usergroup = $this -> getGroupById($data['id']);
             $old_data = 'group_name:' . $usergroup -> group_name;
             $new_data = 'group_name:' . $data['group_name'];
             $auditdata = [
                 'user_id' => $_SESSION['user_id'],
                 'action_type' => 'Edit',
                 'affected_table' => 'usergroup',
                 'affected_record_id' => $data['id'],
                 'old_data' => $old_data,
                 'new_data' => $new_data,
                 'module' => 'Users'
             ];
            
            $this -> db -> query('UPDATE '.$this->rlschema.' SET group_name =:group_name WHERE id=:id');
            //Bind values
            $this -> db -> bind(':group_name',$data['group_name']);
            $this -> db -> bind(':id',$data['id']);
            //Execute 

            if($this -> db -> execute()){
                if($this -> audit_trail($auditdata)){
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }
        }

        

        // ********** This code is for User Group **************************

        
        // ********** This code is for Designation **************************
        public function getDesignations(){
            //echo ' you are in this function';
            $this -> db -> query('SELECT d.*, u.user_name FROM '.$this->deschema.' d left join bc_int.users u on u.user_id=d.entered_by WHERE d.del_flag=1 ORDER BY d.desg_name');
            $rows = $this -> db -> resultSet();
            return $rows;
        }

        public function getDesignaitonById($id){            
            $this -> db -> query('SELECT * FROM '.$this->deschema.' WHERE id=:id');
            $this -> db -> bind(':id', $id);
            $row = $this -> db -> single();
            return $row;
        }

        public function getDesignationByName($desg_name){
            
            $this -> db -> query('SELECT * FROM '.$this->deschema.' WHERE desg_name=:desg_name');
            $this -> db -> bind(':desg_name', $desg_name);
            $row = $this -> db -> single();
            return $row;
        }

        public function addDesignation($data){
            //Prepare for audit trail
            $new_data = 'desg_name:' . $data['desg_name'];
            $auditdata = [
                'user_id' => $_SESSION['user_id'],
                'action_type' => 'Insert',
                'affected_table' => 'designation',
                'affected_record_id' => 0,
                'old_data' => 'New record',
                'new_data' => $new_data,
                'module' => 'Users'
            ];

            $this -> db -> query('SELECT * FROM '.$this->deschema.' WHERE desg_name=:desg_name');
            $this -> db -> bind(':desg_name',$data['desg_name']);
            $exirow = $this -> db -> single();

            if($exirow) {
                return false;
            } else {
                $this -> db -> query('INSERT INTO '.$this->deschema.'(desg_name,entered_by) VALUES(:desg_name,:entered_by)');
                //Bind values
                $this -> db -> bind(':desg_name',$data['desg_name']);
                $this -> db -> bind(':entered_by', $_SESSION['user_id']);
                //Execute 
                if($this -> db -> execute()){
                    $this -> db -> query('SELECT * FROM '.$this->deschema.' WHERE desg_name=:desg_name and del_flag=1');
                    $this -> db -> bind(':desg_name',$data['desg_name']);
                    $row = $this -> db -> single();
                    $record_no = $row -> id;
                    $auditdata['affected_record_id'] = $record_no;
                    //Saving audit trail data
                    if($this -> audit_trail($auditdata)){
                        return true;
                    }else {
                        return false;
                    }
                }else {
                    return false;
                }
            }
            
        }

        public function editDesignation($data){
             //Prepare audit trail data
             $designation = $this -> getDesignaitonById($data['id']);
             $old_data = 'desg_name:' . $designation -> desg_name;
             $new_data = 'desg_name:' . $data['desg_name'];
             $auditdata = [
                 'user_id' => $_SESSION['user_id'],
                 'action_type' => 'Edit',
                 'affected_table' => 'designation',
                 'affected_record_id' => $data['id'],
                 'old_data' => $old_data,
                 'new_data' => $new_data,
                 'module' => 'Users'
             ];
           
            $this -> db -> query('UPDATE '.$this->deschema.' SET desg_name =:desg_name WHERE id=:id');
            //Bind values
            $this -> db -> bind(':desg_name',$data['desg_name']);
            $this -> db -> bind(':id',$data['id']);
            //Execute 

            if($this -> db -> execute()){
                if($this -> audit_trail($auditdata)){
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }
        }

        
        // ********** This code is for Designation **************************


        // ********** This code is for Department **************************
        public function getDepartments(){
            //echo ' you are in this function';
            $this -> db -> query('SELECT * FROM '.$this->dpschema.' WHERE del_flag=1 ORDER BY dept_name');
            $rows = $this -> db -> resultSet();
            return $rows;
        }

        public function getDepartmentById($id){
            
            $this -> db -> query('SELECT * FROM '.$this->dpschema.' WHERE id=:id');
            $this -> db -> bind(':id', $id);
            $row = $this -> db -> single();
            return $row;
        }

        public function getDepartmentByName($dept_name){
            
            $this -> db -> query('SELECT * FROM '.$this->dpschema.' WHERE dept_name=:dept_name');
            $this -> db -> bind(':dept_name', $dept_name);
            $row = $this -> db -> single();
            return $row;
        }

        public function addDepartment($data){
            //Prepare for audit trail
            $new_data = 'dept_name:' . $data['dept_name'];
            $auditdata = [
                 'user_id' => $_SESSION['user_id'],
                 'action_type' => 'Insert',
                 'affected_table' => 'department',
                 'affected_record_id' => 0,
                 'old_data' => 'New record',
                 'new_data' => $new_data,
                 'module' => 'Users'
            ];

            $this -> db -> query('SELECT * FROM '.$this->dpschema.' WHERE dept_name=:dept_name');
            $this -> db -> bind(':dept_name',$data['dept_name']);
            $exirow = $this -> db -> single();

            if($exirow) {
                return false;
            } else {
                $this -> db -> query('INSERT INTO '.$this->dpschema.'(dept_name,entered_by) VALUES(:dept_name,:entered_by)');
                //Bind values
                $this -> db -> bind(':dept_name',$data['dept_name']);
                $this -> db -> bind(':entered_by', $_SESSION['user_id']);
                //Execute 
                if($this -> db -> execute()){
                    $this -> db -> query('SELECT * FROM '.$this->dpschema.' WHERE dept_name=:dept_name and del_flag=1');
                    $this -> db -> bind(':dept_name',$data['dept_name']);
                    $row = $this -> db -> single();
                    $record_no = $row -> id;
                    $auditdata['affected_record_id'] = $record_no;
                    //Saving audit trail data
                    if($this -> audit_trail($auditdata)){
                        return true;
                    }else {
                        return false;
                    }
                }else {
                    return false;
                }
            }
            
        }

        public function editDepartment($data){
            //Prepare audit trail data
            $department = $this -> getDepartmentById($data['id']);
            $old_data = 'dept_name:' . $department -> dept_name;
            $new_data = 'dept_name:' . $data['dept_name'];
            $auditdata = [
                'user_id' => $_SESSION['user_id'],
                'action_type' => 'Edit',
                'affected_table' => 'department',
                'affected_record_id' => $data['id'],
                'old_data' => $old_data,
                'new_data' => $new_data,
                'module' => 'Users'
            ];



            $this -> db -> query('UPDATE '.$this->dpschema.' SET dept_name =:dept_name WHERE id=:id');
            //Bind values
            $this -> db -> bind(':dept_name',$data['dept_name']);
            $this -> db -> bind(':id',$data['id']);
            //Execute 

            if($this -> db -> execute()){
                //Saving audit trail data
                if($this -> audit_trail($auditdata)){
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }
        }


        // ********** This code is for Department **************************

        public function saveLogInfo($id,$inorout){
            $mymonth = date('F-Y');
            $myyear = date('Y');
            $mydate = date('Y-m-d');
            $mydatetime = date('Y-m-d H:i:s');
            $this -> db -> query('INSERT INTO '.$this->ulschema.' (user_id,logged_date,logged_date_time,in_or_out,month_name,year)
                                    VALUES(:user_id,:logged_date,:logged_date_time,:in_or_out,:month_name,:year)');
            $this -> db -> bind(':user_id',$id);
            $this -> db -> bind(':logged_date',$mydate);
            $this -> db -> bind(':logged_date_time',$mydatetime);
            $this -> db -> bind(':in_or_out',$inorout);
            $this -> db -> bind('month_name',$mymonth);
            $this -> db -> bind(':year',$myyear);
            if($this -> db -> execute()){
                return true;
            }else {
                return false;
            }
        }

        public function getReportings(){
            $this -> db -> query('SELECT * FROM '.$this->uschema.' WHERE reportingauthority=:reportingauthority ORDER BY name');
            $this -> db -> bind(':reportingauthority','Yes');
            $rows = $this -> db -> resultSet();
            return $rows;
        }

        public function resetPWD($id,$user_id){
            //Prepare audit trail data
            $auditdata = [
                'user_id' => $_SESSION['user_id'],
                'action_type' => 'Password Reset',
                'affected_table' => 'users',
                'affected_record_id' => $id,
                'old_data' => 'Password reset by admin',
                'new_data' => 'Password reset by admin'
            ];

            $password = password_hash($user_id, PASSWORD_DEFAULT);
            $this -> db -> query('UPDATE '.$this->uschema.' SET password=:password WHERE id=:id');
            $this -> db -> bind(':password',$password);
            $this -> db -> bind(':id',$id);
            if($this -> db -> execute()){
                //Saving audit trail data

                if($this -> audit_trail($auditdata)){
                    return true;
                }else {
                    return false;
                }
            }else{
                return false;
            }
        }

        public function changePWD($data){
            //Prepare audit trail data
            $auditdata = [
                'user_id' => $data['user_id'],
                'action_type' => 'Password Change',
                'affected_table' => 'users',
                'affected_record_id' => $data['id'],
                'old_data' => 'Password changed by user',
                'new_data' => 'Password changed by user',
                'module' => 'user'
            ];

            $this -> db -> query('UPDATE '.$this->uschema.' SET password=:password, password_updated_at=:curdt WHERE id=:id');
            $this -> db -> bind(':password',$data['password']);
            $this -> db -> bind(':curdt',date('Y-m-d'));
            $this -> db -> bind(':id',$data['id']);
            if($this -> db -> execute()){
                //Saving audit trail data
                if($this -> audit_trail($auditdata)){
                    return true;
                }else {
                    return false;
                }
            }else{
                return false;
            }
        }

        public function getAuditTrails($page,$limit,$start){
            
            $qryStr = "SELECT * FROM ".$this->adschema." ORDER BY action_time DESC LIMIT $limit OFFSET $start";
            $this -> db -> query($qryStr);
            $rows = $this -> db -> resultSet();
            $rowcount = count($rows);
            return $rows;
        }

        public function getAuditTrailRecords(){
            $this -> db -> query('SELECT * FROM '.$this->adschema.' ORDER BY action_time');
            $rows = $this -> db -> resultSet();
            $rowcount = count($rows);
            return $rowcount;
        }

        public function getAuditTrailDataById($id){
            $this -> db -> query('SELECT *, u.user_id as userid,
                                    u.user_name as user_name
                                    FROM '.$this->adschema.' a
                                    INNER JOIN '.$this->uschema.' u
                                    ON a.user_id = u.user_id  AND del_flag=1 
                                    WHERE a.id=:id');
            $this -> db -> bind(':id',$id);
            $row = $this -> db -> single();
            return $row;
        }


        public function hashpassword($data) {
            $this -> db -> query('SELECT * FROM bc_int.users WHERE user_id=:id');
            $this -> db -> bind(':id',$data[0]);
            $row = $this -> db -> single(); 

            if($row) {
                $password = password_hash($data[1], PASSWORD_DEFAULT);
                $this -> db -> query('UPDATE bc_int.users SET password=:pwd WHERE user_id=:user_id');
                $this -> db -> bind(':pwd',$password);
                $this -> db -> bind(':user_id',$data[0]);
                if($this -> db -> execute()) {
                    return true;
                } else {
                    return false;
                }                
            } else {           
                return false;
            }    
        }

        public function changemobile($data) {
            $this -> db -> query('SELECT * FROM bc_int.users WHERE user_id=:id');
            $this -> db -> bind(':id',$data[0]);
            $row = $this -> db -> single(); 

            if($row) {
                $this -> db -> query('UPDATE bc_int.users SET desg_id=:desg_id,mobile=:mobile,mobile_chk=:mobile_chk WHERE user_id=:user_id');
                $this -> db -> bind(':desg_id',26);
                $this -> db -> bind(':mobile',$data[1]);
                $this -> db -> bind(':mobile_chk',1);
                $this -> db -> bind(':user_id',$data[0]);
                if($this -> db -> execute()) {
                    return true;
                } else {
                    return false;
                }                
            } else {           
                return false;
            }  
        }

        public function chgflag($userid,$flag){
            
            if($flag==1){
                $old_data='Inactive';
                $new_data='Active';
            }else if($flag==0){
                $old_data='Active';
                $new_data='Inactive';
            }
            $auditdata = [
                'user_id' => $_SESSION['user_id'],
                'action_type' => $new_data,
                'affected_table' => 'users',
                'affected_record_id' => $userid,
                'old_data' =>$old_data,
                'new_data' => $new_data,
                'module' => 'Users'
            ];
            
            $this -> db -> query('UPDATE bc_int.users SET del_flag=:del_flag WHERE id=:user_id');
            $this -> db -> bind(':del_flag',$flag);
            $this -> db -> bind(':user_id',$userid);
           // $row = $this -> db -> single();
            //return $row;
            if($this -> db -> execute()){
                if($this -> audit_trail($auditdata)){
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }           
        }
        
    }
