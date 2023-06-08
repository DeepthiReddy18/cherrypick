<?php require APPROOT . '/views/inc/header.php'; 
require_once APPROOT .'/helpers/deleteModal.php';?>
<link rel="stylesheet" href="http://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
<script>
    function usrchk(id){
       // alert(id)
        var flag=0;
        if($("#flag"+id).prop('checked') == true){
            flag=1;
            //$("#flagtxt"+id).text("Active")
        }else{
            flag=0;
            //$("#flagtxt"+id).text("Inactive")
        }
        $.ajax({
            "url":"<?php echo URLROOT; ?>/users/chgflag",
                type: "post",
                data: {usrid:id,flag:flag},
                success: function(d) {
                }
            });
    }   
</script>
<style>

.table > thead > tr > th,
.table > thead > tr > td {
font-size: .9em;
font-weight: 400;
border-bottom: 0;
letter-spacing: 1px;
vertical-align: top;
padding: 8px;
background: #51596a;;
color: #ffffff;
}
.header {
    position: sticky;
    top:0;
}
</style>
<div class="container-fluid my-5 py-4">
<?php flashMsg('DESIGNATION'); ?>
   <div class="row mb-3">
        <div class="col-md-6">
            <h4>Designations</h4>
        </div>

        <div class="col-md-6">
            <a href="<?php echo URLROOT; ?>/users/adddesignation" class="btn btn-primary pull-right" title="Add new Designation">
                <i class="fas fa-plus-circle"></i>
            </a>
        </div>
    </div> 

    

    <table id= "designations" class="table table-bordered table-striped table-fixed" style="width:100%">
        <thead style="position: sticky;top: 0">
            <tr>
                <th scope="col" style="text-align:center;">S No</th>
                <th scope="col">Designation</th>
                <th scope="col" style="text-align:center;">Entered By</th>
                <th scope="col" style="text-align:center;">Entered On</th>
                <th scope="col" style="width:5px;">Active/Inactive</th>
                <th scope="col" style="text-align:center;">Edit</th>
                <th scope="col" style="text-align:center;">Delete</th>
            </tr>
        </thead>
    <?php $rowCount=0; ?>

    </table>
    
    </div>
    <script>
    $(document).ready(function() {
        $('#designations').DataTable({
            "scrollY": 600,        
            "scrollX": true,         
            "scroller": true,
            "scrollCollapse": true,
            "searching": true,
            "lengthChange": false,
            "lengthMenu": [ [-1, 10, 25, 50, 100], ["All", 10, 25, 50, 100] ],
            "info": false,
            "paging": true,
            "processing": true,
            "serverSide":true,        
            "language": {
                "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
                "searchPlaceholder": "Search here"
            },
            "order": [[ 1, "asc" ]],
            "ajax":{
                "url":"<?php echo URLROOT; ?>/users/desig_status",
                "type":"POST"  
            },       
            'columns': [
                { data: 'desgid',width: '2px' },
                { data: 'designation'}, 
                { data: 'entered_by'},                         
                { data: 'userenteredon'},
                { data: 'flag',className: 'text-center'},
                { data: 'editbtn',className: 'text-center'},
                { data: 'delbtn',className: 'text-center'}                 
            ], 
            "columnDefs":[
            {
            "orderable":false,
            },
            
            ],
            "pageLength":25
        });
    });
</script>
<?php require APPROOT . '/views/inc/footer.php'; ?>

